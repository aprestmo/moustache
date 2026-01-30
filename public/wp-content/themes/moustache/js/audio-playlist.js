/**
 * Self-contained vanilla web component: audio playlist with title, track list,
 * auto-advance and optional Screen Wake Lock. No external imports.
 * Build output: dist/audio-playlist.js (use via <script src="..."></script>)
 */

// --- Inlined Screen Wake Lock API utility ---
let wakeLockSentinel = null;
let visibilityHandler = null;

function wakeLockIsSupported() {
	return typeof navigator !== 'undefined' && 'wakeLock' in navigator;
}

async function wakeLockRequest() {
	if (!wakeLockIsSupported()) return false;
	try {
		wakeLockSentinel = await navigator.wakeLock.request('screen');
		return true;
	} catch (err) {
		console.warn('Screen Wake Lock request failed:', err);
		return false;
	}
}

async function wakeLockRelease() {
	if (wakeLockSentinel) {
		await wakeLockSentinel.release();
		wakeLockSentinel = null;
	}
}

function wakeLockStartVisibilityReacquire(getShouldLock) {
	if (visibilityHandler) {
		document.removeEventListener('visibilitychange', visibilityHandler);
		visibilityHandler = null;
	}
	visibilityHandler = async () => {
		if (document.visibilityState === 'visible' && getShouldLock()) {
			await wakeLockRequest();
		}
	};
	document.addEventListener('visibilitychange', visibilityHandler);
}

// --- Playlist helpers ---
function normalizePlaylist(raw) {
	if (!Array.isArray(raw)) return [];
	return raw
		.map((item) => {
			if (typeof item === 'string') {
				return { url: item, title: item.split('/').pop() || 'Untitled', duration: undefined };
			}
			return {
				url: item.url || item.source_url || '',
				title: item.title || 'Untitled',
				duration: item.duration != null ? item.duration : undefined,
			};
		})
		.filter((item) => item.url);
}

function formatDuration(seconds) {
	if (seconds == null || Number.isNaN(Number(seconds))) return '';
	const s = Math.floor(Number(seconds));
	const m = Math.floor(s / 60);
	const sec = s % 60;
	return `${m}:${sec.toString().padStart(2, '0')}`;
}

function getUrl(item) {
	return typeof item === 'string' ? item : item.url;
}

// --- Template ---
const template = document.createElement('template');
template.innerHTML = `
  <style>
    :host {
      display: block;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
      background: #fff;
      color: #181818;
      border-radius: 8px;
      padding: 1.25rem;
      overflow: hidden;
    }
    .toggle-row {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
      cursor: pointer;
      color: #555;
      font-size: 0.875rem;
    }
    .toggle-row .switch {
      position: relative;
      width: 2.5rem;
      height: 1.25rem;
      background: #b3b3b3;
      border-radius: 1.25rem;
      flex-shrink: 0;
      transition: background 0.2s;
    }
    .toggle-row .switch::after {
      content: '';
      position: absolute;
      top: 2px;
      left: 2px;
      width: calc(1.25rem - 4px);
      height: calc(1.25rem - 4px);
      background: #fff;
      border-radius: 50%;
      box-shadow: 0 1px 3px rgba(0,0,0,0.2);
      transition: transform 0.2s;
    }
    .toggle-row input { position: absolute; opacity: 0; width: 0; height: 0; }
    .toggle-row input:checked + .switch { background: #2e8b57; }
    .toggle-row input:checked + .switch::after { transform: translateX(1.25rem); }
    .toggle-row input:focus-visible + .switch { outline: 2px solid #2e8b57; outline-offset: 2px; }
    .toggle-row:hover { color: #181818; }
    h2 {
      margin: 0 0 1rem;
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: -0.04em;
      color: #181818;
    }
    #tracklist {
      list-style: none;
      margin: 0;
      padding: 0;
      max-height: 40vh;
      overflow-y: auto;
      border-radius: 4px;
    }
    #tracklist::-webkit-scrollbar { width: 12px; }
    #tracklist::-webkit-scrollbar-track { background: #f5f5f5; border-radius: 6px; }
    #tracklist::-webkit-scrollbar-thumb { background: #b3b3b3; border-radius: 6px; border: 3px solid #fff; }
    #tracklist::-webkit-scrollbar-thumb:hover { background: #888; }
    #tracklist li {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 0.5rem 0.5rem 0;
      cursor: pointer;
      border-radius: 4px;
      color: #555;
      transition: background 0.15s, color 0.15s;
    }
    #tracklist li:hover {
      background: rgba(0, 0, 0, 0.06);
      color: #181818;
    }
    #tracklist li.active {
      color: lab(47.7268% 67.744 45.0378);
      background: transparent;
    }
    #tracklist li.active:hover { background: rgba(0, 0, 0, 0.06); }
    #tracklist li .track-index {
      flex-shrink: 0;
      width: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.875rem;
      font-variant-numeric: tabular-nums;
    }
    #tracklist li .track-play-icon { display: none; color: #181818; }
    #tracklist li .track-pause-icon { display: none; color: #181818; }
    #tracklist li:hover .track-num { display: none; }
    #tracklist li:hover .track-play-icon { display: flex; }
    #tracklist li.active .track-num { display: none; }
    #tracklist li.active .track-play-icon { color: lab(47.7268% 67.744 45.0378); }
    #tracklist li.active:not(.playing) .track-play-icon { display: flex; }
    #tracklist li.active.playing .track-play-icon { display: none; }
    #tracklist li.active.playing .track-pause-icon { display: flex; color: lab(47.7268% 67.744 45.0378); }
    #tracklist li .track-title {
      flex: 1;
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      font-size: 0.9375rem;
    }
    #tracklist li .track-duration {
      flex-shrink: 0;
      font-size: 0.875rem;
      font-variant-numeric: tabular-nums;
    }
    #tracklist li.active .track-duration { color: #555; }
    .player-bar {
      margin-top: 0.75rem;
      padding-top: 0.75rem;
      border-top: 1px solid #e0e0e0;
      border-radius: 0 0 6px 6px;
    }
    #audio {
      display: block;
      width: 100%;
      height: 40px;
    }
  </style>
  <label class="toggle-row">
    <input type="checkbox" id="keep-screen-on" />
    <span class="switch" aria-hidden="true"></span>
    <span class="toggle-label">Behold skjermen på</span>
  </label>
  <h2 id="heading"></h2>
  <ul id="tracklist"></ul>
  <div class="player-bar">
    <audio id="audio" controls></audio>
  </div>
`;

class AudioPlaylist extends HTMLElement {
	connectedCallback() {
		const playlistAttr = this.getAttribute('playlist');
		if (!playlistAttr) return;

		let raw;
		try {
			raw = JSON.parse(playlistAttr);
		} catch (_) {
			return;
		}
		this.playlist = normalizePlaylist(raw);
		if (this.playlist.length === 0) return;

		this.currentIndex = 0;

		this.attachShadow({ mode: 'open' });
		this.shadowRoot.appendChild(template.content.cloneNode(true));

		const heading = this.shadowRoot.getElementById('heading');
		const audio = this.shadowRoot.getElementById('audio');
		const tracklist = this.shadowRoot.getElementById('tracklist');
		const checkbox = this.shadowRoot.getElementById('keep-screen-on');

		const titleAttr = this.getAttribute('title');
		if (titleAttr) {
			heading.textContent = titleAttr;
		} else {
			heading.remove();
		}

		const playIconSvg = () => {
			const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
			svg.setAttribute('viewBox', '0 0 24 24');
			svg.setAttribute('width', '14');
			svg.setAttribute('height', '14');
			svg.setAttribute('aria-hidden', 'true');
			const poly = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
			poly.setAttribute('points', '6,4 6,20 20,12');
			poly.setAttribute('fill', 'currentColor');
			svg.appendChild(poly);
			return svg;
		};

		const pauseIconSvg = () => {
			const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
			svg.setAttribute('viewBox', '0 0 24 24');
			svg.setAttribute('width', '14');
			svg.setAttribute('height', '14');
			svg.setAttribute('aria-hidden', 'true');
			const rect1 = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
			rect1.setAttribute('x', '6');
			rect1.setAttribute('y', '4');
			rect1.setAttribute('width', '4');
			rect1.setAttribute('height', '16');
			rect1.setAttribute('fill', 'currentColor');
			const rect2 = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
			rect2.setAttribute('x', '14');
			rect2.setAttribute('y', '4');
			rect2.setAttribute('width', '4');
			rect2.setAttribute('height', '16');
			rect2.setAttribute('fill', 'currentColor');
			svg.appendChild(rect1);
			svg.appendChild(rect2);
			return svg;
		};

		this.playlist.forEach((item, i) => {
			const li = document.createElement('li');
			li.setAttribute('data-index', String(i));
			const indexWrap = document.createElement('span');
			indexWrap.className = 'track-index';
			const numSpan = document.createElement('span');
			numSpan.className = 'track-num';
			numSpan.textContent = String(i + 1);
			indexWrap.appendChild(numSpan);
			const playIcon = document.createElement('span');
			playIcon.className = 'track-play-icon';
			playIcon.appendChild(playIconSvg());
			indexWrap.appendChild(playIcon);
			const pauseIcon = document.createElement('span');
			pauseIcon.className = 'track-pause-icon';
			pauseIcon.appendChild(pauseIconSvg());
			indexWrap.appendChild(pauseIcon);
			li.appendChild(indexWrap);
			const titleSpan = document.createElement('span');
			titleSpan.className = 'track-title';
			titleSpan.textContent = item.title;
			li.appendChild(titleSpan);
			const durationSpan = document.createElement('span');
			durationSpan.className = 'track-duration';
			durationSpan.textContent = item.duration != null ? formatDuration(item.duration) : '';
			li.appendChild(durationSpan);
			if (i === 0) li.classList.add('active');
			tracklist.appendChild(li);
		});

		audio.src = getUrl(this.playlist[0]);

		const setActiveTrack = (index) => {
			this.currentIndex = index;
			tracklist.querySelectorAll('li').forEach((li, i) => {
				li.classList.toggle('active', i === index);
				li.classList.toggle('playing', i === index && !audio.paused);
			});
		};

		tracklist.addEventListener('click', (e) => {
			const li = e.target.closest('li[data-index]');
			if (!li) return;
			const index = parseInt(li.getAttribute('data-index'), 10);
			const isActiveTrack = index === this.currentIndex;
			if (isActiveTrack) {
				if (audio.paused) {
					audio.play();
				} else {
					audio.pause();
				}
				setActiveTrack(index);
				return;
			}
			setActiveTrack(index);
			audio.src = getUrl(this.playlist[index]);
			audio.play();
		});

		audio.addEventListener('ended', () => {
			this.currentIndex = (this.currentIndex + 1) % this.playlist.length;
			setActiveTrack(this.currentIndex);
			audio.src = getUrl(this.playlist[this.currentIndex]);
			audio.play();
		});

		audio.addEventListener('play', () => {
			setActiveTrack(this.currentIndex);
			const activeLi = tracklist.querySelector('li.active');
			if (activeLi) activeLi.classList.add('playing');
			if (checkbox.checked) wakeLockRequest();
		});
		audio.addEventListener('pause', () => {
			tracklist.querySelectorAll('li').forEach((li) => li.classList.remove('playing'));
			wakeLockRelease();
		});
		checkbox.addEventListener('change', () => {
			if (!checkbox.checked) wakeLockRelease();
		});
		wakeLockStartVisibilityReacquire(() => checkbox.checked && !audio.paused);

		if (!wakeLockIsSupported()) {
			checkbox.disabled = true;
			const labelEl = this.shadowRoot.querySelector('.toggle-label');
			if (labelEl) labelEl.textContent = 'Behold skjermen på (ikke støttet)';
		}
	}
}

customElements.define('audio-playlist', AudioPlaylist);
