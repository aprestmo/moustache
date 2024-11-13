<?php

/**
 * Clubs overview template
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

get_header();

/**
 * Get all clubs
 *
 * @return array Array of club posts
 */
function get_all_clubs(): array
{
	return get_posts([
		'numberposts' => -1,
		'post_type'   => 'club',
		'orderby'     => 'title',
		'order'       => 'ASC',
	]);
}

/**
 * Display club link
 *
 * @param WP_Post $club Club post object
 * @return void
 */
function display_club_link(WP_Post $club): void
{
?>
	<li class="o-grid__item u-1/2 u-1/5@md">
		<a href="<?php echo esc_url(get_permalink($club->ID)); ?>">
			<?php echo esc_html(get_the_title($club->ID)); ?>
		</a>
	</li>
<?php
}
?>

<div class="mou-site-wrap mou-site-wrap--padding wysiwyg u-soft-top-md u-push-bottom-lg">
	<?php
	$clubs = get_all_clubs();

	if ($clubs) : ?>
		<header>
			<h1><?php esc_html_e('Klubboversikt', 'moustache'); ?></h1>
		</header>

		<ul class="clubs-grid u-soft-top-md u-flush-left">
			<?php
			foreach ($clubs as $club) {
				display_club_link($club);
			}
			?>
		</ul>
	<?php else : ?>
		<p><?php esc_html_e('Ingen klubber funnet.', 'moustache'); ?></p>
	<?php endif; ?>
</div>

<?php
get_footer();
