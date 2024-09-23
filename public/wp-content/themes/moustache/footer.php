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

require_once 'vendor/autoload.php';

// Load the .env.development file explicitly
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env.development');
$dotenv->load();

// Check the environment variable directly from $_ENV or $_SERVER
$nodeEnv = $_ENV['NODE_ENV'] ?? $_SERVER['NODE_ENV'] ?? false; // Use $_ENV or fallback to $_SERVER
echo 'NODE_ENV: ' . var_export($nodeEnv, true) . '<br>';

// Correctly determine if it's in development mode
$isDevMode = $nodeEnv === 'development';
var_dump($isDevMode); // Should output true if in development mode

// Set the base URL for assets
$baseURL = $isDevMode ? '/public/' : '/dist/assets/';
?></main>

<?php
// Explicitly specify the correct .env file path
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env.development'); // Adjust path as needed
$dotenv->load();

// Debug to see if the environment variable is set
echo '<pre>';
print_r($_ENV);  // Check what environment variables are loaded into $_ENV
print_r($_SERVER); // Also check $_SERVER for loaded variables
echo '</pre>';

// Check directly if NODE_ENV is in the environment arrays
echo '<pre>';
echo '$_ENV NODE_ENV: ' . var_export($_ENV['NODE_ENV'] ?? 'not set', true) . '<br>';
echo '$_SERVER NODE_ENV: ' . var_export($_SERVER['NODE_ENV'] ?? 'not set', true) . '<br>';
echo '</pre>';

$nodeEnv = getenv('NODE_ENV');
echo 'NODE_ENV: ' . var_export($nodeEnv, true) . '<br>'; // Check what NODE_ENV contains
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