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
  wp_deregister_script('jquery');
  wp_enqueue_script('jquery', '//cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js', false, '3.6.0', true);

  wp_enqueue_style('site_main_css', get_template_directory_uri() . '/dist/css/main.min.css');
  wp_enqueue_script('site_main_js', get_template_directory_uri() . '/dist/js/main.min.js', null, null, true);
}
add_action('wp_enqueue_scripts', 'theme_enqueue');
