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

function lt_html_excerpt($text)
{
    // Fakes an excerpt if needed
    global $post;
    if ('' == $text) {
        $text = get_the_content('');
        $text = apply_filters('the_content', $text);
        $text = str_replace('\]\]\>', ']]&gt;', $text);
        /*just add all the tags you want to appear in the excerpt --
        be sure there are no white spaces in the string of allowed tags */
        $text = strip_tags($text, '<p><br><b><a><em><strong>');
        /* you can also change the length of the excerpt here, if you want */
        $excerpt_length = 50;
        $words = explode(' ', $text, $excerpt_length + 1);
        if (count($words) > $excerpt_length) {
            array_pop($words);
            array_push($words, '&hellip;');
            $text = implode(' ', $words);
        }
    }
    return $text;
}

/* remove the default filter */
remove_filter('get_the_excerpt', 'wp_trim_excerpt');

/* now, add your own filter */
add_filter('get_the_excerpt', 'lt_html_excerpt');

function inject_google_maps_api_key()
{
    // Define the condition for injecting the API key.
    // Example: Only on specific pages, post types, or templates.
    if (is_singular('pitch')) { // Adjust this condition to your needs
        echo '<script>const googleMapsApiKey = "' . esc_js(GOOGLE_MAPS_API_KEY) . '";</script>';
    }
}
add_action('wp_head', 'inject_google_maps_api_key');

/**
 * Fetch standings data from JSON URL with improved caching and error handling
 *
 * @return array|false Standings data or false on error
 */
function fetch_standings_data(): array|false
{
    // Check for cached data (cache for 6 hours since data updates weekly)
    $cache_key = 'standings_data';
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        return $cached_data;
    }

    // Use GitHub API instead of raw file URL (more reliable, no tokens needed)
    // Note: The ?ref=main parameter is required to specify the branch
    $json_url = 'https://api.github.com/repos/aprestmo/bedriftsidretten-standings-scraper/contents/public/standings.json?ref=main';

    // Use WordPress HTTP API with better error handling
    $response = wp_remote_get($json_url, [
        'timeout' => 15,
        'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url'),
        'headers' => [
            'Accept' => 'application/json',
            'Cache-Control' => 'no-cache'
        ]
    ]);

    if (is_wp_error($response)) {
        error_log('Standings API Error: ' . $response->get_error_message());
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        error_log('Standings API HTTP Error: ' . $status_code);
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        error_log('Standings API: Empty response body');
        return false;
    }

    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Standings API JSON Error: ' . json_last_error_msg());
        return false;
    }

    // GitHub API returns file metadata with base64-encoded content
    if (!isset($data['content']) || !isset($data['encoding']) || $data['encoding'] !== 'base64') {
        error_log('Standings API: Invalid GitHub API response format');
        return false;
    }

    // Decode the base64 content
    $json_content = base64_decode($data['content']);
    if ($json_content === false) {
        error_log('Standings API: Failed to decode base64 content');
        return false;
    }

    // Parse the actual JSON data
    $standings_data = json_decode($json_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Standings API JSON Content Error: ' . json_last_error_msg());
        return false;
    }

    // Validate standings data structure
    if (!is_array($standings_data) || empty($standings_data)) {
        error_log('Standings API: Invalid standings data structure');
        return false;
    }

    // Cache the data for 6 hours (since data updates weekly)
    set_transient($cache_key, $standings_data, 6 * HOUR_IN_SECONDS);

    return $standings_data;
}

/**
 * Get the last update time for standings data
 *
 * @return string|false Last update time or false if not available
 */
function get_standings_last_update(): string|false
{
    $cache_key = 'standings_last_update';
    $last_update = get_transient($cache_key);

    if ($last_update === false) {
        $last_update = current_time('mysql');
        set_transient($cache_key, $last_update, 6 * HOUR_IN_SECONDS);
    }

    return $last_update;
}

/**
 * Clear standings cache (useful for manual refresh)
 */
function clear_standings_cache(): void
{
    delete_transient('standings_data');
    delete_transient('standings_last_update');
}

// Add admin action to clear cache
add_action('wp_ajax_clear_standings_cache', function () {
    if (current_user_can('manage_options')) {
        clear_standings_cache();
        wp_send_json_success('Standings cache cleared');
    }
    wp_send_json_error('Unauthorized');
});

/**
 * Register REST API endpoint for standings data
 */
add_action('rest_api_init', function () {
    register_rest_route('moustache/v1', '/standings', [
        'methods' => 'GET',
        'callback' => 'get_standings_rest_data',
        'permission_callback' => '__return_true',
        'args' => [
            'team' => [
                'required' => false,
                'type' => 'string',
                'description' => 'Filter by team name'
            ]
        ]
    ]);
});

function get_standings_rest_data($request)
{
    $standings = fetch_standings_data();

    if (!$standings) {
        return new WP_Error('no_data', 'No standings data available', ['status' => 404]);
    }

    $team_filter = $request->get_param('team');

    if ($team_filter) {
        $standings = array_filter($standings, function ($team) use ($team_filter) {
            return stripos($team['team'], $team_filter) !== false;
        });
    }

    return [
        'data' => $standings,
        'last_update' => get_standings_last_update(),
        'count' => count($standings)
    ];
}
