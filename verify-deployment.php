<?php
/**
 * Quick deployment verification script
 * Upload this to your production server and access it via browser
 * DELETE THIS FILE after verification for security
 */

// Prevent direct access from non-localhost in production
if (!isset($_SERVER['HTTP_HOST']) || (strpos($_SERVER['HTTP_HOST'], 'localhost') === false && !isset($_GET['allow']))) {
    die('Access denied. Add ?allow=1 to URL if you are the administrator.');
}

echo '<h1>Production Deployment Verification</h1>';
echo '<style>body{font-family:Arial,sans-serif;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;}</style>';

echo '<h2>File Existence Check</h2>';

$files_to_check = [
    'functions.php' => 'Core functions file',
    'template-parts/standings-table.php' => 'Standings table template',
    'taxonomy-tournament.php' => 'Tournament taxonomy template',
    'test-standings.php' => 'Debug test script (optional)'
];

foreach ($files_to_check as $file => $description) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo '<p class="ok">✓ ' . $file . ' - ' . $description . ' (Modified: ' . date('Y-m-d H:i:s', filemtime($path)) . ')</p>';
    } else {
        echo '<p class="error">✗ ' . $file . ' - ' . $description . ' - FILE MISSING!</p>';
    }
}

echo '<h2>PHP Version Check</h2>';
$php_version = phpversion();
echo '<p>PHP Version: ' . $php_version;
if (version_compare($php_version, '8.0.0', '>=')) {
    echo ' <span class="ok">✓ Compatible</span></p>';
} else {
    echo ' <span class="warning">⚠ May have compatibility issues with return type declarations</span></p>';
}

echo '<h2>Extension Check</h2>';
$extensions = [
    'curl' => 'Required for HTTP requests',
    'openssl' => 'Required for HTTPS requests', 
    'json' => 'Required for JSON parsing'
];

foreach ($extensions as $ext => $description) {
    if (extension_loaded($ext)) {
        echo '<p class="ok">✓ ' . $ext . ' - ' . $description . '</p>';
    } else {
        echo '<p class="error">✗ ' . $ext . ' - ' . $description . ' - MISSING!</p>';
    }
}

echo '<h2>Configuration Check</h2>';
echo '<p>allow_url_fopen: ' . (ini_get('allow_url_fopen') ? '<span class="ok">Enabled</span>' : '<span class="warning">Disabled</span>') . '</p>';
echo '<p>memory_limit: ' . ini_get('memory_limit') . '</p>';
echo '<p>max_execution_time: ' . ini_get('max_execution_time') . ' seconds</p>';

echo '<h2>WordPress Integration</h2>';
if (file_exists(__DIR__ . '/../../../../wp-load.php')) {
    echo '<p class="ok">✓ WordPress wp-load.php found</p>';
    
    try {
        require_once(__DIR__ . '/../../../../wp-load.php');
        echo '<p class="ok">✓ WordPress loaded successfully</p>';
        
        // Check if our functions exist
        if (function_exists('fetch_standings_data')) {
            echo '<p class="ok">✓ fetch_standings_data function available</p>';
        } else {
            echo '<p class="error">✗ fetch_standings_data function not found</p>';
        }
        
        if (function_exists('is_season_2025_active')) {
            echo '<p class="ok">✓ is_season_2025_active function available</p>';
        } else {
            echo '<p class="error">✗ is_season_2025_active function not found</p>';
        }
        
        // Test basic WordPress function
        if (function_exists('wp_remote_get')) {
            echo '<p class="ok">✓ wp_remote_get function available</p>';
        } else {
            echo '<p class="error">✗ wp_remote_get function not available</p>';
        }
        
    } catch (Exception $e) {
        echo '<p class="error">✗ Error loading WordPress: ' . $e->getMessage() . '</p>';
    }
} else {
    echo '<p class="error">✗ WordPress wp-load.php not found - check file paths</p>';
}

echo '<h2>Network Test</h2>';
$test_url = 'https://api.github.com/repos/aprestmo/bedriftsidretten-standings-scraper/contents/public/standings.json?ref=main';

if (function_exists('wp_remote_get')) {
    $response = wp_remote_get($test_url, ['timeout' => 10]);
    if (is_wp_error($response)) {
        echo '<p class="error">✗ API request failed: ' . $response->get_error_message() . '</p>';
    } else {
        $status = wp_remote_retrieve_response_code($response);
        if ($status === 200) {
            echo '<p class="ok">✓ API is accessible (Status: ' . $status . ')</p>';
        } else {
            echo '<p class="error">✗ API returned status: ' . $status . '</p>';
        }
    }
} else if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $test_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Deployment-Test/1.0');
    $result = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($status === 200) {
        echo '<p class="ok">✓ API is accessible via cURL (Status: ' . $status . ')</p>';
    } else {
        echo '<p class="error">✗ cURL request failed (Status: ' . $status . ')</p>';
    }
} else {
    echo '<p class="warning">⚠ Cannot test API access - no HTTP functions available</p>';
}

echo '<hr>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ul>';
echo '<li>If all checks pass, visit your tournament page to test the standings table</li>';
echo '<li>Check <strong>WP Admin > Tools > Standings</strong> for more detailed testing</li>';
echo '<li>Monitor error logs after deployment</li>';
echo '<li><strong>DELETE THIS FILE</strong> after verification for security</li>';
echo '</ul>';

echo '<p><em>Verification completed at ' . date('Y-m-d H:i:s') . '</em></p>';
?>