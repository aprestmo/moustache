/**
 * Audio playlist web component (self-contained: includes Screen Wake Lock).
 * Usage: <audio-playlist title="My playlist" playlist='[{"url":"...","title":"..."}]'></audio-playlist>
 */
(function () {
    // --- Wake Lock (inlined) ---
    let wakeLockSentinel = null;
    let visibilityHandler = null;
    function wakeLockSupported() { return 'wakeLock' in navigator; }
    async function wakeLockRequest() {
        if (!wakeLockSupported()) return false;
        try {
            wakeLockSentinel = await navigator.wakeLock.request('screen');
            return true;
        } catch (e) { console.warn('Wake Lock failed', e); return false; }
    }
    async function wakeLockRelease() {
        if (wakeLockSentinel) { await wakeLockSentinel.release(); wakeLockSentinel = null; }
    }
    function wakeLockStartVisibilityReacquire(getShouldLock) {
        if (visibilityHandler) {
            document.removeEventListener('visibilitychange', visibilityHandler);
            visibilityHandler = null;
        }
        visibilityHandler = async function () {
            if (document.visibilityState === 'visible' && getShouldLock()) await wakeLockRequest();
        };
        document.addEventListener('visibilitychange', visibilityHandler);
    }

    // --- Playlist normalization ---
    function normalizePlaylist(raw) {
        if (!Array.isArray(raw)) return [];
        return raw.map(function (item) {
            if (typeof item === 'string') return { url: item, title: item.split('/').pop() || 'Untitled' };
            return { url: item.url || item.source_url || '', title: item.title || 'Untitled' };
        }).filter(function (item) { return item.url; });
    }
    function getUrl(item) { return typeof item === 'string' ? item : item.url; }

    // --- Template & component ---
    var template = document.createElement('template');
    template.innerHTML = [
        '<style>',
        ':host { display: block; font-family: system-ui, sans-serif; }',
        'h2 { margin: 0 0 0.75rem; font-size: 1.25rem; }',
        '#audio { display: block; width: 100%; margin-bottom: 0.75rem; }',
        '#tracklist { list-style: none; margin: 0; padding: 0; max-height: 40vh; overflow-y: auto; }',
        '#tracklist li { padding: 0.4rem 0.5rem; cursor: pointer; border-radius: 4px; }',
        '#tracklist li:hover { background: #eee; }',
        '#tracklist li.active { background: #ddd; font-weight: 500; }',
        'label { display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 0.75rem; cursor: pointer; }',
        '</style>',
        '<h2 id="heading"></h2>',
        '<audio id="audio" controls></audio>',
        '<ul id="tracklist"></ul>',
        '<label><input type="checkbox" id="keep-screen-on" /> Keep screen on while playing</label>'
    ].join('\n');

    class AudioPlaylist extends HTMLElement {
        connectedCallback() {
            var playlistAttr = this.getAttribute('playlist');
            if (!playlistAttr) return;
            var raw = JSON.parse(playlistAttr);
            this.playlist = normalizePlaylist(raw);
            if (this.playlist.length === 0) return;

            this.currentIndex = 0;
            this.attachShadow({ mode: 'open' });
            this.shadowRoot.appendChild(template.content.cloneNode(true));

            var heading = this.shadowRoot.getElementById('heading');
            var audio = this.shadowRoot.getElementById('audio');
            var tracklist = this.shadowRoot.getElementById('tracklist');
            var checkbox = this.shadowRoot.getElementById('keep-screen-on');

            heading.textContent = this.getAttribute('title') || 'Playlist';

            var self = this;
            this.playlist.forEach(function (item, i) {
                var li = document.createElement('li');
                li.textContent = item.title;
                li.setAttribute('data-index', String(i));
                if (i === 0) li.classList.add('active');
                tracklist.appendChild(li);
            });

            audio.src = getUrl(this.playlist[0]);

            function setActiveTrack(index) {
                self.currentIndex = index;
                tracklist.querySelectorAll('li').forEach(function (li, i) {
                    li.classList.toggle('active', i === index);
                });
            }

            tracklist.addEventListener('click', function (e) {
                var li = e.target.closest('li[data-index]');
                if (!li) return;
                var index = parseInt(li.getAttribute('data-index'), 10);
                setActiveTrack(index);
                audio.src = getUrl(self.playlist[index]);
                audio.play();
            });

            audio.addEventListener('ended', function () {
                self.currentIndex = (self.currentIndex + 1) % self.playlist.length;
                setActiveTrack(self.currentIndex);
                audio.src = getUrl(self.playlist[self.currentIndex]);
                audio.play();
            });

            audio.addEventListener('play', function () {
                setActiveTrack(self.currentIndex);
                if (checkbox.checked) wakeLockRequest();
            });
            audio.addEventListener('pause', function () { wakeLockRelease(); });
            checkbox.addEventListener('change', function () {
                if (!checkbox.checked) wakeLockRelease();
            });
            wakeLockStartVisibilityReacquire(function () { return checkbox.checked && !audio.paused; });

            if (!wakeLockSupported()) {
                checkbox.disabled = true;
                checkbox.parentElement.lastChild.textContent = ' Keep screen on (not supported)';
            }
        }
    }

    customElements.define('audio-playlist', AudioPlaylist);
})();
