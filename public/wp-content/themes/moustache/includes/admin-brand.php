<?php

/**
 * Change logo on admin login
 *
 * @since 1.0
 */
function starter_login_logo()
{ ?>
	<style type="text/css">
		.login h1 a {
			background-image: url(<?php echo esc_url(get_template_directory_uri()); ?>/dist/kampbart-logo.svg);
			background-size: 144px;
			padding-bottom: 85px;
			width: 144px;
		}
	</style>
<?php }
add_action('login_enqueue_scripts', 'starter_login_logo');

/**
 * Set link on logo to your site
 *
 * @since 1.0
 */

function starter_login_logo_url()
{
	return home_url();
}
add_filter('login_headerurl', 'starter_login_logo_url');

/**
 * Specify link text on logo image
 *
 * @since 1.0
 */

function starter_login_logo_url_title()
{
	return esc_html__('Go to site', 'moustache');
}
add_filter('login_headertitle', 'starter_login_logo_url_title');
