# Moustache

A custom WordPress theme for [Kampbart](https://kampbart.com). Built with Vite, PostCSS, and vanilla JS.

- **Version:** 6.0.0
- **Requires PHP:** 8.0+
- **Tested up to WordPress:** 6.6
- **License:** MIT
- **Author:** Alexander Prestmo / [Attityd](https://attityd.no)
- **Repository:** https://git.attityd.no/kampbart/moustache

## Requirements

- PHP 8.0+
- WordPress 6.x
- [Advanced Custom Fields (ACF)](https://www.advancedcustomfields.com/) plugin
- Node.js 20+ and [pnpm](https://pnpm.io/)

## Local Development

### Install dependencies

```bash
pnpm install
```

### Start dev server

```bash
pnpm dev
```

This starts Vite with live reload. Source files live in `src/` and are compiled to `dist/`.

### Production build

```bash
pnpm build
```

Compiled assets are output to `dist/` and referenced by WordPress via `includes/enqueue-assets.php`.

## Directory Structure

```
moustache/
├── .gitea/workflows/   # Gitea Actions workflow (builds + commits dist/)
├── acf-json/          # ACF field group JSON (auto-synced)
├── dist/              # Compiled assets (CSS, JS, fonts, icons)
├── deploy.sh          # Server-side asset rebuild (git pull && ./deploy.sh)
├── includes/
│   ├── normalize/     # WordPress behaviour cleanup
│   ├── layouts/       # Layout-specific PHP helpers
│   ├── admin-brand.php          # Custom admin login branding
│   ├── custom-functions.php     # Theme utility functions
│   ├── enqueue-assets.php       # Script/style registration
│   ├── options-page.php         # ACF options page
│   ├── setup-theme.php          # Theme supports, menus, image sizes
│   ├── standings.php            # Standings service (loaded via mu-plugin loader)
│   └── trigger-astro-build.php  # GitHub Actions dispatch on post save
├── mu-plugins/        # Loader stub → symlink to wp-content/mu-plugins/ (see Deployment)
├── src/
│   ├── css/           # Source CSS (PostCSS / postcss-preset-env)
│   └── js/            # Source JS
├── template-parts/    # Reusable template partials
├── languages/         # Translation files (.po / .mo)
├── functions.php      # Theme bootstrap
└── style.css          # Theme metadata header
```

## wp-config.php Constants

Add the following constants to `wp-config.php`:

```php
// Google Maps API key (used on pitch pages)
define('GOOGLE_MAPS_API_KEY', 'your-api-key');

// GitHub Actions dispatch (triggers moustache-v7 Astro build on post save)
define('MOUSTACHE_GITHUB_OWNER', 'aprestmo');
define('MOUSTACHE_GITHUB_REPO',  'moustache-v7');
define('MOUSTACHE_GITHUB_TOKEN', 'ghp_...');
```

The Astro build trigger is silently skipped if `MOUSTACHE_GITHUB_TOKEN` is not set.

## Custom Post Types

Post types and taxonomies are registered via ACF JSON in `acf-json/`:

| Post type | Description |
|-----------|-------------|
| `pitch` | Football pitches (Google Maps integration) |
| `fixture` | Match fixtures (no public permalink) |
| `player` | Players |
| `club` | Clubs |

Taxonomies: `tournament` (on fixtures), `division` (on tournaments).

League tables live on tournament terms (for example «Uteserie 2026»), not a `league` post type.

## REST API

A custom endpoint for standings data:

```
GET /wp-json/moustache/v1/standings
GET /wp-json/moustache/v1/standings?team=Kampbart
```

Standings are fetched from the [bedriftsidretten-standings-scraper](https://github.com/aprestmo/bedriftsidretten-standings-scraper) GitHub repo and cached as a transient for 6 hours.

The code lives in `includes/standings.php` and is loaded by the single-file mu-plugin `mu-plugins/moustache-standings.php` (symlinked to `wp-content/mu-plugins/` — see Deployment), with `functions.php` as a fallback when the loader is not installed. The season cutoff and tournament slug are constants at the top of `includes/standings.php` (**update both annually**).

## Admin Pages

- **Theme Settings** — 404 content.
- **Theme Settings → Club Information** — club details and Gullbart winners (shown on the Club info page).
- **Tools → Standings** — view cache status, season status, environment check, and manually clear the standings cache.
- **Tools → Fixture migration** — dry-run / write the new Kamper field values (goals, cards, unplayed, numeric results).
- **Settings → Astro build** — view GitHub Actions trigger status and manually dispatch a build.

## Deployment

The site runs on [Coolify](https://coolify.io/) via Docker at **https://kampbart.com** (repository: **Gitea**, not GitHub).

The theme directory is a git checkout mounted as a Docker volume:
```
/var/lib/docker/volumes/v10zdqzt6cvc9ktlu4pyqyv2_wordpress-files/_data/wp-content/themes/moustache
```

### What a deploy needs

| Changed paths | `git pull` on the server | Also run `./deploy.sh` |
|---|---|---|
| PHP templates, `includes/`, `template-parts/`, `js/`, `acf-json/`, `languages/` | yes | no |
| `src/`, `public/`, `package.json`, `pnpm-lock.yaml`, `vite.config.js`, `postcss.config.js` | yes | **yes** |

WordPress reads `dist/.vite/manifest.json` at runtime, so `src/`/`public/` changes only go live after a rebuild on the server. `dist/` is committed to git as a safety net, but **do not rely on it staying fresh** — if the manifest is missing entirely, WordPress logs `moustache theme: Vite manifest missing ...` instead of failing silently.

### Deploy changes

1. Make and test changes locally (`pnpm build` first if you touched assets — `dist/` is committed).
2. Commit and push to the `main` branch:
   ```bash
   git add -A
   git commit -m "Your message"
   git push origin main
   ```
3. On the server, pull and rebuild:
   ```bash
   git pull origin main
   ./deploy.sh      # pnpm install --frozen-lockfile && pnpm build — skip for pure PHP/content changes
   ```

No server restart is required — WordPress serves the updated PHP and `dist/` assets immediately. `deploy.sh` can also be set as Coolify's post-deployment command for this resource.

### One-time setup: standings mu-plugin

The standings REST endpoint / Tools → Standings page is shipped inside the theme (`includes/standings.php`) but registered through a mu-plugin loader so it is loaded independently of the theme bootstrap. On the server, symlink it once (the theme checkout and `wp-content/mu-plugins/` live in the same Docker volume):

```bash
cd .../wp-content/mu-plugins
ln -s ../themes/moustache/mu-plugins/moustache-standings.php moustache-standings.php
```

Until that symlink exists, `functions.php` loads the service as a fallback, so nothing breaks if you skip this — but the symlink is the intended setup (and is required for the service to survive a future theme change).

### CI (optional): Gitea Actions

The build workflow lives at **`.gitea/workflows/build.yml`** (the canonical Gitea location; the former `.github/workflows/build.yml` copy was moved there). On pushes to `main` touching `src/`, `public/`, or build config it rebuilds `dist/` and commits it back with `[skip ci]`.

For it to actually run, all of the following must be true — none of which are verifiable from the repo itself:

1. Gitea instance has Actions enabled (`[actions] ENABLED=true`, default since Gitea 1.21).
2. **Repository Actions enabled**: repo → Settings → Actions → "Enable Repository Actions" (disabled per-repo by default).
3. A [Gitea Act runner](https://gitea.com/gitea/runner) is registered and online, can run `ubuntu-latest` (Docker-label) jobs, and its token may push to this repository (the workflow's final `git push` needs write access).

If any of these is missing, CI silently does nothing — which is why `./deploy.sh` exists: **deploys never depend on CI**.
