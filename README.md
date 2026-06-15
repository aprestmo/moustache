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
├── acf-json/          # ACF field group JSON (auto-synced)
├── dist/              # Compiled assets (CSS, JS, fonts, icons)
├── includes/
│   ├── normalize/     # WordPress behaviour cleanup
│   ├── layouts/       # Layout-specific PHP helpers
│   ├── admin-brand.php          # Custom admin login branding
│   ├── custom-functions.php     # Theme utility functions
│   ├── enqueue-assets.php       # Script/style registration
│   ├── options-page.php         # ACF options page
│   ├── setup-theme.php          # Theme supports, menus, image sizes
│   └── trigger-astro-build.php  # GitHub Actions dispatch on post save
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

The theme expects the following post types to be registered (via a plugin or mu-plugin):

| Post type | Description |
|-----------|-------------|
| `pitch` | Football pitches (Google Maps integration) |
| `fixture` | Match fixtures |
| `player` | Players |
| `club` | Clubs |
| `league` | Leagues |

Taxonomy: `tournament`

## REST API

The theme registers a custom endpoint for standings data:

```
GET /wp-json/moustache/v1/standings
GET /wp-json/moustache/v1/standings?team=Kampbart
```

Standings are fetched from the [bedriftsidretten-standings-scraper](https://github.com/aprestmo/bedriftsidretten-standings-scraper) GitHub repo and cached as a transient for 6 hours.

## Admin Pages

- **Tools → Standings** — view cache status, season status, environment check, and manually clear the standings cache.
- **Settings → Astro build** — view GitHub Actions trigger status and manually dispatch a build.

## Deployment

The site runs on [Coolify](https://coolify.io/) via Docker at **https://kampbart.com**.

The theme directory is mounted as a Docker volume:
```
/var/lib/docker/volumes/v10zdqzt6cvc9ktlu4pyqyv2_wordpress-files/_data/wp-content/themes/moustache
```

### Deploy changes

1. Make and test changes locally.
2. Commit and push to the `main` branch:
   ```bash
   git add -A
   git commit -m "Your message"
   git push origin main
   ```
3. On the server, pull the latest changes into the theme directory:
   ```bash
   git pull origin main
   ```
4. If front-end assets were changed, rebuild:
   ```bash
   pnpm install && pnpm build
   ```

No server restart is required — WordPress serves the updated PHP and `dist/` assets immediately.
