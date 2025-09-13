<?php
/**
 * Test script for debugging standings functionality
 * Access via: yoursite.com/wp-content/themes/moustache/test-standings.php
 */

// Load WordPress
require_once('../../../../wp-load.php');

echo '<h1>Standings Debug Test</h1>';

echo '<h2>Function Existence Check</h2>';
if (function_exists('fetch_standings_data')) {
    echo '<p>✓ fetch_standings_data function exists</p>';
} else {
    echo '<p>✗ fetch_standings_data function not found</p>';
}

if (function_exists('get_standings_last_update')) {
    echo '<p>✓ get_standings_last_update function exists</p>';
} else {
    echo '<p>✗ get_standings_last_update function not found</p>';
}

if (function_exists('is_season_2025_active')) {
    echo '<p>✓ is_season_2025_active function exists</p>';
} else {
    echo '<p>✗ is_season_2025_active function not found</p>';
}

echo '<h2>Season Status Check</h2>';
if (function_exists('is_season_2025_active')) {
    $is_active = is_season_2025_active();
    echo '<p>Season 2025 active: ' . ($is_active ? 'YES' : 'NO') . '</p>';
    echo '<p>Current date: ' . current_time('Y-m-d H:i:s') . '</p>';
} else {
    echo '<p>Cannot check season status - function missing</p>';
}

echo '<h2>Data Fetch Test</h2>';
if (function_exists('fetch_standings_data')) {
    try {
        $standings = fetch_standings_data();
        if (is_array($standings)) {
            echo '<p>✓ Successfully fetched ' . count($standings) . ' teams</p>';
            echo '<h3>First Team Data:</h3>';
            if (count($standings) > 0) {
                echo '<pre>' . print_r($standings[0], true) . '</pre>';
            }
        } else {
            echo '<p>✗ fetch_standings_data returned: ' . gettype($standings) . '</p>';
        }
    } catch (Exception $e) {
        echo '<p>✗ Error: ' . $e->getMessage() . '</p>';
    }
} else {
    echo '<p>Cannot test data fetch - function missing</p>';
}

echo '<h2>Cache Check</h2>';
$cache_data = get_transient('standings_data');
if ($cache_data) {
    echo '<p>Cached data exists (' . count($cache_data) . ' teams)</p>';
} else {
    echo '<p>No cached data found</p>';
}

echo '<h2>Direct API Test</h2>';
$api_url = 'https://api.github.com/repos/aprestmo/bedriftsidretten-standings-scraper/contents/public/standings.json?ref=main';
$response = wp_remote_get($api_url, ['timeout' => 15]);

if (is_wp_error($response)) {
    echo '<p>✗ API Error: ' . $response->get_error_message() . '</p>';
} else {
    $status = wp_remote_retrieve_response_code($response);
    echo '<p>API Response Status: ' . $status . '</p>';
    if ($status === 200) {
        echo '<p>✓ API is accessible</p>';
    }
}

echo '<hr><p><em>Test completed at ' . current_time('Y-m-d H:i:s') . '</em></p>';
?>