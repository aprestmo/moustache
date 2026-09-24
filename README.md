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
├── auto-deploy.sh     # Push-to-deploy: fetch + ff-merge + conditional build (webhook/cron)
├── deploy.sh          # Server-side asset rebuild (git pull && ./deploy.sh)
├── includes/
│   ├── normalize/     # WordPress behaviour cleanup
│   ├── layouts/       # Layout-specific PHP helpers
│   ├── acf.php                  # ACF accessors (goals/cards/teams, legacy fallbacks)
│   ├── acf-migrate-fixtures.php # Tools → Fixture migration (legacy → new Kamper fields)
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

// Gitea push webhook + Action deploy (must match both Gitea secret values).
// While undefined, the deploy AJAX actions answer 404.
define('MOUSTACHE_WEBHOOK_SECRET', 'long-random-string');
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
- **Tools → Fixture migration** — dry-run / write the new Kamper field values (goals, cards, unplayed, numeric results). See below.
- **Settings → Astro build** — view GitHub Actions trigger status and manually dispatch a build.

## Fixture field migration (Kamper ACF)

Match reports read goals/assists/cards through `moustache_get_goals()` / `moustache_get_cards()` in `includes/acf.php`. The Kamper field group in `acf-json/group_5539864c3a238.json` was simplified to a single `goals` repeater and `match_cards` repeater (plus numeric `result_*` / `unplayed` fields). Production data originally lived in the removed half-based fields:

| Legacy meta (still in DB until cleaned up) | New field |
|---|---|
| `goals_assists_first_half` / `goals_assists_second_half` | `goals` |
| `cards` / `cards_first_half` / `cards_second_half` | `match_cards` |
| `result_fulltime` / `result_pause` text | `result_home_ft` / `result_away_ft` / `result_*_ht` |
| `canceled` | `unplayed` + `unplayed_reason` |
| multi-value `home_team` / `away_team` / `pitch` | single relationship ID |

**Why a one-shot write is required:** after the ACF JSON cutover, `get_field('goals_assists_*')` no longer expands those repeaters (ACF returns only the raw row-count string). Templates prefer `goals`; without migrated rows the UI shows no scorers/assists. `moustache_get_legacy_repeater_rows()` can still reconstruct legacy rows from post meta for fallbacks and for the migrator itself.

**How to run** (WP Admin, capability `manage_options`):

1. Deploy the theme commit that includes `includes/acf-migrate-fixtures.php` and the legacy meta reader in `includes/acf.php`.
2. Open **Tools → Fixture migration**.
3. **Dry run** first — check sample goal/card counts and any score parse errors.
4. **Write values** — copies legacy data into the new fields for every `fixture` post. Old meta keys are left in place for rollback. Sets option `moustache_fixtures_migrated` to `1`.

CLI equivalent (from the WordPress container):

```bash
php -r 'require "/var/www/html/wp-load.php"; print_r(moustache_run_fixture_migration(true));'   # dry run
php -r 'require "/var/www/html/wp-load.php"; print_r(moustache_run_fixture_migration(false));'  # write
```

Re-running write is safe if you need to pick up migrator fixes; it overwrites the new field values from current legacy meta. kampbart.com was migrated with this path (289 fixtures; ~223 with goal rows).

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
| `src/`, `public/`, `package.json`, `pnpm-lock.yaml`, `vite.config.js`, `postcss.config.js` | yes | **only for manual/server fallback** (the Gitea Action builds `dist/`) |

WordPress reads `dist/.vite/manifest.json` at runtime. The Gitea Action builds and commits `dist/`; the server-side CI deploy then pulls that committed build without rebuilding. A manual/server-side deployment still needs `./deploy.sh` for `src/`/`public/` changes. `dist/` is committed to git as a safety net, but **do not rely on it staying fresh** — if the manifest is missing entirely, WordPress logs `moustache theme: Vite manifest missing ...` instead of failing silently.

### Auto-deploy (CI + webhook + cron)

Pushing to `main` updates PHP/template changes with a plain pull. Frontend input changes are held back until the Gitea Action has built and approved the matching `dist/` commit; this prevents webhook/cron from deploying an unbuilt source tree. One script does the work either way:

- **Gitea Action (CI deployment)** — after the frontend build and `dist/` commit succeed, `.gitea/workflows/build.yml` sends a signed **synchronous** request to `POST /wp-admin/admin-ajax.php?action=moustache_deploy_ci`. The request includes the exact commit produced by the Action; the server verifies it is present, pulls the committed `dist/`, and the Action only turns green after that pull succeeds. The request uses the same `MOUSTACHE_WEBHOOK_SECRET` as production, with `skip_build: true` because CI has already built the assets.
- **`auto-deploy.sh`** — fetches `origin/main`, fast-forwards, and runs `deploy.sh` **only** when build inputs changed (`src/`, `public/`, `package.json`, `pnpm-lock.yaml`, `vite.config.js`, `postcss.config.js`). It is idempotent and lock-protected, so running it from two triggers at once is safe. The CI request sets `MOUSTACHE_DEPLOY_SKIP_BUILD=1` and `MOUSTACHE_DEPLOY_STRICT=1`: it verifies the exact target commit and committed manifest, uses `dist/` instead of rebuilding on the server, and fails if the checkout is dirty. Ordinary webhook/cron runs refuse unapproved frontend changes; a manual server build must opt in with `MOUSTACHE_DEPLOY_ALLOW_SERVER_BUILD=1`.
- **Webhook (instant fallback)** — Gitea POSTs to `POST /wp-admin/admin-ajax.php?action=moustache_deploy` (`includes/deploy-webhook.php`), which verifies Gitea's `X-Gitea-Signature` (HMAC-SHA256 of the raw body with `MOUSTACHE_WEBHOOK_SECRET`), checks the push targeted `main`, and spawns `auto-deploy.sh` detached, logging to `/tmp/moustache-deploy.log`. It uses `admin-ajax.php` rather than a REST route because the REST API is disabled site-wide on kampbart.com by a filter outside this repo (`WP_Error: rest_disabled`) — note that the same filter also 403s `/wp-json/moustache/v1/standings`.
- **Cron (fallback)** — runs the same script every minute, so missed PHP/template changes still deploy within ~60s. It intentionally refuses unapproved frontend source changes; use the Gitea Action for those.

One-time setup:

1. Add the secret to `wp-config.php`:
   ```php
   define('MOUSTACHE_WEBHOOK_SECRET', 'long-random-string');  // e.g. openssl rand -hex 32
   ```
2. In Gitea → repo → **Settings → Actions → Secrets**, add a repository secret named `MOUSTACHE_WEBHOOK_SECRET` with the **exact same value**. The Action's final deploy step fails immediately if this is missing or does not match production.
3. For the PHP/push fallback, in Gitea → repo → **Settings → Webhooks → Add Webhook → Gitea**:
   - **URL:** `https://kampbart.com/wp-admin/admin-ajax.php?action=moustache_deploy`
   - **Secret:** the same `MOUSTACHE_WEBHOOK_SECRET` value
   - **Trigger:** Push events, branch `main` (other refs are ignored by the endpoint anyway)
   - **SSL verification:** enabled
4. On the host, add the cron fallback (runs on the Docker host against the volume checkout). It will pull PHP changes but will not bypass the frontend CI gate:
   ```cron
   * * * * * /bin/bash /var/lib/docker/volumes/v10zdqzt6cvc9ktlu4pyqyv2_wordpress-files/_data/wp-content/themes/moustache/auto-deploy.sh >> /tmp/moustache-deploy.log 2>&1
   ```
   Needs `git` with pull access to the Gitea remote. For a manual server-side frontend build, also install Node 20+/pnpm and explicitly set `MOUSTACHE_DEPLOY_ALLOW_SERVER_BUILD=1`; the Action does not need pnpm on the server because it deploys committed `dist/`.

Verify a frontend push in Gitea Actions: the build job first commits `dist/`, then the **Deploy committed assets to production** step calls `action=moustache_deploy_ci` synchronously and returns `{"status":"deployed","deployed_commit":"...", ...}`. The Action verifies both the status and the exact commit SHA. A failed fetch, dirty checkout, fast-forward problem, newer remote commit, missing manifest, or a server that has not yet received the new CI endpoint makes the Action fail. During the first rollout, if the CI endpoint is not present, pull the new handler once on the server, then re-run the Action. The push webhook and cron remain useful for PHP/push fallbacks; their deliveries still return `200` with `{"status":"queued","pid":...}` because they are asynchronous. A `403` means the secrets don't match; `404` means `MOUSTACHE_WEBHOOK_SECRET` is undefined in `wp-config.php`.

Webhook down or first-time bootstrap? Pull the new handler once by hand, then re-run the Action:

```bash
cd .../themes/moustache
git pull --ff-only origin main
```

If the checkout is clean and you intentionally want a server-side frontend rebuild, opt in explicitly:

```bash
MOUSTACHE_DEPLOY_ALLOW_SERVER_BUILD=1 bash auto-deploy.sh
```

### Deploy changes (manual)

Auto-deploy covers the normal flow; if you need to do it by hand:

1. Make and test changes locally (`pnpm build` first if you touched assets — `dist/` is committed).
2. Commit and push to the `main` branch:
   ```bash
   git add -A
   git commit -m "Your message"
   git push origin main
   ```
3. On the server (only needed when auto-deploy is not set up yet):
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

### CI + deployment: Gitea Actions

The build workflow lives at **`.gitea/workflows/build.yml`** (the canonical Gitea location; the former `.github/workflows/build.yml` copy was moved there). On pushes to `main` touching `src/`, `public/`, or build config it rebuilds `dist/`, commits it with `[skip ci]`, and then makes a signed synchronous production deploy request. The deploy step uses the committed `dist/` and fails the Action if the server cannot pull it.

For it to actually run, all of the following must be true — none of which are verifiable from the repo itself:

1. Gitea instance has Actions enabled (`[actions] ENABLED=true`, default since Gitea 1.21).
2. **Repository Actions enabled**: repo → Settings → Actions → "Enable Repository Actions" (disabled per-repo by default).
3. A [Gitea Act runner](https://gitea.com/gitea/runner) is registered and online, can run `ubuntu-latest` (Docker-label) jobs, and its token may push to this repository (the workflow's final `git push` needs write access).
4. The repository Actions secret `MOUSTACHE_WEBHOOK_SECRET` exists and exactly matches the value in production's `wp-config.php`.

If Actions is unavailable or the secret is missing, the push webhook and host cron remain deployment fallbacks; otherwise use `git pull origin main` plus `./deploy.sh` manually.
