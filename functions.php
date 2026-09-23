<?php
//Die if accessed directly
defined('ABSPATH') || die('Shame on you');

/**
 * Author: Alexander Prestmo
 * Author URI: http://attityd.no
 */

/**
 * Define default translation domain for this theme
 */
define('TRANSLATION_DOMAIN', 'moustache');

/**
 * Define theme root
 */
define('THEME_ROOT', __DIR__ . '/');

/**
 * Setup theme (Images and menus)
 */
require __DIR__ . '/includes/setup-theme.php';

/**
 * Setup assets (Script and styles)
 */
require __DIR__ . '/includes/enqueue-assets.php';

/*
 * Cleanup/normalize WordPress behavior
 */
foreach (glob(__DIR__ . '/includes/normalize/*.php') as $file) {
    include $file;
}

/**
 * Custom functions
 */
require __DIR__ . '/includes/custom-functions.php';

/**
 * ACF accessors, location rules, and options migration
 */
require __DIR__ . '/includes/acf.php';

/**
 * Match report functions
 */
require get_template_directory() . '/includes/layouts/match-report.php';

/**
 * Brand Admin Login
 */
require get_template_directory() . '/includes/admin-brand.php';

/**
 * Options page
 */
require get_template_directory() . '/includes/options-page.php';

/**
 * Trigger GitHub Actions build (moustache-v7) when content is updated
 */
require get_template_directory() . '/includes/trigger-astro-build.php';

/**
 * Fixture ACF field migration (Tools → Fixture migration)
 */
require get_template_directory() . '/includes/acf-migrate-fixtures.php';

/**
 * Gitea push webhook → auto-deploy.sh (spawns a detached pull + build;
 * host cron running the same script is the fallback — see README)
 */
require get_template_directory() . '/includes/deploy-webhook.php';

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
            exit;
        }
    }
} // redirect_users_by_role
add_action('admin_init', 'redirect_users_by_role');

/**
 * Excerpts: keep WordPress' default generator (wp_trim_excerpt → trimmed with
 * wp_trim_words()) and only set the theme's historical length + ellipsis.
 *
 * This replaces the old custom lt_html_excerpt() filter, which re-implemented
 * trimming by hand and carried a broken single-quoted
 * str_replace('\]\]\>', ...) pattern, and whose allow-tag list could nest
 * markup (e.g. <p>) inside the <p> that front-page.php wraps
 * get_the_excerpt() in — wp_trim_words() strips all tags, so no allow-list is
 * needed here. Hand-written post excerpts still pass through untrimmed (both
 * old filter and core leave a non-empty post_excerpt alone).
 */
add_filter('excerpt_length', function () {
    return 50;
});

add_filter('excerpt_more', function () {
    return ' &hellip;';
});

function inject_google_maps_api_key()
{
    if (!is_singular('pitch') || !defined('GOOGLE_MAPS_API_KEY') || GOOGLE_MAPS_API_KEY === '') {
        return;
    }

    echo '<script>const googleMapsApiKey = "' . esc_js(GOOGLE_MAPS_API_KEY) . '";</script>';
}
add_action('wp_head', 'inject_google_maps_api_key');

/**
 * Standings service (REST route, cache, Tools → Standings) — normally loaded
 * early by the mu-plugin loader (wp-content/mu-plugins/moustache-standings.php
 * requires includes/standings.php). Require it here as a fallback so the
 * endpoint still works when that loader is not installed.
 */
if (!function_exists('fetch_standings_data')) {
    require __DIR__ . '/includes/standings.php';
}
