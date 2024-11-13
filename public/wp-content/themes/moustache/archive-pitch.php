<?php

/**
 * Pitch overview template
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

get_header();

/**
 * Get all pitches
 *
 * @return array Array of pitch posts
 */
function get_all_pitches(): array
{
	return get_posts([
		'numberposts' => -1,
		'post_type'   => 'pitch',
		'orderby'     => 'title',
		'order'       => 'ASC',
	]);
}

/**
 * Display pitch link
 *
 * @param WP_Post $pitch Pitch post object
 * @return void
 */
function display_pitch_link(WP_Post $pitch): void
{
?>
	<li class="o-grid__item u-1/2 u-1/5@md">
		<a href="<?php echo esc_url(get_permalink($pitch->ID)); ?>">
			<?php echo esc_html(get_the_title($pitch->ID)); ?>
		</a>
	</li>
<?php
}
?>

<div class="mou-site-wrap mou-site-wrap--padding wysiwyg u-soft-top-md u-push-bottom-lg">
	<?php
	$pitches = get_all_pitches();

	if ($pitches) : ?>
		<header>
			<h1><?php esc_html_e('Baneoversikt', 'moustache'); ?></h1>
		</header>

		<ul class="pitch-grid u-soft-top-md u-flush-left">
			<?php
			foreach ($pitches as $pitch) {
				display_pitch_link($pitch);
			}
			?>
		</ul>
	<?php else : ?>
		<p><?php esc_html_e('Ingen baner funnet.', 'moustache'); ?></p>
	<?php endif; ?>
</div>

<?php
get_footer();
