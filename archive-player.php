<?php

/**
 * Player overview template
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

get_header();

/**
 * Get players by status
 *
 * @param string $status Player status (active/retired)
 * @return array Array of player posts
 */
function get_players_by_status(string $status): array
{
	return get_posts([
		'numberposts' => -1,
		'post_type'   => 'player',
		'orderby'     => 'title',
		'order'       => 'ASC',
		'meta_query'  => [
			[
				'key'     => 'status',
				'value'   => $status,
				'compare' => 'LIKE'
			]
		]
	]);
}

/**
 * Display player thumbnail and info
 *
 * @param WP_Post $player Player post object
 * @return void
 */
function display_player_card(WP_Post $player): void
{
	$image = get_field('image', $player->ID);
	$shirt_number = get_field('shirt_number', $player->ID);
?>
	<li class="o-grid__item u-1/2 u-1/4@md u-soft-md">
		<a href="<?php echo esc_url(get_permalink($player->ID)); ?>">
			<?php if ($image) : ?>
				<img src="<?php echo esc_url($image['sizes']['thumbnail']); ?>"
					alt="<?php echo esc_attr(get_the_title($player->ID)); ?>">
			<?php else : ?>
				<div style="aspect-ratio: 1 / 1 ; background-color: #eee"></div>
			<?php endif; ?>

			<span class="player-name">
				<?php echo esc_html(get_the_title($player->ID)); ?>
				<?php if ($shirt_number) : ?>
					(<?php echo esc_html($shirt_number); ?>)
				<?php endif; ?>
			</span>
		</a>
	</li>
<?php
}
?>

<div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
	<div class="o-grid u-text-center">
		<div class="o-grid__item u-1/1 u-2/3@sm">
			<section class="o-section-md">
				<?php
				// Get active players
				$active_players = get_players_by_status('active');
				if ($active_players) : ?>
					<header>
						<h1><?php esc_html_e('Aktive spillere', 'moustache'); ?></h1>
					</header>

					<ul class="players-grid">
						<?php
						foreach ($active_players as $player) {
							display_player_card($player);
						}
						?>
					</ul>
				<?php endif; ?>

				<?php
				// Get retired players
				$retired_players = get_players_by_status('retired');
				if ($retired_players) : ?>
					<header>
						<h2><?php esc_html_e('Spillere med barten på hylla', 'moustache'); ?></h2>
					</header>

					<ul class="players-list">
						<?php
						foreach ($retired_players as $player) : ?>
							<li>
								<a href="<?php echo esc_url(get_permalink($player->ID)); ?>">
									<?php echo esc_html(get_the_title($player->ID)); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if (empty($active_players) && empty($retired_players)) : ?>
					<p><?php esc_html_e('Ingen spillere funnet.', 'moustache'); ?></p>
				<?php endif; ?>
			</section>
		</div>
	</div>
</div>

<?php get_footer(); ?>