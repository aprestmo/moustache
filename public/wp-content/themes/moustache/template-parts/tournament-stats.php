<?php

/**
 * Template part for displaying tournament statistics
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Get player statistics for tournament
 *
 * @param int $player_id Player ID
 * @param array $fixtures Array of fixture posts
 * @return array Player statistics
 */
function get_player_tournament_stats(int $player_id, array $fixtures): array
{
	$stats = [
		'matches' => 0,
		'goals' => 0,
		'assists' => 0,
		'yellow_cards' => 0,
		'red_cards' => 0
	];

	foreach ($fixtures as $fixture) {
		// Sjekk om spilleren var med i kampen
		$present = get_field('present', $fixture->ID);
		if (!$present || !in_array($player_id, array_column($present, 'ID'))) {
			continue;
		}

		$stats['matches']++;

		// Tell mål i første omgang
		if (have_rows('goals_assists_first_half', $fixture->ID)) {
			while (have_rows('goals_assists_first_half', $fixture->ID)) {
				the_row();
				if (get_sub_field('goal_scorer_first_half') && get_sub_field('goal_scorer_first_half')->ID === $player_id) {
					$stats['goals']++;
				}
				if (get_sub_field('assist_first_half') && get_sub_field('assist_first_half')->ID === $player_id) {
					$stats['assists']++;
				}
			}
		}

		// Tell mål i andre omgang
		if (have_rows('goals_assists_second_half', $fixture->ID)) {
			while (have_rows('goals_assists_second_half', $fixture->ID)) {
				the_row();
				if (get_sub_field('goal_scorer_second_half') && get_sub_field('goal_scorer_second_half')->ID === $player_id) {
					$stats['goals']++;
				}
				if (get_sub_field('assist_second_half') && get_sub_field('assist_second_half')->ID === $player_id) {
					$stats['assists']++;
				}
			}
		}

		// Tell kort i første omgang
		if (have_rows('cards_first_half', $fixture->ID)) {
			while (have_rows('cards_first_half', $fixture->ID)) {
				the_row();
				$card_player = get_sub_field('card_player_first_half');
				$card_colour = get_sub_field('card_colour_first_half');

				if ($card_player && $card_player->ID === $player_id) {
					if ($card_colour === 'yellow') {
						$stats['yellow_cards']++;
					} elseif ($card_colour === 'red') {
						$stats['red_cards']++;
					}
				}
			}
		}

		// Tell kort i andre omgang
		if (have_rows('cards_second_half', $fixture->ID)) {
			while (have_rows('cards_second_half', $fixture->ID)) {
				the_row();
				$card_player = get_sub_field('card_player_second_half');
				$card_colour = get_sub_field('card_colour_second_half');

				if ($card_player && $card_player->ID === $player_id) {
					if ($card_colour === 'yellow') {
						$stats['yellow_cards']++;
					} elseif ($card_colour === 'red') {
						$stats['red_cards']++;
					}
				}
			}
		}
	}

	return $stats;
}

// Hent alle kamper i turneringen
$fixtures_query = new WP_Query([
	'post_type' => 'fixture',
	'posts_per_page' => -1,
	'tax_query' => [
		[
			'taxonomy' => $term->taxonomy,
			'field'    => 'term_id',
			'terms'    => $term->term_id,
		],
	],
]);

if ($fixtures_query->have_posts()) :
	$fixtures = $fixtures_query->posts;
	$players = [];

	// Finn alle spillere som har deltatt
	foreach ($fixtures as $fixture) {
		$present = get_field('present', $fixture->ID);
		if ($present) {
			foreach ($present as $player) {
				if (!isset($players[$player->ID])) {
					$players[$player->ID] = [
						'name' => $player->post_title,
						'stats' => get_player_tournament_stats($player->ID, $fixtures)
					];
				}
			}
		}
	}

	// Sorter spillere etter mål (kan endres til annen sortering om ønskelig)
	uasort($players, function ($a, $b) {
		return strnatcasecmp($a['name'], $b['name']);
	});
?>

	<div class="table-scroll" role="region" aria-labelledby="tournament-stats" tabindex="0">
		<table>
			<caption id="tournament-stats"><?php esc_html_e('Tournament Statistics', 'moustache'); ?></caption>
			<thead>
				<tr>
					<th><?php esc_html_e('Name', 'moustache'); ?></th>
					<th><?php esc_html_e('Matches', 'moustache'); ?></th>
					<th><?php esc_html_e('Goals', 'moustache'); ?></th>
					<th><?php esc_html_e('Assists', 'moustache'); ?></th>
					<th><?php esc_html_e('Yellow Cards', 'moustache'); ?></th>
					<th><?php esc_html_e('Red Cards', 'moustache'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				// Initialiser totaler
				$totals = [
					'matches' => 0,
					'goals' => 0,
					'assists' => 0,
					'yellow_cards' => 0,
					'red_cards' => 0
				];

				foreach ($players as $player_id => $player) :
					// Summer opp totaler
					$totals['matches'] += $player['stats']['matches'];
					$totals['goals'] += $player['stats']['goals'];
					$totals['assists'] += $player['stats']['assists'];
					$totals['yellow_cards'] += $player['stats']['yellow_cards'];
					$totals['red_cards'] += $player['stats']['red_cards'];
				?>
					<tr>
						<th>
							<a href="<?php echo esc_url(get_permalink($player_id)); ?>">
								<?php echo esc_html($player['name']); ?>
							</a>
						</th>
						<td><?php echo esc_html($player['stats']['matches']); ?></td>
						<td><?php echo esc_html($player['stats']['goals']); ?></td>
						<td><?php echo esc_html($player['stats']['assists']); ?></td>
						<td><?php echo esc_html($player['stats']['yellow_cards']); ?></td>
						<td><?php echo esc_html($player['stats']['red_cards']); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<th><?php esc_html_e('Total', 'moustache'); ?></th>
					<th><?php // echo esc_html($totals['matches']); 
						?></th>
					<th><?php echo esc_html($totals['goals']); ?></th>
					<th><?php echo esc_html($totals['assists']); ?></th>
					<th><?php echo esc_html($totals['yellow_cards']); ?></th>
					<th><?php echo esc_html($totals['red_cards']); ?></th>
				</tr>
			</tfoot>
		</table>
	</div>

<?php
endif;
wp_reset_postdata();
?>