<?php
/**
 * Opponent statistics admin page
 *
 * @package Moustache
 */

defined('ABSPATH') || die('Shame on you');

/**
 * Get all opponent statistics grouped by tournament.
 *
 * @return array<int, array{
 *     name: string,
 *     club_id: int,
 *     tournaments: array<int, array{name: string, played: int, wins: int, draws: int, losses: int, gf: int, ga: int, walkovers: int, abandoned: int}>,
 *     total: array{played: int, wins: int, draws: int, losses: int, gf: int, ga: int, walkovers: int, abandoned: int}
 * }>
 */
function moustache_get_all_opponent_stats(): array {
	$fixtures = get_posts([
		'post_type'      => 'fixture',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'meta_query'     => [
			'relation' => 'OR',
			[
				'key'     => 'home_team',
				'compare' => 'EXISTS',
			],
			[
				'key'     => 'away_team',
				'compare' => 'EXISTS',
			],
		],
	]);

	$stats = [];

	foreach ($fixtures as $fixture) {
		$fixture_id = $fixture->ID;

		$home_team = moustache_get_home_team($fixture_id);
		$away_team = moustache_get_away_team($fixture_id);

		// Must have exactly one Kampbart side.
		$kampbart_home = moustache_is_kampbart($home_team);
		$kampbart_away = moustache_is_kampbart($away_team);
		if ($kampbart_home === $kampbart_away) {
			continue;
		}

		$opponent = $kampbart_home ? $away_team : $home_team;
		if (!$opponent) {
			continue;
		}

		$opponent_id = (int) $opponent->ID;
		$opponent_name = $opponent->post_title;

		// Tournament for this fixture.
		$tournaments = wp_get_object_terms($fixture_id, 'tournament');
		$tournament_ids = [];
		if (!is_wp_error($tournaments)) {
			foreach ($tournaments as $term) {
				$tournament_ids[] = (int) $term->term_id;
			}
		}

		// Determine result status.
		$unplayed_reason = moustache_fixture_unplayed_reason($fixture_id);
		$walkover = (bool) get_field('walkover', $fixture_id);
		$walkover_winner = $walkover ? get_field('walkover_winner', $fixture_id) : null;
		$walkover_result = $walkover ? (int) get_field('walkover_result', $fixture_id) : 0;

		// Classify: only actually played matches count toward "played".
		// Walkover -> only in walkovers column.
		// Abandoned -> only in abandoned column.
		// Played matches -> played, wins/draws/losses, goals.
		$played = false;
		$win = $draw = $loss = false;
		$gf = $ga = 0;

		if ($walkover) {
			// Walkover: counted only in walkovers column per user decision.
			// No played/wins/draws/losses/goals.
		} elseif ($unplayed_reason === 'abandoned') {
			// Abandoned: only in abandoned column.
		} else {
			// Try to get a result: goals repeater, then numeric fields, then legacy text.
			$goals = moustache_get_goals($fixture_id);
			$kampbart_goals = 0;
			$opponent_goals = 0;

			if ($goals !== []) {
				foreach ($goals as $goal) {
					if (($goal['side'] ?? '') === 'kampbart') {
						$kampbart_goals++;
					} else {
						$opponent_goals++;
					}
				}
				$played = true;
			} else {
				$recorded = moustache_get_recorded_result($fixture_id);
				$recorded_ft = null;
				if ($recorded['home_ft'] !== null && $recorded['away_ft'] !== null) {
					$recorded_ft = [$recorded['home_ft'], $recorded['away_ft']];
				} elseif ($recorded['text_ft'] !== '') {
					$recorded_ft = moustache_parse_score_text($recorded['text_ft']);
				}

				if ($recorded_ft !== null) {
					if ($kampbart_home) {
						$kampbart_goals = $recorded_ft[0];
						$opponent_goals = $recorded_ft[1];
					} else {
						$kampbart_goals = $recorded_ft[1];
						$opponent_goals = $recorded_ft[0];
					}
					$played = true;
				}
			}

			if ($played) {
				$gf = $kampbart_goals;
				$ga = $opponent_goals;
				if ($kampbart_goals > $opponent_goals) {
					$win = true;
				} elseif ($kampbart_goals === $opponent_goals) {
					$draw = true;
				} else {
					$loss = true;
				}
			}
		}

		// Aggregate per opponent.
		if (!isset($stats[$opponent_id])) {
			$stats[$opponent_id] = [
				'name'        => $opponent_name,
				'club_id'     => $opponent_id,
				'tournaments' => [],
				'total'       => [
					'played'     => 0,
					'wins'       => 0,
					'draws'      => 0,
					'losses'     => 0,
					'gf'         => 0,
					'ga'         => 0,
					'walkovers'  => 0,
					'abandoned'  => 0,
				],
			];
		}

		// Add to total.
		if ($played) {
			$stats[$opponent_id]['total']['played']++;
			$stats[$opponent_id]['total']['gf'] += $gf;
			$stats[$opponent_id]['total']['ga'] += $ga;
			if ($win) {
				$stats[$opponent_id]['total']['wins']++;
			} elseif ($draw) {
				$stats[$opponent_id]['total']['draws']++;
			} else {
				$stats[$opponent_id]['total']['losses']++;
			}
		}
		if ($walkover) {
			$stats[$opponent_id]['total']['walkovers']++;
		}
		if ($unplayed_reason === 'abandoned') {
			$stats[$opponent_id]['total']['abandoned']++;
		}

		// Add to each tournament.
		foreach ($tournament_ids as $tid) {
			if (!isset($stats[$opponent_id]['tournaments'][$tid])) {
				$term = get_term($tid, 'tournament');
				$t_name = $term && !is_wp_error($term) ? $term->name : "Turnering $tid";
				$stats[$opponent_id]['tournaments'][$tid] = [
					'name'     => $t_name,
					'played'   => 0,
					'wins'     => 0,
					'draws'    => 0,
					'losses'   => 0,
					'gf'       => 0,
					'ga'       => 0,
					'walkovers' => 0,
					'abandoned'  => 0,
				];
			}

			$t = &$stats[$opponent_id]['tournaments'][$tid];
			if ($played) {
				$t['played']++;
				$t['gf'] += $gf;
				$t['ga'] += $ga;
				if ($win) {
					$t['wins']++;
				} elseif ($draw) {
					$t['draws']++;
				} else {
					$t['losses']++;
				}
			}
			if ($walkover) {
				$t['walkovers']++;
			}
			if ($unplayed_reason === 'abandoned') {
				$t['abandoned']++;
			}
		}

		// Also aggregate for opponents with no tournament (tid = 0 bucket).
		if ($tournament_ids === []) {
			if (!isset($stats[$opponent_id]['tournaments'][0])) {
				$stats[$opponent_id]['tournaments'][0] = [
					'name'     => __('Uten turnering', 'moustache'),
					'played'   => 0,
					'wins'     => 0,
					'draws'    => 0,
					'losses'   => 0,
					'gf'       => 0,
					'ga'       => 0,
					'walkovers' => 0,
					'abandoned'  => 0,
				];
			}
			$t = &$stats[$opponent_id]['tournaments'][0];
			if ($played) {
				$t['played']++;
				$t['gf'] += $gf;
				$t['ga'] += $ga;
				if ($win) {
					$t['wins']++;
				} elseif ($draw) {
					$t['draws']++;
				} else {
					$t['losses']++;
				}
			}
			if ($walkover) {
				$t['walkovers']++;
			}
			if ($unplayed_reason === 'abandoned') {
				$t['abandoned']++;
			}
		}
	}

	// Sort by name.
	uksort($stats, function (int $a, int $b) use ($stats): int {
		return strnatcasecmp($stats[$a]['name'], $stats[$b]['name']);
	});

	return $stats;
}

/**
 * Sort opponent stats by the column currently displayed.
 *
 * @param array<int, array> $stats
 * @param int               $selected_tournament
 * @param string            $orderby
 * @param string            $order
 * @return array<int, array>
 */
function moustache_sort_opponent_stats(array $stats, int $selected_tournament, string $orderby, string $order): array {
	$allowed = ['name', 'played', 'wins', 'draws', 'losses', 'gf', 'ga', 'walkovers', 'abandoned'];
	$orderby = in_array($orderby, $allowed, true) ? $orderby : 'name';
	$order   = $order === 'desc' ? 'desc' : 'asc';

	$value = static function (array $opponent) use ($selected_tournament, $orderby): int|string {
		if ('name' === $orderby) {
			return $opponent['name'];
		}
		$source = ($selected_tournament > 0 && isset($opponent['tournaments'][$selected_tournament]))
			? $opponent['tournaments'][$selected_tournament]
			: $opponent['total'];
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
 * Opponent statistics list table (Tools → Motstanderstatistikk).
 */
class Moustache_Opponent_Stats_List_Table extends WP_List_Table {

	private int $selected_tournament = 0;
	private string $orderby           = 'name';
	private string $order             = 'asc';

	public function __construct() {
		parent::__construct([
			'singular' => 'motstander',
			'plural'   => 'motstandere',
			'ajax'     => false,
		]);
	}

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

		$stats = moustache_get_all_opponent_stats();

		if ($this->selected_tournament > 0) {
			$stats = array_filter($stats, function (array $opponent): bool {
				return isset($opponent['tournaments'][$this->selected_tournament]);
			});
		}

		$this->items = moustache_sort_opponent_stats($stats, $this->selected_tournament, $this->orderby, $this->order);

		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
			'name',
		];

		$this->set_pagination_args([
			'total_items' => count($this->items),
			'total_pages' => 1,
		]);
	}

	public function no_items(): void {
		esc_html_e('Ingen motstandere funnet.', 'moustache');
	}

	public function get_columns(): array {
		return [
			'name'       => esc_html__('Lag', 'moustache'),
			'played'     => esc_html__('Møtt', 'moustache'),
			'wins'       => esc_html__('Seire', 'moustache'),
			'draws'      => esc_html__('Uavgjort', 'moustache'),
			'losses'     => esc_html__('Tap', 'moustache'),
			'gf'         => esc_html__('Mål for', 'moustache'),
			'ga'         => esc_html__('Mål mot', 'moustache'),
			'walkovers'  => esc_html__('Walkover', 'moustache'),
			'abandoned'  => esc_html__('Avlyst', 'moustache'),
		];
	}

	protected function get_sortable_columns(): array {
		return [
			'name'      => ['name', false, '', '', 'asc'],
			'played'    => ['played', false],
			'wins'      => ['wins', false],
			'draws'     => ['draws', false],
			'losses'    => ['losses', false],
			'gf'        => ['gf', false],
			'ga'        => ['ga', false],
			'walkovers' => ['walkovers', false],
			'abandoned' => ['abandoned', false],
		];
	}

	public function column_name($item): string {
		return esc_html($item['name']);
	}

	protected function column_default($item, $column_name): string {
		return esc_html((string) ($this->opponent_source($item)[$column_name] ?? 0));
	}

	protected function get_primary_column_aria_label($item): string {
		return $item['name'];
	}

	protected function extra_tablenav($which): void {
		if ('bottom' !== $which || ! $this->has_items()) {
			return;
		}

		$totals = [
			'played'    => 0,
			'wins'      => 0,
			'draws'     => 0,
			'losses'    => 0,
			'gf'        => 0,
			'ga'        => 0,
			'walkovers' => 0,
			'abandoned' => 0,
		];
		foreach ($this->items as $opponent) {
			$source = $this->opponent_source($opponent);
			$totals['played']    += $source['played'];
			$totals['wins']      += $source['wins'];
			$totals['draws']     += $source['draws'];
			$totals['losses']    += $source['losses'];
			$totals['gf']        += $source['gf'];
			$totals['ga']        += $source['ga'];
			$totals['walkovers'] += $source['walkovers'];
			$totals['abandoned'] += $source['abandoned'];
		}
		?>
		<table class="widefat striped" style="margin-top: 0; border-top: 0;">
			<tfoot>
				<tr>
					<th><?php esc_html_e('Total', 'moustache'); ?></th>
					<th><?php echo esc_html($totals['played']); ?></th>
					<th><?php echo esc_html($totals['wins']); ?></th>
					<th><?php echo esc_html($totals['draws']); ?></th>
					<th><?php echo esc_html($totals['losses']); ?></th>
					<th><?php echo esc_html($totals['gf']); ?></th>
					<th><?php echo esc_html($totals['ga']); ?></th>
					<th><?php echo esc_html($totals['walkovers']); ?></th>
					<th><?php echo esc_html($totals['abandoned']); ?></th>
				</tr>
			</tfoot>
		</table>
		<?php
	}

	private function opponent_source(array $opponent): array {
		return ($this->selected_tournament > 0 && isset($opponent['tournaments'][$this->selected_tournament]))
			? $opponent['tournaments'][$this->selected_tournament]
			: $opponent['total'];
	}
}

/**
 * Render the opponent statistics admin page.
 */
function moustache_opponent_stats_admin_page(): void {
	if (!current_user_can('manage_options')) {
		return;
	}

	if (isset($_GET['export']) && $_GET['export'] === 'csv') {
		moustache_opponent_stats_export_csv();
		return;
	}

	$table = new Moustache_Opponent_Stats_List_Table();
	$table->prepare_items();

	$state                 = $table->get_sort_state();
	$selected_tournament   = $state['tournament'];
	$orderby               = $state['orderby'];
	$order                 = $state['order'];

	$tournaments = get_terms([
		'taxonomy'   => 'tournament',
		'hide_empty' => false,
	]);

	?>
	<div class="wrap">
		<h1><?php esc_html_e('Motstandere', 'moustache'); ?></h1>

		<form method="get" style="margin-bottom: 20px;">
			<input type="hidden" name="page" value="opponent-stats">
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
			<a href="<?php echo esc_url(admin_url('tools.php?page=opponent-stats&export=csv&tournament=' . $selected_tournament . '&orderby=' . $orderby . '&order=' . $order)); ?>" class="button button-primary">
				<?php esc_html_e('Eksporter til CSV', 'moustache'); ?>
			</a>
		</form>

		<?php $table->display(); ?>
	</div>
	<?php
}

/**
 * Export opponent statistics to CSV.
 */
function moustache_opponent_stats_export_csv(): void {
	if (!current_user_can('manage_options')) {
		wp_die(esc_html__('Unauthorized', 'moustache'));
	}

	$selected_tournament = isset($_GET['tournament']) ? (int) $_GET['tournament'] : 0;
	$orderby            = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'name';
	$order              = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'desc' : 'asc';

	$stats = moustache_get_all_opponent_stats();

	if ($selected_tournament > 0) {
		$stats = array_filter($stats, static function (array $opponent) use ($selected_tournament): bool {
			return isset($opponent['tournaments'][$selected_tournament]);
		});
	}

	$stats = moustache_sort_opponent_stats($stats, $selected_tournament, $orderby, $order);

	$filename = 'motstanderstatistikk-' . date('Y-m-d') . '.csv';

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Pragma: no-cache');
	header('Expires: 0');

	// UTF-8 BOM for Excel
	echo "\xEF\xBB\xBF";

	$output = fopen('php://output', 'w');

	fputcsv($output, [
		__('Lag', 'moustache'),
		__('Møtt', 'moustache'),
		__('Seire', 'moustache'),
		__('Uavgjort', 'moustache'),
		__('Tap', 'moustache'),
		__('Mål for', 'moustache'),
		__('Mål mot', 'moustache'),
		__('Walkover', 'moustache'),
		__('Avlyst', 'moustache'),
	], ';');

	foreach ($stats as $opponent) {
		$source = ($selected_tournament > 0 && isset($opponent['tournaments'][$selected_tournament]))
			? $opponent['tournaments'][$selected_tournament]
			: $opponent['total'];
		fputcsv($output, [
			$opponent['name'],
			$source['played'],
			$source['wins'],
			$source['draws'],
			$source['losses'],
			$source['gf'],
			$source['ga'],
			$source['walkovers'],
			$source['abandoned'],
		], ';');
	}

	fclose($output);
	exit;
}

// Registered as submenu page via includes/admin-menu.php