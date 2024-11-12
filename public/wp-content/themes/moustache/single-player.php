<?php

/**
 * Single player template
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

get_header();

// Initialize player data
$player_id = get_the_ID();
$player_data = [
	'image' => get_field('image'),
	'shirt_number' => get_field('shirt_number'),
	'shirt_name' => get_field('shirt_name'),
	'dob' => get_field('dob'),
	'position' => get_field('position'),
	'position_labels' => get_field_object('position'),
	'former_clubs' => get_field('former_clubs'),
	'best_memory' => get_field('best_memory')
];

// Get player statistics
function get_player_matches($player_id)
{
	return get_posts([
		'post_type' => 'fixture',
		'fields' => 'ids',
		'nopaging' => true,
		'posts_per_page' => -1,
		'meta_key' => 'present',
		'meta_value' => '"' . $player_id . '"',
		'meta_compare' => 'LIKE',
	]);
}

function initialize_season_stats()
{
	return (object) [
		'count' => 0,
		'name' => '',
		'slug' => '',
		'ids' => [],
		'goalsFirst' => 0,
		'goalsSecond' => 0,
		'assistsFirst' => 0,
		'assistsSecond' => 0,
		'yellowCardsFirst' => 0,
		'yellowCardsSecond' => 0,
		'redCardsFirst' => 0,
		'redCardsSecond' => 0
	];
}

function count_goals($post_id, $player_id, &$stats, $term_name, $half = 'first')
{
	$field_name = "goals_assists_{$half}_half";
	$scorer_field = "goal_scorer_{$half}_half";

	while (have_rows($field_name, $post_id)) {
		the_row();
		$scorer = get_sub_field($scorer_field);
		if ($scorer && $scorer->ID === $player_id) {
			$stats[$term_name]->{$half === 'first' ? 'goalsFirst' : 'goalsSecond'}++;
		}
	}
}

function count_assists($post_id, $player_id, &$stats, $term_name, $half = 'first')
{
	$field_name = "goals_assists_{$half}_half";
	$assist_field = "assist_{$half}_half";

	while (have_rows($field_name, $post_id)) {
		the_row();
		$assist = get_sub_field($assist_field);
		if ($assist && $assist->ID === $player_id) {
			$stats[$term_name]->{$half === 'first' ? 'assistsFirst' : 'assistsSecond'}++;
		}
	}
}

function count_cards($post_id, $player_id, &$stats, $term_name, $half = 'first')
{
	$field_name = "cards_{$half}_half";
	$player_field = "card_player_{$half}_half";
	$colour_field = "card_colour_{$half}_half";

	while (have_rows($field_name, $post_id)) {
		the_row();
		$card_player = get_sub_field($player_field);
		$card_colour = get_sub_field($colour_field);

		if ($card_player && $card_player->ID === $player_id) {
			if ($card_colour === 'yellow') {
				$stats[$term_name]->{$half === 'first' ? 'yellowCardsFirst' : 'yellowCardsSecond'}++;
			}
		}
	}
}

// Calculate statistics
$matches = get_player_matches($player_id);
$stats = [];
$totals = [
	'matches' => 0,
	'goals' => 0,
	'assists' => 0,
	'yellowCards' => 0,
	'redCards' => 0
];

foreach ($matches as $match_id) {
	$terms = get_the_terms($match_id, 'tournament');

	// Sjekk om $terms er et gyldig array før vi fortsetter
	if (!empty($terms) && !is_wp_error($terms)) {
		foreach ($terms as $term) {
			if (!isset($stats[$term->name])) {
				$stats[$term->name] = initialize_season_stats();
				$stats[$term->name]->name = $term->name;
				$stats[$term->name]->slug = $term->slug;
			}

			$stats[$term->name]->ids[] = $match_id;
			$stats[$term->name]->count++;

			// Count statistics for both halves
			foreach (['first', 'second'] as $half) {
				count_goals($match_id, $player_id, $stats, $term->name, $half);
				count_assists($match_id, $player_id, $stats, $term->name, $half);
				count_cards($match_id, $player_id, $stats, $term->name, $half);
			}
		}
	}
}
?>

<div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
	<div class="o-grid u-text-center">
		<div class="o-grid__item u-1/1 u-2/3@sm">
			<section class="o-section-md" data-layout="player">
				<h1><?php the_title(); ?></h1>

				<main>
					<?php include(locate_template('template-parts/player-details.php')); ?>
					<?php include(locate_template('template-parts/player-statistics.php')); ?>
				</main>
			</section>
		</div>
	</div>
</div>

<?php
get_footer();
