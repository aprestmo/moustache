<?php
//Die if accessed directly
defined('ABSPATH') || die('Shame on you');

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
// require __DIR__ . '/includes/acf.php';



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
