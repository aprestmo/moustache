# AGENTS.md

WordPress theme for kampbart.com — classic PHP template hierarchy (not a block theme). Requires the ACF plugin. PHP 8+, Node 20+ with pnpm.

## Commands

- `pnpm install` — pnpm only (`packageManager` in `package.json`).
- `pnpm dev` — Vite dev server on `:5173` with live reload of `*.php`.
- `pnpm build` — production build to `dist/`.
- `bash auto-deploy.sh` — server-side: fetch `origin/main`, ff-merge, run `deploy.sh` only if build inputs changed (webhook + cron both call this; lock-protected).

There are **no** lint, test, typecheck, or format scripts. Verify frontend changes with `pnpm build` and PHP with `php -l`. Prettier is installed but unconfigured and unused in CI — don't mass-format.

## Build pipeline (read before touching assets)

- Single entry: `src/main.js` (imports `src/js/*` + `src/css/main.css`). Output: `dist/`, manifest at `dist/.vite/manifest.json`.
- `includes/enqueue-assets.php` reads the manifest with the hardcoded key `src/main.js` — renaming the entry requires updating that PHP file too. A missing manifest in non-local env is `error_log`ged (broken-deploy guard).
- Dev/prod switch is `WP_ENVIRONMENT_TYPE === 'local'` (read via `wp_get_environment_type()`): local echoes `http://localhost:5173/...` tags directly (requires `pnpm dev` running); anything else serves the last build from the manifest.
- `public/` is Vite's `publicDir` and is copied verbatim into `dist/` on every build (logos, fonts, icons, `robots.txt`). Edit `public/`, never `dist/`.
- `dist/` is committed as a safety net. CI lives at **`.gitea/workflows/build.yml`** (Gitea Actions — it rebuilds and commits `dist/` on push to `main` when `src/`, `public/`, or build config change, with `[skip ci]`). It only runs if a Gitea Act runner is registered **and** repo Actions are enabled (README → CI section); otherwise build on the server with `./deploy.sh`. Never hand-edit `dist/`.
- CSS layer order is declared in `src/css/main.css`: `settings, tools, resets, base, objects, components, utilities`; the numbered dirs under `src/css/` map to those layers. PostCSS is `postcss-preset-env` stage 1 — modern syntax (oklch, logical properties) is intentional.
- `js/audio-playlist.js` and `js/acf-fixture-admin.js` are **not** in the Vite build — PHP enqueues them directly from `js/`.

## Architecture

- `functions.php` is the bootstrap; it requires everything in `includes/`.
- Standings is theme-independent: `includes/standings.php` holds the REST route, cache helpers, season gate, and Tools → Standings. It is loaded by the loader stub `mu-plugins/moustache-standings.php` (symlink to `wp-content/mu-plugins/` — README → One-time setup), or by `functions.php` as fallback when the loader is absent (guarded by `function_exists`). Never both.
- `includes/normalize/*.php` are auto-included by glob — a new file there loads with no registration.
- Root `*.php` files are template-hierarchy templates; partials in `template-parts/`; match-report helpers in `includes/layouts/`.
- CPTs (`pitch`, `fixture`, `player`, `club`) and taxonomies (`tournament`, `division`) are registered via ACF JSON in `acf-json/`. Field changes go in that JSON (auto-synced), not only in the WP admin.
- League tables live on `tournament` terms — there is no `league` post type.

## Gotchas

- Season logic lives in `includes/standings.php` as two constants at the top: `MOUSTACHE_SEASON_END_DATE` (`2026-12-31`) and `MOUSTACHE_SEASON_TOURNAMENT_SLUG` (`uteserie-2026`) — both marked **UPDATE ANNUALLY**, one-line edits. Standings silently return nothing once the season is "over" — bump them on year rollover.
- Standings are fetched from the external `bedriftsidretten-standings-scraper` GitHub repo and cached in the `standings_data` transient for 6h. Clear via Tools → Standings.
- Fixture goals/assists: UI uses `goals` / `match_cards` (see `moustache_get_goals()`). After pulling the simplified Kamper ACF JSON onto an environment that still has only legacy `goals_assists_*` meta, run **Tools → Fixture migration** (or `moustache_run_fixture_migration(false)`) once — otherwise match reports show empty scores. Migrator + meta fallback live in `includes/acf-migrate-fixtures.php` and `includes/acf.php` (`moustache_get_legacy_repeater_rows`). Flag: option `moustache_fixtures_migrated`. Details in README → Fixture field migration.
- `GOOGLE_MAPS_API_KEY` must be defined in `wp-config.php`; it's echoed as global `googleMapsApiKey` only on single `pitch` pages — `src/js/map.js` no-ops without it.
- Saving posts dispatches a GitHub Actions build of the separate `moustache-v7` Astro site (`includes/trigger-astro-build.php`, non-blocking/`blocking => false`); silently skipped if `MOUSTACHE_GITHUB_TOKEN` is unset.

## Deployment

Theme runs on Coolify/Docker at kampbart.com, mounted as a Docker volume; repo host is **Gitea** (git.attityd.no), not GitHub. Flow: push to `main` → auto-deploy (webhook `POST /wp-admin/admin-ajax.php?action=moustache_deploy` + host cron both run `auto-deploy.sh`: fetch, ff-merge, conditional `pnpm build`); manual fallback is `git pull origin main` on the server → `./deploy.sh` when `src/`/`public/` changed (PHP, `js/`, `acf-json/` are picked up by the pull alone). The `.gitea/workflows/build.yml` CI is optional and needs a registered Gitea Act runner — deploys must not depend on it. Webhook needs `MOUSTACHE_WEBHOOK_SECRET` in `wp-config.php` matching the Gitea webhook secret (setup in README → Auto-deploy). Note: prod has the whole REST API disabled via an external `rest_authentication_errors` filter (`rest_disabled`) — not in this repo; a REST-based route will 403 there, which is why the webhook uses admin-ajax. Full steps, the volume path, and CI requirements are in `README.md`.
