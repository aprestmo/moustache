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
	wp_deregister_script('jquery');
	wp_enqueue_script('jquery', '//cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js', false, '3.6.0', true);

	$is_dev = defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local';

	if ($is_dev) {
		// In development, use Vite's dev server on default port 5173
		// wp_enqueue_script('vite-main', 'http://localhost:5173/src/main.js', [], null, true);
		// wp_enqueue_style('vite-main-style', 'http://localhost:5173/src/css/main.css', [], null);
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
		}
	}
}
add_action('wp_enqueue_scripts', 'enqueue_vite_assets');
