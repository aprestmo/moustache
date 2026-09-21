<?php

/**
 * Template part for displaying next match
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Get next match
 *
 * @return array Array of fixture posts
 */
function get_next_match(): array
{
	return get_posts([
		'post_type'      => 'fixture',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_key'       => 'date_time',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_query'     => [
			[
				'key'     => 'date_time',
				'compare' => '>',
				'value'   => date('Y-m-d H:i:s'),
			]
		]
	]);
}

/**
 * Get pitch name
 */
function get_pitch_name(?WP_Post $pitch): string
{
	return $pitch ? $pitch->post_title : '';
}

/**
 * Format match date
 *
 * @param string $datetime DateTime string
 * @return array
 */
function format_match_date(string $datetime): array
{
	$timestamp = strtotime($datetime);
	return [
		'weekday' => date_i18n('l j.', $timestamp),
		'month'   => date_i18n('F', $timestamp),
		'time'    => date_i18n('H.i', $timestamp)
	];
}

$next_match = get_next_match();

if ($next_match) :
	foreach ($next_match as $post) :
		setup_postdata($post);

		if (moustache_fixture_unplayed_reason(get_the_ID())) {
			continue;
		}

		$home_team = moustache_get_home_team();
		$away_team = moustache_get_away_team();
		$when = moustache_get_fixture_datetime();
		$pitch = moustache_get_pitch();

		if ($when === '') {
			continue;
		}

		$ground = get_pitch_name($pitch);
		$hosts = $home_team ? $home_team->post_title : '';
		$guests = $away_team ? $away_team->post_title : '';
		$date = format_match_date($when);
?>

		<div class="c-upcoming">
			<div class="mou-site-wrap mou-site-wrap--padding">
				<div class="o-grid c-upcoming__item">
					<p class="o-grid__item u-flush-bottom u-text-center">
						<img class="c-upcoming__icon"
							src="<?php echo esc_url(get_template_directory_uri()); ?>/dist/icons/ball.svg"
							alt=""
							width="18"
							height="18">
						<b><?php esc_html_e('Next match:', 'moustache'); ?></b>
						<?php echo esc_html($hosts); ?>&ndash;<?php echo esc_html($guests); ?>.
						<?php echo esc_html(ucfirst($date['weekday'])); ?>
						<?php echo esc_html($date['month']); ?>
						<?php esc_html_e('at', 'moustache'); ?>
						<?php echo esc_html($date['time']); ?>,
						<?php echo esc_html($ground); ?>.
					</p>
				</div>
			</div>
		</div>

<?php
		break; // Stop loop after first valid post
	endforeach;
	wp_reset_postdata();
endif;
?>