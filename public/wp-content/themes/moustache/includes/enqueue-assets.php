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
// function theme_enqueue()
// {
// 	wp_deregister_script('jquery');
// 	wp_enqueue_script('jquery', '//cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js', false, '3.6.0', true);

// 	//   wp_enqueue_style('site_main_css', get_template_directory_uri() . '/dist/assets/main.css');
// 	//   wp_enqueue_script('site_main_js', get_template_directory_uri() . '/dist/assets/main.js', null, null, true);
// }
// add_action('wp_enqueue_scripts', 'theme_enqueue');

function enqueue_vite_assets()
{
	// Check if we're in development mode (e.g., by setting a constant or checking the environment)
	$is_dev = defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local';

	// If in development, use the Vite server directly
	if ($is_dev) {
		echo '<script type="module" src="http://localhost:5173/src/js/main.js"></script>';
		echo '<link rel="stylesheet" href="http://localhost:5173/src/css/main.css">';
	} else {
		// In production, use the manifest to locate the files
		$manifest_path = get_template_directory() . '/dist/.vite/manifest.json';
		if (file_exists($manifest_path)) {
			$manifest = json_decode(file_get_contents($manifest_path), true);
			$js = $manifest['src/js/main.js']['file'] ?? '';
			$css = $manifest['src/css/style.css']['file'] ?? '';
			if ($js) {
				echo '<script type="module" src="' . get_template_directory_uri() . '/dist/' . $js . '"></script>';
			}
			if ($css) {
				echo '<link rel="stylesheet" href="' . get_template_directory_uri() . '/dist/' . $css . '">';
			}
		}
	}
}
add_action('wp_head', 'enqueue_vite_assets');
