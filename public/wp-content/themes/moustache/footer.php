<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @package Moustache
 * @since 1.0
 * @version 1.0
 */
?></main>

<?php
require_once 'vendor/autoload.php';

// Determine which environment file to load based on NODE_ENV
$envFile = '.env'; // Default environment file

// Check if environment variables are set in $_ENV or $_SERVER
$nodeEnv = $_ENV['NODE_ENV'] ?? $_SERVER['NODE_ENV'] ?? 'production'; // Default to 'production' if not set

// Set the environment file based on the NODE_ENV value
if ($nodeEnv === 'development') {
	$envFile = '.env.development';
} elseif ($nodeEnv === 'production') {
	$envFile = '.env.production';
}

// Load the determined .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, $envFile);
$dotenv->load();

// Debugging output to verify the correct environment file is loaded
echo 'Loaded Environment File: ' . $envFile . '<br>';
echo 'NODE_ENV: ' . var_export($nodeEnv, true) . '<br>';

// Determine if the environment is in development mode
$isDevMode = $nodeEnv === 'development';
var_dump($isDevMode); // Should output true if in development mode

// Set the base URL for assets
$baseURL = $isDevMode ? '/public/' : '/dist/assets/';
?>

<footer class="c-footer" role="contentinfo">
	<div class="mou-site-wrap mou-site-wrap--padding">
		<div class="o-grid o-section-md">
			<div class="o-grid__item u-1/1 u-text-center">
				<img class="c-footer__logo" src="<?php echo get_template_directory_uri() . $baseURL; ?>kampbart-logo.svg" alt="">
				<p class="c-footer__slogan">
					<span class="u-visually-hidden"><?php echo esc_html(bloginfo('description')); ?></span>
					<img src="<?php echo esc_url(get_template_directory_uri() . '/dist/slogan.svg'); ?>" alt="" height="26">
				</p>
				<p class="c-footer__copyright">
					<?php echo esc_html(get_bloginfo('name')); ?>
					<br>
					<?php esc_html_e('Founded 2003', 'moustache'); ?>
					<br>
					<?php esc_html_e('Copyright', 'moustache'); ?> &copy; <?php echo esc_html(get_bloginfo('name')); ?> &ndash; <?php echo esc_html(date('Y')); ?>
				</p>
				<p>Se opp for nye barter i <?php echo date('Y'); ?>!</p>
			</div>
		</div>
	</div>
</footer>

<?php get_search_form(); ?>

<?php wp_footer(); ?>

</body>

</html>