<?php
/**
 * Team statistics admin page (Kampbart overall stats).
 *
 * @package Moustache
 */

defined('ABSPATH') || die('Shame on you');

/**
 * Get overall team statistics for Kampbart.
 *
 * @param int $tournament_id Optional tournament term ID to filter by.
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
function moustache_get_team_stats(int $tournament_id = 0): array {
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

	$fixtures = get_posts($args);

	$matches_played = 0;
	$wins = 0;
	$draws = 0;
	$losses = 0;
	$walkovers_for = 0;
	$walkovers_against = 0;
	$gf = 0;
	$ga = 0;

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

		$unplayed_reason = moustache_fixture_unplayed_reason($fixture_id);

		// Walkover: count as win/loss but not as a played match for goals.
		// Detect via unplayed_reason (new) OR legacy walkover field.
		$is_walkover = $unplayed_reason === 'canceled';
		$walkover_winner = $is_walkover ? get_field('walkover_winner', $fixture_id) : null;

		if ($is_walkover) {
			$is_kampbart_winner = $walkover_winner === 'kampbart';
			if ($is_kampbart_winner) {
				$wins++;
				$walkovers_for++;
			} else {
				$losses++;
				$walkovers_against++;
			}
			continue;
		}

		// Abandoned: don't count at all.
		if ($unplayed_reason === 'abandoned') {
			continue;
		}

		// Played match: get goals.
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
			}
		}

		// Count as a played match if we have a result (goals or recorded).
		$has_result = ($goals !== []) || ($recorded_ft ?? null) !== null;
		if (!$has_result) {
			continue;
		}

		$matches_played++;
		$gf += $kampbart_goals;
		$ga += $opponent_goals;

		if ($kampbart_goals > $opponent_goals) {
			$wins++;
		} elseif ($kampbart_goals === $opponent_goals) {
			$draws++;
		} else {
			$losses++;
		}
	}

	$total_matches = $matches_played + $walkovers_for + $walkovers_against;
	$points = ($wins * 3) + $draws;

	return [
		'matches_played'       => $matches_played,
		'walkovers_for'        => $walkovers_for,
		'walkovers_against'    => $walkovers_against,
		'total_matches'        => $total_matches,
		'wins'                 => $wins,
		'draws'                => $draws,
		'losses'               => $losses,
		'points'               => $points,
		'points_per_match'     => $matches_played > 0 ? round($points / $matches_played, 2) : 0,
		'gf'                   => $gf,
		'ga'                   => $ga,
		'gf_per_match'         => $matches_played > 0 ? round($gf / $matches_played, 2) : 0,
		'ga_per_match'         => $matches_played > 0 ? round($ga / $matches_played, 2) : 0,
	];
}

/**
 * Team statistics list table (Statistikk → Lagstatistikk).
 */
class Moustache_Team_Stats_List_Table extends WP_List_Table {

	private int $selected_tournament = 0;

	public function __construct() {
		parent::__construct([
			'singular' => 'statistikk',
			'plural'   => 'statistikk',
			'ajax'     => false,
		]);
	}

	public function get_sort_state(): array {
		return [
			'tournament' => $this->selected_tournament,
			'orderby'   => isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'matches_played',
			'order'     => isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'desc' : 'asc',
		];
	}

	public function prepare_items(): void {
		$this->selected_tournament = isset($_GET['tournament']) ? (int) $_GET['tournament'] : 0;

		$stats = moustache_get_team_stats($this->selected_tournament);

		// Build rows as a single-item array for the list table.
		$this->items = [$stats];

		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
			'matches_played',
		];

		$this->set_pagination_args([
			'total_items' => 1,
			'total_pages' => 1,
		]);
	}

	public function no_items(): void {
		esc_html_e('Ingen statistikk funnet.', 'moustache');
	}

	public function get_columns(): array {
		return [
			'matches_played'     => esc_html__('Kamper', 'moustache'),
			'walkovers_for'      => esc_html__('WO (for)', 'moustache'),
			'walkovers_against'  => esc_html__('WO (mot)', 'moustache'),
			'wins'               => esc_html__('Seire', 'moustache'),
			'draws'              => esc_html__('Uavgjort', 'moustache'),
			'losses'             => esc_html__('Tap', 'moustache'),
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
		return 'Kampbart';
	}
}

/**
 * Render the team statistics admin page.
 */
function moustache_team_stats_admin_page(): void {
	if (!current_user_can('manage_options')) {
		return;
	}

	$table = new Moustache_Team_Stats_List_Table();
	$table->prepare_items();

	$state = $table->get_sort_state();
	$selected_tournament = $state['tournament'] ?? 0;
	$orderby = $state['orderby'] ?? 'matches_played';
	$order = $state['order'] ?? 'asc';

	?>
	<div class="wrap">
		<h1><?php esc_html_e('Lagstatistikk', 'moustache'); ?></h1>
		<p class="description"><?php esc_html_e('Oversikt over Kampbart sine samlede resultater.', 'moustache'); ?></p>

		<form method="get" style="margin-bottom: 20px;">
			<input type="hidden" name="page" value="team-stats">
			<input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>">
			<input type="hidden" name="order" value="<?php echo esc_attr($order); ?>">
		</form>

		<?php $table->display(); ?>
	</div>
	<?php
}