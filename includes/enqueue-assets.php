<?php
if (!defined('ABSPATH'))
	return;

define('ASSETS_VERSION', '1.0.0');

function assets_version_id()
{
	return WP_DEBUG ? time() : ASSETS_VERSION;
}

/**
 * Enqueue scripts and styles for frontend.
 *
 * @since 6.0.0
 */

function enqueue_vite_assets()
{
	// wp_deregister_script('jquery');
	// wp_enqueue_script('jquery', '//cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js', false, '3.6.0', true);

	$is_dev = defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local';

	if ($is_dev) {
		// In development, use Vite's dev server on default port 5173
		echo '<script type="module" src="http://localhost:5173/wp-content/themes/' . get_template() . '/@vite/client"></script>';
		echo '<script type="module" src="http://localhost:5173/wp-content/themes/' . get_template() . '/src/main.js"></script>';
	} else {
		// In production, use the manifest to locate the files
		$manifest_path = get_template_directory() . '/dist/.vite/manifest.json';
		if (file_exists($manifest_path)) {
			$manifest = json_decode(file_get_contents($manifest_path), true);
			$js = $manifest['src/main.js']['file'] ?? '';
			$css = $manifest['src/main.js']['css'][0] ?? '';

			if ($js) {
				wp_enqueue_script('theme-main', get_template_directory_uri() . '/dist/' . $js, [], null, true);
			}
			if ($css) {
				wp_enqueue_style('theme-main-style', get_template_directory_uri() . '/dist/' . $css, [], null);
			}
		}
	}
}
add_action('wp_enqueue_scripts', 'enqueue_vite_assets');

function remove_block_library_css()
{
	wp_dequeue_style('wp-block-library');
	wp_dequeue_style('wp-block-library-theme'); // Optional: removes block theme styles
	wp_dequeue_style('wp-block-library-style'); // Optional: removes inline styles (if any)
}
add_action('wp_enqueue_scripts', 'remove_block_library_css', 100);

// Add the action to dequeue and conditionally enqueue BBPress CSS
add_action('wp_enqueue_scripts', 'conditionally_load_bbpress_css', 20);

function conditionally_load_bbpress_css()
{
	// Dequeue default BBPress CSS
	wp_dequeue_style('bbp-default'); // Dequeues the default BBPress CSS handle

	// Check if the current URL path matches /forum/*
	if (is_forum_page()) {
		// Re-enqueue BBPress CSS file on /forum/* paths
		wp_enqueue_style('bbp-default'); // You can re-enqueue or load a custom CSS if preferred
	}
}

// Helper function to check if the current page is a forum page
function is_forum_page()
{
	// Get the current requested URL path
	$current_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

	// Check if the path starts with "forum" (adjust if needed)
	if (strpos($current_path, 'forums') === 0) {
		return true;
	}

	return false;
}
