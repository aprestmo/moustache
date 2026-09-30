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
 * Sort player stats by the column currently displayed (respects the
 * tournament filter) using WP_List_Table's orderby/order request vars.
 *
 * @param array<int, array> $stats                Player stats from moustache_get_all_players_stats().
 * @param int               $selected_tournament  Tournament term ID, or 0 for all tournaments.
 * @param string            $orderby              Column key: name, matches, goals, assists, yellow_cards, red_cards.
 * @param string            $order                'asc' or 'desc'.
 * @return array<int, array> Sorted stats (keys preserved).
 */
function moustache_sort_player_stats(array $stats, int $selected_tournament, string $orderby, string $order): array {
	$allowed = ['name', 'matches', 'goals', 'assists', 'yellow_cards', 'red_cards'];
	$orderby = in_array($orderby, $allowed, true) ? $orderby : 'name';
	$order   = $order === 'desc' ? 'desc' : 'asc';

	$value = static function (array $player) use ($selected_tournament, $orderby): int|string {
		if ('name' === $orderby) {
			return $player['name'];
		}
		$source = ($selected_tournament > 0 && isset($player['tournaments'][$selected_tournament]))
			? $player['tournaments'][$selected_tournament]
			: $player['total'];
		return $source[$orderby] ?? 0;
	};

	uasort($stats, static function (array $a, array $b) use ($value, $order): int {
		$a_value    = $value($a);
		$b_value    = $value($b);
		$comparison = is_string($a_value) || is_string($b_value)
			? strnatcasecmp($a_value, $b_value)
			: $a_value <=> $b_value;
		return 'desc' === $order ? -$comparison : $comparison;
	});

	return $stats;
}

/**
 * Player statistics list table (Tools → Spillerstatistikk).
 *
 * Extends WP_List_Table so column sorting works like every other admin list
 * table: clickable headers, orderby/order in the URL, aria-sort and sorting
 * indicators included. Sort links are built from the current request URL, so
 * the tournament filter survives sorting.
 */
class Moustache_Player_Stats_List_Table extends WP_List_Table {

	private int $selected_tournament = 0;
	private string $orderby           = 'name';
	private string $order             = 'asc';

	public function __construct() {
		parent::__construct([
			'singular' => 'spiller',
			'plural'   => 'spillere',
			'ajax'     => false,
		]);
	}

	/**
	 * Current filter/sort state, read from the request.
	 *
	 * @return array{tournament: int, orderby: string, order: string}
	 */
	public function get_sort_state(): array {
		return [
			'tournament' => $this->selected_tournament,
			'orderby'   => $this->orderby,
			'order'     => $this->order,
		];
	}

	public function prepare_items(): void {
		$this->selected_tournament = isset($_GET['tournament']) ? (int) $_GET['tournament'] : 0;
		$this->orderby            = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'name';
		$this->order              = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'desc' : 'asc';

		$stats = moustache_get_all_players_stats();

		if ($this->selected_tournament > 0) {
			$stats = array_filter($stats, function (array $player): bool {
				return isset($player['tournaments'][$this->selected_tournament]);
			});
		}

		$this->items = moustache_sort_player_stats($stats, $this->selected_tournament, $this->orderby, $this->order);

		// Set column headers directly instead of relying on the
		// manage_{screen}_columns filter, which can be cached empty when
		// get_column_headers() runs before this table is constructed.
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
			'name',
		];

		// Single page, but keeps the item count in the tablenav honest
		// instead of WP_List_Table's default "0 items".
		$this->set_pagination_args([
			'total_items' => count($this->items),
			'total_pages' => 1,
		]);
	}

	public function no_items(): void {
		esc_html_e('Ingen spillere funnet.', 'moustache');
	}

	public function get_columns(): array {
		return [
			'name'         => esc_html__('Spiller', 'moustache'),
			'matches'      => esc_html__('Kamper', 'moustache'),
			'goals'        => esc_html__('Mål', 'moustache'),
			'assists'      => esc_html__('Assists', 'moustache'),
			'yellow_cards' => esc_html__('Gule kort', 'moustache'),
			'red_cards'    => esc_html__('Røde kort', 'moustache'),
		];
	}

	protected function get_sortable_columns(): array {
		return [
			// The fifth element makes name the initially sorted column.
			'name'         => ['name', false, '', '', 'asc'],
			'matches'      => ['matches', false],
			'goals'        => ['goals', false],
			'assists'      => ['assists', false],
			'yellow_cards' => ['yellow_cards', false],
			'red_cards'    => ['red_cards', false],
		];
	}

	public function column_name($item): string {
		return esc_html($item['name']);
	}

	protected function column_default($item, $column_name): string {
		return esc_html((string) ($this->player_source($item)[$column_name] ?? 0));
	}

	protected function get_primary_column_aria_label($item): string {
		return $item['name'];
	}

	/**
	 * Totals row, rendered below the table (WP_List_Table has no footer slot
	 * for data rows — its tfoot repeats the column headers).
	 */
	protected function extra_tablenav($which): void {
		if ('bottom' !== $which || ! $this->has_items()) {
			return;
		}

		$totals = [
			'matches'      => 0,
			'goals'        => 0,
			'assists'      => 0,
			'yellow_cards' => 0,
			'red_cards'    => 0,
		];
		foreach ($this->items as $player) {
			$source               = $this->player_source($player);
			$totals['matches']      += $source['matches'];
			$totals['goals']        += $source['goals'];
			$totals['assists']      += $source['assists'];
			$totals['yellow_cards'] += $source['yellow_cards'];
			$totals['red_cards']    += $source['red_cards'];
		}
		?>
		<table class="widefat striped" style="margin-top: 0; border-top: 0;">
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
		<?php
	}

	private function player_source(array $player): array {
		return ($this->selected_tournament > 0 && isset($player['tournaments'][$this->selected_tournament]))
			? $player['tournaments'][$this->selected_tournament]
			: $player['total'];
	}
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

	$table = new Moustache_Player_Stats_List_Table();
	$table->prepare_items();

	$state                 = $table->get_sort_state();
	$selected_tournament   = $state['tournament'];
	$orderby               = $state['orderby'];
	$order                 = $state['order'];

	$tournaments = get_terms([
		'taxonomy' => 'tournament',
		'hide_empty' => false,
	]);

	?>
	<div class="wrap">
		<h1><?php esc_html_e('Spillerstatistikk', 'moustache'); ?></h1>

		<form method="get" style="margin-bottom: 20px;">
			<input type="hidden" name="page" value="player-stats">
			<input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>">
			<input type="hidden" name="order" value="<?php echo esc_attr($order); ?>">
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
			<a href="<?php echo esc_url(admin_url('tools.php?page=player-stats&export=csv&tournament=' . $selected_tournament . '&orderby=' . $orderby . '&order=' . $order)); ?>" class="button button-primary">
				<?php esc_html_e('Eksporter til CSV', 'moustache'); ?>
			</a>
		</form>

		<?php $table->display(); ?>
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

	$selected_tournament = isset($_GET['tournament']) ? (int) $_GET['tournament'] : 0;
	$orderby            = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'name';
	$order              = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'desc' : 'asc';

	$stats = moustache_get_all_players_stats();

	if ($selected_tournament > 0) {
		$stats = array_filter($stats, static function (array $player) use ($selected_tournament): bool {
			return isset($player['tournaments'][$selected_tournament]);
		});
	}

	$stats = moustache_sort_player_stats($stats, $selected_tournament, $orderby, $order);

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
