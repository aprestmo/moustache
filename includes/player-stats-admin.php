<?php
/**
 * Player statistics admin page
 *
 * @package Moustache
 */

defined('ABSPATH') || die('Shame on you');

/**
 * Get all player statistics grouped by tournament.
 *
 * @return array<int, array{
 *     name: string,
 *     tournaments: array<int, array{name: string, matches: int, goals: int, assists: int, yellow_cards: int, red_cards: int}>,
 *     total: array{matches: int, goals: int, assists: int, yellow_cards: int, red_cards: int}
 * }>
 */
function moustache_get_all_players_stats(): array {
	$players = get_posts([
		'post_type' => 'player',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'orderby' => 'title',
		'order' => 'ASC',
	]);

	$fixtures = get_posts([
		'post_type' => 'fixture',
		'posts_per_page' => -1,
		'post_status' => 'publish',
	]);

	$stats = [];

	foreach ($players as $player) {
		$stats[$player->ID] = [
			'name' => $player->post_title,
			'tournaments' => [],
			'total' => [
				'matches' => 0,
				'goals' => 0,
				'assists' => 0,
				'yellow_cards' => 0,
				'red_cards' => 0,
			],
		];
	}

	foreach ($fixtures as $fixture) {
		$present = moustache_get_present($fixture->ID);
		if (empty($present)) {
			continue;
		}

		$tournaments = wp_get_post_terms($fixture->ID, 'tournament');
		$tournament_ids = [];
		foreach ($tournaments as $term) {
			$tournament_ids[] = $term->term_id;
		}

		$present_ids = array_map(static fn(WP_Post $p) => $p->ID, $present);

		$goals = moustache_get_goals($fixture->ID);
		$cards = moustache_get_cards($fixture->ID);

		foreach ($present_ids as $player_id) {
			if (!isset($stats[$player_id])) {
				continue;
			}

			foreach ($tournament_ids as $term_id) {
				if (!isset($stats[$player_id]['tournaments'][$term_id])) {
					$term = get_term($term_id, 'tournament');
					$stats[$player_id]['tournaments'][$term_id] = [
						'name' => $term ? $term->name : 'Unknown',
						'matches' => 0,
						'goals' => 0,
						'assists' => 0,
						'yellow_cards' => 0,
						'red_cards' => 0,
					];
				}

				$stats[$player_id]['tournaments'][$term_id]['matches']++;
			}

			$stats[$player_id]['total']['matches']++;

			foreach ($goals as $goal) {
				if ($goal['scorer'] && $goal['scorer']->ID === $player_id) {
					$stats[$player_id]['total']['goals']++;
					foreach ($tournament_ids as $term_id) {
						$stats[$player_id]['tournaments'][$term_id]['goals']++;
					}
				}
				if ($goal['assist'] && $goal['assist']->ID === $player_id) {
					$stats[$player_id]['total']['assists']++;
					foreach ($tournament_ids as $term_id) {
						$stats[$player_id]['tournaments'][$term_id]['assists']++;
					}
				}
			}

			foreach ($cards as $card) {
				if (!$card['player'] || $card['player']->ID !== $player_id) {
					continue;
				}
				if ($card['colour'] === 'yellow') {
					$stats[$player_id]['total']['yellow_cards']++;
					foreach ($tournament_ids as $term_id) {
						$stats[$player_id]['tournaments'][$term_id]['yellow_cards']++;
					}
				} elseif ($card['colour'] === 'red') {
					$stats[$player_id]['total']['red_cards']++;
					foreach ($tournament_ids as $term_id) {
						$stats[$player_id]['tournaments'][$term_id]['red_cards']++;
					}
				}
			}
		}
	}

	return $stats;
}

/**
 * Render the player statistics admin page.
 */
function moustache_player_stats_admin_page(): void {
	if (!current_user_can('manage_options')) {
		return;
	}

	if (isset($_GET['export']) && $_GET['export'] === 'csv') {
		moustache_player_stats_export_csv();
		return;
	}

	$stats = moustache_get_all_players_stats();
	$tournaments = get_terms([
		'taxonomy' => 'tournament',
		'hide_empty' => false,
	]);

	$selected_tournament = isset($_GET['tournament']) ? (int) $_GET['tournament'] : 0;

	if ($selected_tournament > 0) {
		$stats = array_filter($stats, static function (array $player) use ($selected_tournament): bool {
			return isset($player['tournaments'][$selected_tournament]);
		});
	}

	uasort($stats, static fn(array $a, array $b): int => strnatcasecmp($a['name'], $b['name']));

	$totals = [
		'matches' => 0,
		'goals' => 0,
		'assists' => 0,
		'yellow_cards' => 0,
		'red_cards' => 0,
	];
	foreach ($stats as $player) {
		$source = ($selected_tournament > 0 && isset($player['tournaments'][$selected_tournament]))
			? $player['tournaments'][$selected_tournament]
			: $player['total'];
		$totals['matches'] += $source['matches'];
		$totals['goals'] += $source['goals'];
		$totals['assists'] += $source['assists'];
		$totals['yellow_cards'] += $source['yellow_cards'];
		$totals['red_cards'] += $source['red_cards'];
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e('Spillerstatistikk', 'moustache'); ?></h1>

		<form method="get" style="margin-bottom: 20px;">
			<input type="hidden" name="page" value="player-stats">
			<label for="tournament"><?php esc_html_e('Turnering:', 'moustache'); ?></label>
			<select name="tournament" id="tournament">
				<option value="0"><?php esc_html_e('Alle turneringer', 'moustache'); ?></option>
				<?php foreach ($tournaments as $term) : ?>
					<option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($selected_tournament, $term->term_id); ?>>
						<?php echo esc_html($term->name); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="button"><?php esc_html_e('Filtrer', 'moustache'); ?></button>
			<a href="<?php echo esc_url(admin_url('tools.php?page=player-stats&export=csv&tournament=' . $selected_tournament)); ?>" class="button button-primary">
				<?php esc_html_e('Eksporter til CSV', 'moustache'); ?>
			</a>
		</form>

		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e('Spiller', 'moustache'); ?></th>
					<th><?php esc_html_e('Kamper', 'moustache'); ?></th>
					<th><?php esc_html_e('Mål', 'moustache'); ?></th>
					<th><?php esc_html_e('Assists', 'moustache'); ?></th>
					<th><?php esc_html_e('Gule kort', 'moustache'); ?></th>
					<th><?php esc_html_e('Røde kort', 'moustache'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($stats as $player) :
					$source = ($selected_tournament > 0 && isset($player['tournaments'][$selected_tournament]))
						? $player['tournaments'][$selected_tournament]
						: $player['total'];
					?>
					<tr>
						<th><?php echo esc_html($player['name']); ?></th>
						<td><?php echo esc_html($source['matches']); ?></td>
						<td><?php echo esc_html($source['goals']); ?></td>
						<td><?php echo esc_html($source['assists']); ?></td>
						<td><?php echo esc_html($source['yellow_cards']); ?></td>
						<td><?php echo esc_html($source['red_cards']); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<th><?php esc_html_e('Total', 'moustache'); ?></th>
					<th><?php echo esc_html($totals['matches']); ?></th>
					<th><?php echo esc_html($totals['goals']); ?></th>
					<th><?php echo esc_html($totals['assists']); ?></th>
					<th><?php echo esc_html($totals['yellow_cards']); ?></th>
					<th><?php echo esc_html($totals['red_cards']); ?></th>
				</tr>
			</tfoot>
		</table>
	</div>
	<?php
}

/**
 * Export player statistics to CSV.
 */
function moustache_player_stats_export_csv(): void {
	if (!current_user_can('manage_options')) {
		wp_die(esc_html__('Unauthorized', 'moustache'));
	}

	$stats = moustache_get_all_players_stats();

	$selected_tournament = isset($_GET['tournament']) ? (int) $_GET['tournament'] : 0;

	if ($selected_tournament > 0) {
		$stats = array_filter($stats, static function (array $player) use ($selected_tournament): bool {
			return isset($player['tournaments'][$selected_tournament]);
		});
	}

	uasort($stats, static fn(array $a, array $b): int => strnatcasecmp($a['name'], $b['name']));

	$filename = 'spillerstatistikk-' . date('Y-m-d') . '.csv';

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Pragma: no-cache');
	header('Expires: 0');

	// UTF-8 BOM for Excel
	echo "\xEF\xBB\xBF";

	$output = fopen('php://output', 'w');

	fputcsv($output, [
		__('Spiller', 'moustache'),
		__('Kamper', 'moustache'),
		__('Mål', 'moustache'),
		__('Assists', 'moustache'),
		__('Gule kort', 'moustache'),
		__('Røde kort', 'moustache'),
	], ';');

	foreach ($stats as $player) {
		$source = ($selected_tournament > 0 && isset($player['tournaments'][$selected_tournament]))
			? $player['tournaments'][$selected_tournament]
			: $player['total'];
		fputcsv($output, [
			$player['name'],
			$source['matches'],
			$source['goals'],
			$source['assists'],
			$source['yellow_cards'],
			$source['red_cards'],
		], ';');
	}

	fclose($output);
	exit;
}

add_action('admin_menu', function (): void {
	add_management_page(
		__('Spillerstatistikk', 'moustache'),
		__('Spillerstatistikk', 'moustache'),
		'manage_options',
		'player-stats',
		'moustache_player_stats_admin_page'
	);
});
