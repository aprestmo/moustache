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

// Set the default environment file to production
$envFile = '.env.production';

// Check if NODE_ENV is set in $_ENV or $_SERVER and adjust the .env file accordingly
if (file_exists(__DIR__ . '/.env.development') && ($_ENV['NODE_ENV'] ?? $_SERVER['NODE_ENV']) === 'development') {
	$envFile = '.env.development';
} elseif (file_exists(__DIR__ . '/.env.production') && ($_ENV['NODE_ENV'] ?? $_SERVER['NODE_ENV']) === 'production') {
	$envFile = '.env.production';
}

// Load the determined .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, $envFile);
$dotenv->load();

// Debugging output to verify the correct environment file is loaded
echo 'Loaded Environment File: ' . $envFile . '<br>';
echo '$_ENV NODE_ENV: ' . var_export($_ENV['NODE_ENV'] ?? 'not set', true) . '<br>';
echo '$_SERVER NODE_ENV: ' . var_export($_SERVER['NODE_ENV'] ?? 'not set', true) . '<br>';

// Set $nodeEnv based on the values found
$nodeEnv = $_ENV['NODE_ENV'] ?? $_SERVER['NODE_ENV'] ?? null;
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
				<img class="c-footer__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/kampbart-logo.svg'); ?>" alt="">
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