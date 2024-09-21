<?php
//Die if accessed directly
defined('ABSPATH') || die('Shame on you');

/**
 * Author: Alexander Prestmo
 *Author URI: http://attityd.no
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

function lt_html_excerpt($text) {
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

/* Vite */
function my_vite_enqueue_scripts() {
	if ( WP_ENVIRONMENT_TYPE === 'local' ) {
		// Vite HMR connection in development
		echo '<script type="module" src="http://localhost:5173/@vite/client"></script>';
		echo '<script type="module" src="http://localhost:5173/src/main.js"></script>';
	} else {
		// Production assets
		wp_enqueue_style( 'my-vite-theme-style', get_template_directory_uri() . '/dist/main.css', [], '1.0.0' );
		wp_enqueue_script( 'my-vite-theme-script', get_template_directory_uri() . '/dist/main.js', [], '1.0.0', true );
	}
}
add_action( 'wp_enqueue_scripts', 'my_vite_enqueue_scripts' );

/* remove the default filter */
remove_filter('get_the_excerpt', 'wp_trim_excerpt');

/* now, add your own filter */
add_filter('get_the_excerpt', 'lt_html_excerpt');
