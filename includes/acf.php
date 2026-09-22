<?php

/**
 * ACF accessors and admin setup for the Moustache theme.
 *
 * @package Moustache
 */

defined('ABSPATH') || exit;

/**
 * Normalize an ACF relationship / post_object value to a single post.
 */
function moustache_acf_post(mixed $value): ?WP_Post
{
	if ($value instanceof WP_Post) {
		return $value;
	}

	if (is_array($value) && $value !== []) {
		$first = reset($value);
		return moustache_acf_post($first);
	}

	if (is_numeric($value) && (int) $value > 0) {
		$post = get_post((int) $value);
		return $post instanceof WP_Post ? $post : null;
	}

	return null;
}

/**
 * Normalize an ACF relationship value to a list of posts.
 *
 * @return WP_Post[]
 */
function moustache_acf_posts(mixed $value): array
{
	if ($value instanceof WP_Post) {
		return [$value];
	}

	if (!is_array($value) || $value === []) {
		if (is_numeric($value) && (int) $value > 0) {
			$post = moustache_acf_post($value);
			return $post ? [$post] : [];
		}

		return [];
	}

	$posts = [];
	foreach ($value as $item) {
		$post = moustache_acf_post($item);
		if ($post) {
			$posts[] = $post;
		}
	}

	return $posts;
}

function moustache_acf_post_id(int $post_id = 0): int
{
	if ($post_id) {
		return $post_id;
	}

	return (int) get_the_ID();
}

function moustache_unwrap_single_reference(mixed $value): mixed
{
	if (is_array($value) && $value !== [] && !isset($value['ID'])) {
		return reset($value);
	}

	return $value;
}

add_filter('acf/load_value/name=home_team', 'moustache_unwrap_single_reference');
add_filter('acf/load_value/name=away_team', 'moustache_unwrap_single_reference');
add_filter('acf/load_value/name=pitch', 'moustache_unwrap_single_reference');

/**
 * Limit fixture "Tilstede" picker to active players.
 *
 * Already selected players (e.g. later retired) still remain on the match.
 *
 * @param array<string, mixed> $args
 * @return array<string, mixed>
 */
function moustache_acf_relationship_active_players(array $args, array $field, mixed $post_id): array
{
	$meta_query = isset($args['meta_query']) && is_array($args['meta_query'])
		? $args['meta_query']
		: [];

	$meta_query[] = [
		'key' => 'status',
		'value' => 'active',
		'compare' => '=',
	];

	$args['meta_query'] = $meta_query;

	return $args;
}

add_filter('acf/fields/relationship/query/name=present', 'moustache_acf_relationship_active_players', 10, 3);

/**
 * Player IDs marked present on the fixture (AJAX selection wins over saved meta).
 *
 * @return int[]
 */
function moustache_acf_present_player_ids(mixed $post_id = 0): array
{
	if (isset($_POST['moustache_present_ids'])) {
		$raw = wp_unslash($_POST['moustache_present_ids']);

		if (is_string($raw) && $raw !== '') {
			$raw = explode(',', $raw);
		}

		if (is_array($raw)) {
			return array_values(array_unique(array_filter(array_map('intval', $raw))));
		}
	}

	$post_id = (int) $post_id;
	if (!$post_id) {
		return [];
	}

	return array_map(
		static fn(WP_Post $player): int => $player->ID,
		moustache_get_present($post_id)
	);
}

/**
 * Limit goal/assist/card pickers to players present on this fixture.
 *
 * @param array<string, mixed> $args
 * @return array<string, mixed>
 */
function moustache_acf_post_object_present_only(array $args, array $field, mixed $post_id): array
{
	$ids = moustache_acf_present_player_ids($post_id);
	$args['post__in'] = $ids !== [] ? $ids : [0];

	return $args;
}

foreach (
	[
		'field_moustache_goal_scorer',
		'field_moustache_goal_assist',
		'field_moustache_card_player',
	] as $field_key
) {
	add_filter(
		"acf/fields/post_object/query/key={$field_key}",
		'moustache_acf_post_object_present_only',
		10,
		3
	);
}

add_action('acf/input/admin_enqueue_scripts', static function (): void {
	$screen = function_exists('get_current_screen') ? get_current_screen() : null;
	if (!$screen || $screen->post_type !== 'fixture') {
		return;
	}

	wp_enqueue_script(
		'moustache-acf-fixture',
		get_template_directory_uri() . '/js/acf-fixture-admin.js',
		['acf-input'],
		'1.0.0',
		true
	);
});

function moustache_get_home_team(int $post_id = 0): ?WP_Post
{
	return moustache_acf_post(get_field('home_team', moustache_acf_post_id($post_id)));
}

function moustache_get_away_team(int $post_id = 0): ?WP_Post
{
	return moustache_acf_post(get_field('away_team', moustache_acf_post_id($post_id)));
}

function moustache_get_pitch(int $post_id = 0): ?WP_Post
{
	return moustache_acf_post(get_field('pitch', moustache_acf_post_id($post_id)));
}

function moustache_is_kampbart(?WP_Post $team): bool
{
	return $team && $team->post_name === 'kampbart';
}

/**
 * Effective kickoff time (postponed date wins when set).
 */
function moustache_get_fixture_datetime(int $post_id = 0): string
{
	$post_id = moustache_acf_post_id($post_id);
	$postponed = get_field('postponed', $post_id);
	$new_date_time = (string) get_field('new_date_time', $post_id);
	$date_time = (string) get_field('date_time', $post_id);

	if ($postponed && $new_date_time !== '') {
		return $new_date_time;
	}

	return $date_time;
}

/**
 * @return WP_Post[]
 */
function moustache_get_present(int $post_id = 0): array
{
	return moustache_acf_posts(get_field('present', moustache_acf_post_id($post_id)));
}

function moustache_fixture_is_result_only(int $post_id = 0): bool
{
	$post_id = moustache_acf_post_id($post_id);

	// Prefer new model; keep legacy only_result_* for pre-migration sites.
	return (bool) get_field('result_only', $post_id)
		|| (bool) get_field('only_result_fulltime', $post_id)
		|| (bool) get_field('result_only_fulltime', $post_id);
}

/**
 * Recorded score when the match has no goal-by-goal data.
 *
 * @return array{home_ft: ?int, away_ft: ?int, home_ht: ?int, away_ht: ?int, text_ft: string, text_ht: string}
 */
function moustache_get_recorded_result(int $post_id = 0): array
{
	$post_id = moustache_acf_post_id($post_id);

	$numeric = static function (mixed $value): ?int {
		if ($value === null || $value === false || $value === '') {
			return null;
		}

		return (int) $value;
	};

	return [
		'home_ft' => $numeric(get_field('result_home_ft', $post_id)),
		'away_ft' => $numeric(get_field('result_away_ft', $post_id)),
		'home_ht' => $numeric(get_field('result_home_ht', $post_id)),
		'away_ht' => $numeric(get_field('result_away_ht', $post_id)),
		'text_ft' => (string) get_field('result_fulltime', $post_id),
		'text_ht' => (string) get_field('result_pause', $post_id),
	];
}

/**
 * Goals from both halves, oldest first.
 *
 * @return array<int, array{half: string, side: string, scorer: ?WP_Post, assist: ?WP_Post, assist_text: string, own_goal: bool}>
 */
function moustache_get_goals(int $post_id = 0): array
{
	$post_id = moustache_acf_post_id($post_id);
	$new = get_field('goals', $post_id);
	$goals = [];

	if (is_array($new) && $new !== []) {
		foreach ($new as $row) {
			$goals[] = [
				'half' => (string) ($row['half'] ?? 'first'),
				'side' => (string) ($row['side'] ?? ''),
				'scorer' => moustache_acf_post($row['scorer'] ?? null),
				'assist' => moustache_acf_post($row['assist'] ?? null),
				'assist_text' => trim((string) ($row['assist_text'] ?? '')),
				'own_goal' => !empty($row['own_goal']),
			];
		}

		return $goals;
	}

	foreach (['first', 'second'] as $half) {
		$rows = get_field("goals_assists_{$half}_half", $post_id);
		if (!is_array($rows)) {
			continue;
		}

		foreach ($rows as $row) {
			$own_goal = !empty($row["own_goal_{$half}_half"]);
			$scorer = $row["goal_scorer_{$half}_half"] ?? null;
			if ($own_goal && ($row['goal_for'] ?? '') === 'opponent') {
				$scorer = $row["own_goal_{$half}_half_kampbart_player"] ?? $scorer;
			}

			$goals[] = [
				'half' => $half,
				'side' => (string) ($row['goal_for'] ?? ''),
				'scorer' => moustache_acf_post($scorer),
				'assist' => moustache_acf_post($row["assist_{$half}_half"] ?? null),
				'assist_text' => trim((string) ($row["assist_{$half}_half_text"] ?? '')),
				'own_goal' => $own_goal,
			];
		}
	}

	return $goals;
}

/**
 * Cards from unknown-half repeater, otherwise first + second half.
 *
 * @return array<int, array{half: string, player: ?WP_Post, colour: string}>
 */
function moustache_get_cards(int $post_id = 0): array
{
	$post_id = moustache_acf_post_id($post_id);
	$new = get_field('match_cards', $post_id);
	$cards = [];

	if (is_array($new) && $new !== []) {
		foreach ($new as $row) {
			$cards[] = [
				'half' => (string) ($row['half'] ?? 'unknown'),
				'player' => moustache_acf_post($row['player'] ?? null),
				'colour' => (string) ($row['colour'] ?? ''),
			];
		}

		return $cards;
	}

	$unknown = get_field('cards', $post_id);

	if (is_array($unknown) && $unknown !== []) {
		foreach ($unknown as $row) {
			$cards[] = [
				'half' => 'unknown',
				'player' => moustache_acf_post($row['card_player'] ?? null),
				'colour' => (string) ($row['card_colour'] ?? ''),
			];
		}

		return $cards;
	}

	foreach (['first', 'second'] as $half) {
		$rows = get_field("cards_{$half}_half", $post_id);
		if (!is_array($rows)) {
			continue;
		}

		foreach ($rows as $row) {
			$cards[] = [
				'half' => $half,
				'player' => moustache_acf_post($row["card_player_{$half}_half"] ?? null),
				'colour' => (string) ($row["card_colour_{$half}_half"] ?? ''),
			];
		}
	}

	return $cards;
}

/**
 * Withdrawn clubs on a tournament term (supports the misspelled field name).
 *
 * @return WP_Post[]
 */
function moustache_get_withdrawn_clubs(WP_Term|int|string $term): array
{
	$clubs = get_field('tournament_withdrawn_clubs', $term);
	if (!$clubs) {
		$clubs = get_field('tournament_whitdrawn_clubs', $term);
	}

	return moustache_acf_posts($clubs);
}

/**
 * Get why a fixture was not played.
 *
 * @return string|null 'abandoned', 'canceled', or null if the match was played.
 */
function moustache_fixture_unplayed_reason(int $post_id = 0): ?string
{
	$post_id = moustache_acf_post_id($post_id);
	if (!$post_id) {
		return null;
	}

	$unplayed = get_field('unplayed', $post_id);
	$unplayed_reason = get_field('unplayed_reason', $post_id);
	$canceled = get_field('canceled', $post_id);

	$from_reason = static function (mixed $value): ?string {
		if (!is_string($value) || $value === '') {
			return null;
		}

		if (in_array($value, ['walkover', 'match_canceled', 'canceled'], true)) {
			return 'canceled';
		}

		if (in_array($value, ['abandoned', 'match_abandoned', 'match_abandonded'], true)) {
			return 'abandoned';
		}

		return null;
	};

	if ($unplayed === true || $unplayed === 1 || $unplayed === '1') {
		return $from_reason($unplayed_reason);
	}

	return $from_reason($canceled);
}

/**
 * Human-readable label for an unplayed fixture.
 */
function moustache_fixture_unplayed_label(?string $reason = null): string
{
	if ($reason === null) {
		$reason = moustache_fixture_unplayed_reason();
	}

	if ($reason === 'abandoned') {
		return __('Match abandoned', 'moustache');
	}

	if ($reason === 'canceled') {
		return __('Match canceled', 'moustache');
	}

	return '';
}

/**
 * Unix timestamp for an ACF date/datetime wall-clock string in the site timezone.
 */
function moustache_acf_datetime_timestamp(mixed $value): ?int
{
	if (!is_string($value) || $value === '') {
		return null;
	}

	$tz = wp_timezone();
	foreach (['Y-m-d H:i:s', 'Y-m-d', 'Ymd'] as $from) {
		$dt = DateTimeImmutable::createFromFormat('!' . $from, $value, $tz);
		if ($dt instanceof DateTimeImmutable) {
			return $dt->getTimestamp();
		}
	}

	return null;
}

/**
 * Format an ACF date/datetime string for display.
 *
 * ACF stores wall-clock local time (no timezone). WP sets PHP's default TZ to UTC,
 * so wp_date(strtotime($acf)) shifts by the site offset (e.g. +2h in summer).
 */
function moustache_format_acf_datetime(mixed $value, string $format = 'j. F Y H.i'): string
{
	$timestamp = moustache_acf_datetime_timestamp($value);
	if ($timestamp === null) {
		return is_string($value) ? $value : '';
	}

	return wp_date($format, $timestamp, wp_timezone());
}

/**
 * Format an ACF date_picker value (Ymd or already formatted) for display.
 */
function moustache_format_acf_date(mixed $value, string $format = 'j. F Y'): string
{
	return moustache_format_acf_datetime($value, $format);
}

/**
 * Club info lives on the options page after migration, with the klubbinfo page as fallback.
 */
function moustache_club_info_id(): int|string
{
	foreach (['founded', 'bank_account', 'kit', 'contact_info', 'gullbart'] as $name) {
		if (get_field($name, 'option')) {
			return 'option';
		}
	}

	foreach (['klubben', 'klubbinfo'] as $path) {
		$page = get_page_by_path($path);
		if ($page instanceof WP_Post) {
			return (int) $page->ID;
		}
	}

	return get_the_ID() ?: 3;
}

function moustache_migrate_club_info_to_options(): void
{
	if (!is_admin() || get_option('moustache_migrated_club_info')) {
		return;
	}

	$page = get_page_by_path('klubben') ?: get_page_by_path('klubbinfo') ?: get_post(3);

	if (!$page instanceof WP_Post) {
		return;
	}

	foreach (['founded', 'contact_info', 'bank_account', 'kit', 'gullbart'] as $name) {
		$existing = get_field($name, 'option');
		if ($existing) {
			continue;
		}

		$value = get_field($name, $page->ID, false);
		if ($value) {
			update_field($name, $value, 'option');
		}
	}

	update_option('moustache_migrated_club_info', '1');
}
add_action('acf/init', 'moustache_migrate_club_info_to_options', 20);

/**
 * Show field groups on a page by slug (avoids hard-coded page IDs).
 */
function moustache_acf_page_slug_rule_types(array $choices): array
{
	$choices['Page']['page_slug'] = 'Page slug';
	return $choices;
}
add_filter('acf/location/rule_types', 'moustache_acf_page_slug_rule_types');

function moustache_acf_page_slug_rule_values(array $choices): array
{
	$pages = get_pages(['sort_column' => 'post_title', 'sort_order' => 'ASC']);
	if (!$pages) {
		return $choices;
	}

	foreach ($pages as $page) {
		$choices[$page->post_name] = $page->post_title . ' (' . $page->post_name . ')';
	}

	return $choices;
}
add_filter('acf/location/rule_values/page_slug', 'moustache_acf_page_slug_rule_values');

function moustache_acf_page_slug_rule_match(bool $match, array $rule, array $screen): bool
{
	$post_id = $screen['post_id'] ?? 0;
	if (!$post_id) {
		return false;
	}

	$slug = get_post_field('post_name', $post_id);
	if ($rule['operator'] === '==') {
		return $slug === $rule['value'];
	}

	return $slug !== $rule['value'];
}
add_filter('acf/location/rule_match/page_slug', 'moustache_acf_page_slug_rule_match', 10, 3);

/**
 * Load withdrawn-club values stored under the misspelled meta key.
 */
function moustache_load_withdrawn_clubs(mixed $value, mixed $post_id, array $field): mixed
{
	if ($value) {
		return $value;
	}

	return get_field('tournament_whitdrawn_clubs', $post_id, false);
}
add_filter('acf/load_value/name=tournament_withdrawn_clubs', 'moustache_load_withdrawn_clubs', 10, 3);

function moustache_initialize_acf_google_maps(): void
{
	if (!defined('GOOGLE_MAPS_API_KEY') || GOOGLE_MAPS_API_KEY === '') {
		return;
	}

	acf_update_setting('google_api_key', GOOGLE_MAPS_API_KEY);
}
add_action('acf/init', 'moustache_initialize_acf_google_maps');
