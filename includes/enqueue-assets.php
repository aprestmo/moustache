<?php
if (!defined('ABSPATH'))
	return;

/**
 * Enqueue scripts and styles for frontend.
 *
 * @since 6.0.0
 */

function enqueue_vite_assets()
{
	// wp_deregister_script('jquery');
	// wp_enqueue_script('jquery', '//cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js', false, '3.6.0', true);

	$is_dev = wp_get_environment_type() === 'local';

	if ($is_dev) {
		// In development, use Vite's dev server on default port 5173
		echo '<script type="module" src="http://localhost:5173/@vite/client"></script>';
		echo '<script type="module" src="http://localhost:5173/src/main.js"></script>';
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
		} else {
			// Broken deploy (missing/failed build) should be diagnosable, not silent.
			error_log(
				'moustache theme: Vite manifest missing at ' . $manifest_path
				. ' — front-end assets will NOT load. Build them on the server with ./deploy.sh (pnpm install && pnpm build).'
			);
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
function is_forum_page(): bool
{
	// Validate the request URI before matching: unslash the raw value and
	// reduce it to its path component (query string/hash can contain
	// anything).
	$uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
	$path = wp_parse_url($uri, PHP_URL_PATH);

	if (!is_string($path) || $path === '') {
		return false;
	}

	// Match "/forums" or "/forums/..." at the root only, with a slash
	// boundary so "/forumsXYZ" no longer counts as a forum page.
	return $path === '/forums' || str_starts_with($path, '/forums/');
}
