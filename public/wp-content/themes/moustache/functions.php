<?php
//Die if accessed directly
defined('ABSPATH') || die('Shame on you');

// Vite assets helpers
define('IS_DEVELOPMENT', is_development());
define('DIST_PATH', get_template_directory_uri().'/dist/');

function endsWith(string $haystack, string $needle): bool
{
  return substr($haystack, -strlen($needle)) === $needle;
}

function is_development(): bool
{
  if (isset($_SERVER['SERVER_NAME'])) {
    if (
      $_SERVER['SERVER_NAME'] == 'localhost'
      || endsWith($_SERVER['SERVER_NAME'], '.test')
      || endsWith($_SERVER['SERVER_NAME'], '.local')
    ) {
      return true;
    }
  }

  return false;
}

function vite(String $entry): string
{
  return vite_js_tag($entry)
    .vite_js_preload_imports($entry)
    .vite_css_tag($entry);
}

// Helpers to print tags
function vite_js_tag(string $entry): string
{
  $url = IS_DEVELOPMENT ? 'http://localhost:3005/'.$entry : vite_asset_url($entry);

  if (!$url) {
    return '';
  }

  return '<script type="module" crossorigin src="'.$url.'"></script>';
}

function vite_js_preload_imports(string $entry): string
{
  if (IS_DEVELOPMENT) {
    return '';
  }

  $res = '';
  foreach (vite_imports_urls($entry) as $url) {
    $res .= '<link rel="modulepreload" href="'.$url.'">';
  }

  return $res;
}

function vite_css_tag(string $entry): string
{
  // not needed on dev, it's injected by Vite
  if (IS_DEVELOPMENT) {
    return '';
  }

  $tags = '';
  foreach (vite_css_urls($entry) as $url) {
    $tags .= '<link rel="stylesheet" href="'.$url.'">';
  }

  return $tags;
}

// Helpers to locate files
function vite_get_manifest(): array
{
  $content = file_get_contents(__DIR__.'/../dist/manifest.json');

  return json_decode($content, true);
}

function vite_asset_url(string $entry): string
{
  $manifest = vite_get_manifest();

  return isset($manifest[$entry]) ? DIST_PATH.$manifest[$entry]['file'] : '';
}

function vite_imports_urls(string $entry): array
{
  $urls = [];
  $manifest = vite_get_manifest();

  if (!empty($manifest[$entry]['imports'])) {
    foreach ($manifest[$entry]['imports'] as $imports) {
      $urls[] = DIST_PATH.$manifest[$imports]['file'];
    }
  }

  return $urls;
}

function vite_css_urls(string $entry): array
{
  $urls = [];
  $manifest = vite_get_manifest();

  if (!empty($manifest[$entry]['css'])) {
    foreach ($manifest[$entry]['css'] as $file) {
      $urls[] = DIST_PATH.$file;
    }
  }

  return $urls;
}

/**
 * Author: Alexander Prestmo
 *Author URI: http://attityd.no
 */

/** Define default translation domain for this theme */
define('TRANSLATION_DOMAIN', 'moustache');

/** Define theme root */
define('THEME_ROOT', __DIR__ . '/');

/** Register custom Taxonomies and Post Types */
foreach (glob(__DIR__ . '/includes/taxonomies/*.php') as $file) {
  include $file;
}

foreach (glob(__DIR__ . '/includes/post-types/*.php') as $file) {
  include $file;
}

/** Setup theme (Images and menus) */
require __DIR__ . '/includes/setup-theme.php';

/** Setup assets (Script and styles) */
require __DIR__ . '/includes/enqueue-assets.php';

/*
 * Cleanup/normalize WordPress behavior
 */
foreach (glob(__DIR__ . '/includes/normalize/*.php') as $file) {
  include $file;
}

/** Custom functions */
require __DIR__ . '/includes/custom-functions.php';

/** Load ACF */
require __DIR__ . '/includes/acf.php';



//** My oWn things */

/** Match report functions */
include get_template_directory() . '/includes/layouts/match-report.php';

/** Brand Admin Login */
include get_template_directory() . '/includes/admin-brand.php';



// TRUNK

/**
 * Redirect logged in user based on role
 *
 * @since 1.0
 */
function redirect_users_by_role()
{

  if (!defined('DOING_AJAX')) {

    $current_user = wp_get_current_user();
    $role_name    = $current_user->roles[0];

    if ('subscriber' === $role_name) {
      wp_redirect(home_url());
    }
  }
} // redirect_users_by_role
add_action('admin_init', 'redirect_users_by_role');
