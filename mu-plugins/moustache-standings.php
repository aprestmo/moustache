<?php
/**
 * Plugin Name: Moustache Standings (loader)
 * Plugin URI:  https://git.attityd.no/kampbart/moustache
 * Description: Loads the standings service from the Moustache theme (includes/standings.php) so the REST endpoint and Tools → Standings work independently of the theme bootstrap.
 * Version:     1.0
 *
 * INSTALL: WordPress only auto-loads single files placed directly in
 * wp-content/mu-plugins/ (subdirectories are ignored without a stub like
 * this), so symlink (preferred — never goes stale) or copy it there once:
 *
 *   cd wp-content/mu-plugins
 *   ln -s ../themes/moustache/mu-plugins/moustache-standings.php moustache-standings.php
 *   # (both paths live in the same wordpress-files Docker volume)
 *
 * NOTE: mu-plugins load BEFORE the theme is set up, so
 * get_template_directory() is not reliable this early — locate the theme
 * via WP_CONTENT_DIR instead. If the theme directory is ever renamed from
 * "moustache", update the path below.
 */

defined('ABSPATH') || exit;

$moustache_standings_file = WP_CONTENT_DIR . '/themes/moustache/includes/standings.php';

if (file_exists($moustache_standings_file)) {
	require_once $moustache_standings_file;
}

unset($moustache_standings_file);
