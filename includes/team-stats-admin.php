<?php
/**
 * Team statistics admin page (Kampbart overall stats).
 *
 * The dashboard tab shows one row per tournament plus a total row.
 *
 * @package Moustache
 */

defined('ABSPATH') || die('Shame on you');

/**
 * Fixture query for the team statistics.
 *
 * @param int $tournament_id Optional tournament term ID to filter by.
 * @return WP_Post[]
 */
function moustache_team_stats_fixtures(int $tournament_id = 0): array {
	$args = [
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
	];

	if ($tournament_id > 0) {
		$args['tax_query'] = [
			[
				'taxonomy' => 'tournament',
				'field'    => 'term_id',
				'terms'    => $tournament_id,
			],
		];
	}

	return get_posts($args);
}

/**
 * Zeroed statistics accumulator.
 *
 * @return array<string, int|float>
 */
function moustache_team_stats_zero(): array {
	return [
		'matches_played'       => 0,
		'walkovers_for'        => 0,
		'walkovers_against'    => 0,
		'total_matches'        => 0,
		'wins'                 => 0,
		'draws'                => 0,
		'losses'               => 0,
		'points'               => 0,
		'points_per_match'     => 0,
		'gf'                   => 0,
		'ga'                   => 0,
		'gf_per_match'         => 0,
		'ga_per_match'         => 0,
	];
}

/**
 * Derive the computed fields (total matches, points, per-match averages).
 *
 * @param array<string, int|float> $stats
 * @return array<string, int|float>
 */
function moustache_team_stats_finalize(array $stats): array {
	$stats['total_matches']    = $stats['matches_played'] + $stats['walkovers_for'] + $stats['walkovers_against'];
	$stats['points']           = ($stats['wins'] * 3) + $stats['draws'];
	$stats['points_per_match'] = $stats['matches_played'] > 0 ? round($stats['points'] / $stats['matches_played'], 2) : 0;
	$stats['gf_per_match']     = $stats['matches_played'] > 0 ? round($stats['gf'] / $stats['matches_played'], 2) : 0;
	$stats['ga_per_match']     = $stats['matches_played'] > 0 ? round($stats['ga'] / $stats['matches_played'], 2) : 0;

	return $stats;
}

/**
 * Whether the statistics should include matches that were played but where a
 * team withdrew (`unplayed_reason = abandoned`).
 *
 * @return bool
 */
function moustache_team_stats_include_withdrawn(): bool {
	return isset($_GET['withdrawn']) && $_GET['withdrawn'] === '1';
}

/**
 * Whether only tournaments whose slug starts with this prefix should be shown
 * (e.g. `uteserie`). Empty string means every tournament.
 *
 * @return string
 */
function moustache_team_stats_series_filter(): string {
	if (!isset($_GET['serie'])) {
		return '';
	}

	return sanitize_title(wp_unslash($_GET['serie']));
}

/**
 * Classify a single fixture for the team statistics.
 *
 * @param bool $include_withdrawn Count abandoned matches (a team withdrew) too —
 *                                they then resolve like any other played match.
 * @return null|array{
 *     status: 'skip'|'walkover'|'played',
 *     kampbart_win: bool,
 *     kampbart_goals: int,
 *     opponent_goals: int
 * }
 */
function moustache_team_fixture_result(int $fixture_id, bool $include_withdrawn = false): ?array {
	$home_team = moustache_get_home_team($fixture_id);
	$away_team = moustache_get_away_team($fixture_id);

	// Must have exactly one Kampbart side.
	$kampbart_home = moustache_is_kampbart($home_team);
	$kampbart_away = moustache_is_kampbart($away_team);
	if ($kampbart_home === $kampbart_away) {
		return null;
	}

	$unplayed_reason = moustache_fixture_unplayed_reason($fixture_id);

	// Walkover: count as win/loss but not as a played match for goals.
	// Detect via unplayed_reason (new) OR legacy walkover field.
	$is_walkover = $unplayed_reason === 'canceled';
	$walkover_winner = $is_walkover ? get_field('walkover_winner', $fixture_id) : null;

	if ($is_walkover) {
		return [
			'status'         => 'walkover',
			'kampbart_win'   => $walkover_winner === 'kampbart',
			'kampbart_goals' => 0,
			'opponent_goals' => 0,
		];
	}

	// Abandoned: a team withdrew — left out unless the caller opted in, in which
	// case it falls through and resolves like any other played match below.
	if ($unplayed_reason === 'abandoned' && !$include_withdrawn) {
		return [
			'status'         => 'skip',
			'kampbart_win'   => false,
			'kampbart_goals' => 0,
			'opponent_goals' => 0,
		];
	}

	// Played match: get goals.
	$goals = moustache_get_goals($fixture_id);
	$kampbart_goals = 0;
	$opponent_goals = 0;
	$recorded_ft = null;

	if ($goals !== []) {
		foreach ($goals as $goal) {
			if (($goal['side'] ?? '') === 'kampbart') {
				$kampbart_goals++;
			} else {
				$opponent_goals++;
			}
		}
	} else {
		$recorded = moustache_get_recorded_result($fixture_id);
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
		}
	}

	// Count as a played match if we have a result (goals or recorded).
	$has_result = ($goals !== []) || $recorded_ft !== null;
	if (!$has_result) {
		return [
			'status'         => 'skip',
			'kampbart_win'   => false,
			'kampbart_goals' => 0,
			'opponent_goals' => 0,
		];
	}

	return [
		'status'         => 'played',
		'kampbart_win'   => $kampbart_goals > $opponent_goals,
		'kampbart_goals' => $kampbart_goals,
		'opponent_goals' => $opponent_goals,
	];
}

/**
 * Fold one classified fixture into a statistics accumulator.
 *
 * @param array<string, int|float> $stats
 * @param array{status: string, kampbart_win: bool, kampbart_goals: int, opponent_goals: int} $result
 * @return array<string, int|float>
 */
function moustache_team_stats_accumulate(array $stats, array $result): array {
	if ($result['status'] === 'walkover') {
		if ($result['kampbart_win']) {
			$stats['wins']++;
			$stats['walkovers_for']++;
		} else {
			$stats['losses']++;
			$stats['walkovers_against']++;
		}
		return $stats;
	}

	if ($result['status'] !== 'played') {
		return $stats;
	}

	$stats['matches_played']++;
	$stats['gf'] += $result['kampbart_goals'];
	$stats['ga'] += $result['opponent_goals'];

	if ($result['kampbart_goals'] > $result['opponent_goals']) {
		$stats['wins']++;
	} elseif ($result['kampbart_goals'] === $result['opponent_goals']) {
		$stats['draws']++;
	} else {
		$stats['losses']++;
	}

	return $stats;
}

/**
 * Get overall team statistics for Kampbart.
 *
 * @param int  $tournament_id    Optional tournament term ID to filter by.
 * @param bool $include_withdrawn Also count matches where a team withdrew.
 * @return array{
 *     matches_played: int,
 *     walkovers_for: int,
 *     walkovers_against: int,
 *     total_matches: int,
 *     wins: int,
 *     draws: int,
 *     losses: int,
 *     points: int,
 *     points_per_match: float,
 *     gf: int,
 *     ga: int,
 *     gf_per_match: float,
 *     ga_per_match: float,
 * }
 */
function moustache_get_team_stats(int $tournament_id = 0, bool $include_withdrawn = false): array {
	$stats = moustache_team_stats_zero();

	foreach (moustache_team_stats_fixtures($tournament_id) as $fixture) {
		$result = moustache_team_fixture_result((int) $fixture->ID, $include_withdrawn);
		if ($result === null) {
			continue;
		}
		$stats = moustache_team_stats_accumulate($stats, $result);
	}

	return moustache_team_stats_finalize($stats);
}

/**
 * Actual kickoff of a fixture: `new_date_time` (postponed matches) falling back
 * to `date_time`. Both return `Y-m-d H:i:s`, the same shape as `post_date`, so
 * the values compare directly as strings.
 *
 * @return string Empty string when the fixture has no kickoff at all.
 */
function moustache_fixture_match_time(int $fixture_id): string {
	$date_time = get_field('new_date_time', $fixture_id) ?: get_field('date_time', $fixture_id);

	if (is_string($date_time) && $date_time !== '') {
		return $date_time;
	}

	// The one fixture without a kickoff falls back to when it was entered.
	return (string) get_post_field('post_date', $fixture_id);
}

/**
 * Tournament term IDs a fixture belongs to.
 *
 * @return int[] Single-element array with 0 when the fixture has no tournament.
 */
function moustache_fixture_tournament_ids(int $fixture_id): array {
	$terms = wp_get_object_terms($fixture_id, 'tournament');

	if (is_wp_error($terms) || $terms === []) {
		return [0];
	}

	return array_map(static fn($term): int => (int) $term->term_id, $terms);
}

/**
 * Display name for a tournament term (0 = fixtures without a tournament).
 */
function moustache_tournament_name(int $term_id): string {
	if ($term_id === 0) {
		return __('Uten turnering', 'moustache');
	}

	$term = get_term($term_id, 'tournament');
	if ($term && !is_wp_error($term)) {
		return $term->name;
	}

	return sprintf(__('Turnering %d', 'moustache'), $term_id);
}

/**
 * Slug for a tournament term (0 = fixtures without a tournament).
 */
function moustache_tournament_slug(int $term_id): string {
	if ($term_id === 0) {
		return '';
	}

	$term = get_term($term_id, 'tournament');
	if ($term && !is_wp_error($term)) {
		return $term->slug;
	}

	return '';
}

/**
 * Team statistics grouped by tournament, all fixtures fetched in one pass.
 *
 * @param bool $include_withdrawn Also count matches where a team withdrew.
 * @return array{
 *     total: array<string, int|float>,
 *     tournaments: array<int, array{name: string, last_match: string, stats: array<string, int|float>}>
 * }
 */
function moustache_get_team_stats_by_tournament(bool $include_withdrawn = false): array {
	$total   = moustache_team_stats_zero();
	$buckets = [];

	foreach (moustache_team_stats_fixtures() as $fixture) {
		$result = moustache_team_fixture_result((int) $fixture->ID, $include_withdrawn);

		// Not a Kampbart fixture at all.
		if ($result === null) {
			continue;
		}

		// Newest kickoff drives the tournament ordering below.
		$match_time = moustache_fixture_match_time((int) $fixture->ID);
		$total      = moustache_team_stats_accumulate($total, $result);

		// Every tournament this fixture belongs to gets a row — even when the
		// fixture itself doesn't count (upcoming, abandoned, no result yet).
		foreach (moustache_fixture_tournament_ids((int) $fixture->ID) as $term_id) {
			if (!isset($buckets[$term_id])) {
				$buckets[$term_id] = [
					'name'       => moustache_tournament_name($term_id),
					'last_match' => '',
					'stats'      => moustache_team_stats_zero(),
				];
			}

			if ($match_time > $buckets[$term_id]['last_match']) {
				$buckets[$term_id]['last_match'] = $match_time;
			}

			$buckets[$term_id]['stats'] = moustache_team_stats_accumulate($buckets[$term_id]['stats'], $result);
		}
	}

	return [
		'total'       => moustache_team_stats_finalize($total),
		'tournaments' => array_map(
			static function (array $bucket): array {
				$bucket['stats'] = moustache_team_stats_finalize($bucket['stats']);
				return $bucket;
			},
			$buckets
		),
	];
}

/**
 * Table rows: one per tournament (most recently played first) + a total row.
 *
 * @param bool   $include_withdrawn Also count matches where a team withdrew.
 * @param string $series_prefix     Only keep tournaments whose term slug starts
 *                                  with this prefix ('' = every tournament).
 * @return array<int, array{name: string, is_total: bool, last_match: string, term_id: int} & array<string, int|float>>
 */
function moustache_team_stats_rows(bool $include_withdrawn = false, string $series_prefix = ''): array {
	$data = moustache_get_team_stats_by_tournament($include_withdrawn);

	$rows = [];
	$total = moustache_team_stats_zero();
	foreach ($data['tournaments'] as $term_id => $bucket) {
		if ($series_prefix !== '' && !str_starts_with(moustache_tournament_slug((int) $term_id), $series_prefix)) {
			continue;
		}

		$rows[] = [
			'name'       => $bucket['name'],
			'is_total'   => false,
			'last_match' => $bucket['last_match'],
			'term_id'    => $term_id,
		] + $bucket['stats'];

		// The total row mirrors the visible rows (filtered) — raw fields sum,
		// derived fields (points, averages) come from finalize().
		foreach (['matches_played', 'walkovers_for', 'walkovers_against', 'wins', 'draws', 'losses', 'gf', 'ga'] as $key) {
			$total[$key] += $bucket['stats'][$key];
		}
	}

	usort($rows, static function (array $a, array $b): int {
		if ($a['last_match'] === $b['last_match']) {
			return $b['term_id'] <=> $a['term_id'];
		}
		// Most recent kickoff first.
		return $a['last_match'] < $b['last_match'] ? 1 : -1;
	});

	$rows[] = [
		'name'       => __('Totalt', 'moustache'),
		'is_total'   => true,
		'last_match' => '',
		'term_id'    => -1,
	] + moustache_team_stats_finalize($total);

	return $rows;
}

/**
 * Team statistics list table (Statistikk → Kamper).
 */
class Moustache_Team_Stats_List_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct([
			'singular' => 'statistikk',
			'plural'   => 'statistikk',
			'ajax'     => false,
		]);
	}

	public function get_sort_state(): array {
		return [
			'tournament' => 0,
			'orderby'   => isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'matches_played',
			'order'     => isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'desc' : 'asc',
		];
	}

	public function prepare_items(): void {
		$this->items = moustache_team_stats_rows(moustache_team_stats_include_withdrawn(), moustache_team_stats_series_filter());

		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
			'tournament',
		];

		$this->set_pagination_args([
			'total_items' => count($this->items),
			'total_pages' => 1,
		]);
	}

	public function no_items(): void {
		esc_html_e('Ingen statistikk funnet.', 'moustache');
	}

	public function get_columns(): array {
		return [
			'tournament'         => esc_html__('Turnering', 'moustache'),
			'matches_played'     => esc_html__('Kamper', 'moustache'),
			'wins'               => esc_html__('Seire', 'moustache'),
			'draws'              => esc_html__('Uavgjort', 'moustache'),
			'losses'             => esc_html__('Tap', 'moustache'),
			'walkovers_for'      => esc_html__('WO (for)', 'moustache'),
			'walkovers_against'  => esc_html__('WO (mot)', 'moustache'),
			'points'             => esc_html__('Poeng', 'moustache'),
			'points_per_match'   => esc_html__('Poengsnitt/kamp', 'moustache'),
			'gf'                 => esc_html__('Mål for', 'moustache'),
			'ga'                 => esc_html__('Mål mot', 'moustache'),
			'gf_per_match'       => esc_html__('Mål for/kamp', 'moustache'),
			'ga_per_match'       => esc_html__('Mål mot/kamp', 'moustache'),
		];
	}

	protected function get_sortable_columns(): array {
		return [];
	}

	public function column_tournament($item): string {
		$name = esc_html((string) $item['name']);

		return $item['is_total'] ? '<strong>' . $name . '</strong>' : $name;
	}

	public function column_matches_played($item): string {
		return esc_html((string) $item['total_matches']);
	}

	public function column_walkovers_for($item): string {
		return esc_html((string) $item['walkovers_for']);
	}

	public function column_walkovers_against($item): string {
		return esc_html((string) $item['walkovers_against']);
	}

	public function column_wins($item): string {
		return esc_html((string) $item['wins']);
	}

	public function column_draws($item): string {
		return esc_html((string) $item['draws']);
	}

	public function column_losses($item): string {
		return esc_html((string) $item['losses']);
	}

	public function column_points($item): string {
		return esc_html((string) $item['points']);
	}

	public function column_points_per_match($item): string {
		return esc_html(number_format($item['points_per_match'], 2, ',', ' '));
	}

	public function column_gf($item): string {
		return esc_html((string) $item['gf']);
	}

	public function column_ga($item): string {
		return esc_html((string) $item['ga']);
	}

	public function column_gf_per_match($item): string {
		return esc_html(number_format($item['gf_per_match'], 2, ',', ' '));
	}

	public function column_ga_per_match($item): string {
		return esc_html(number_format($item['ga_per_match'], 2, ',', ' '));
	}

	protected function get_primary_column_aria_label($item): string {
		return wp_strip_all_tags((string) $item['name']);
	}
}

/**
 * Export team statistics (one row per tournament + total) to CSV.
 */
function moustache_team_stats_export_csv(): void {
	if (!current_user_can('manage_options')) {
		wp_die(esc_html__('Unauthorized', 'moustache'));
	}

	$rows = moustache_team_stats_rows(moustache_team_stats_include_withdrawn(), moustache_team_stats_series_filter());

	$filename = 'lagstatistikk-' . date('Y-m-d') . '.csv';

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Pragma: no-cache');
	header('Expires: 0');

	// UTF-8 BOM for Excel
	echo "\xEF\xBB\xBF";

	$output = fopen('php://output', 'w');

	fputcsv($output, [
		__('Turnering', 'moustache'),
		__('Kamper', 'moustache'),
		__('Seire', 'moustache'),
		__('Uavgjort', 'moustache'),
		__('Tap', 'moustache'),
		__('WO (for)', 'moustache'),
		__('WO (mot)', 'moustache'),
		__('Poeng', 'moustache'),
		__('Poengsnitt/kamp', 'moustache'),
		__('Mål for', 'moustache'),
		__('Mål mot', 'moustache'),
		__('Mål for/kamp', 'moustache'),
		__('Mål mot/kamp', 'moustache'),
	], ';', '"', '');

	foreach ($rows as $row) {
		fputcsv($output, [
			$row['name'],
			$row['total_matches'],
			$row['wins'],
			$row['draws'],
			$row['losses'],
			$row['walkovers_for'],
			$row['walkovers_against'],
			$row['points'],
			number_format($row['points_per_match'], 2, ',', '.'),
			$row['gf'],
			$row['ga'],
			number_format($row['gf_per_match'], 2, ',', '.'),
			number_format($row['ga_per_match'], 2, ',', '.'),
		], ';', '"', '');
	}

	fclose($output);
	exit;
}

/**
 * Render the standalone team statistics admin page (no longer registered).
 */
function moustache_team_stats_admin_page(): void {
	if (!current_user_can('manage_options')) {
		return;
	}

	$table = new Moustache_Team_Stats_List_Table();
	$table->prepare_items();

	$state = $table->get_sort_state();
	$orderby = $state['orderby'] ?? 'matches_played';
	$order = $state['order'] ?? 'asc';

	?>
	<div class="wrap">
		<h1><?php esc_html_e('Kamper', 'moustache'); ?></h1>
		<p class="description"><?php esc_html_e('Oversikt over Kampbart sine resultater, per turnering.', 'moustache'); ?></p>

		<form method="get" style="margin-bottom: 20px;">
			<input type="hidden" name="page" value="team-stats">
			<input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>">
			<input type="hidden" name="order" value="<?php echo esc_attr($order); ?>">
			<a href="<?php echo esc_url(admin_url('admin.php?page=moustache-stats-dashboard&tab=matches&export=csv')); ?>" class="button button-primary">
				<?php esc_html_e('Eksporter til CSV', 'moustache'); ?>
			</a>
		</form>

		<?php $table->display(); ?>
	</div>
	<?php
}
