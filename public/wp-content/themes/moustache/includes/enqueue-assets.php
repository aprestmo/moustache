<?php
if (!defined('ABSPATH'))
  return;

define('ASSETS_VERSION', '1.0.0');

function assets_version_id()
{
  return WP_DEBUG ? time() : ASSETS_VERSION;
}

/**
 * Enqueue scripts and styles for front-end.
 *
 * @since 0.1.0
 */
function theme_enqueue()
{
	wp_enqueue_style('site_main_css', get_template_directory_uri() . '/dist/assets/main.css');
	wp_enqueue_script('site_main_js', get_template_directory_uri() . '/dist/assets/main.js', null, null, true);
}
add_action('wp_enqueue_scripts', 'theme_enqueue');
