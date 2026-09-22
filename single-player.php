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
	'dob' => moustache_format_acf_date(get_field('dob')),
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

			foreach (moustache_get_goals($match_id) as $goal) {
				$half_key = $goal['half'] === 'second' ? 'Second' : 'First';
				if ($goal['scorer'] && $goal['scorer']->ID === $player_id) {
					$stats[$term->name]->{'goals' . $half_key}++;
				}
				if ($goal['assist'] && $goal['assist']->ID === $player_id) {
					$stats[$term->name]->{'assists' . $half_key}++;
				}
			}

			foreach (moustache_get_cards($match_id) as $card) {
				if (!$card['player'] || $card['player']->ID !== $player_id) {
					continue;
				}
				$half_key = $card['half'] === 'second' ? 'Second' : 'First';
				if ($card['colour'] === 'yellow') {
					$stats[$term->name]->{'yellowCards' . $half_key}++;
				} elseif ($card['colour'] === 'red') {
					$stats[$term->name]->{'redCards' . $half_key}++;
				}
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
