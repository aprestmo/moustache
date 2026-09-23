<?php

/**
 * One-time fixture field migration (dry-run first).
 *
 * Copies legacy half-based goals/cards and related Kamper meta into the
 * simplified ACF fields (`goals`, `match_cards`, `unplayed`, numeric results).
 * Legacy ACF field definitions were removed from acf-json, so the migrator
 * reads repeaters via moustache_get_legacy_repeater_rows() (post meta), not
 * get_field() alone. WP Admin: Tools → Fixture migration. See README.
 *
 * @package Moustache
 */

defined('ABSPATH') || exit;

add_action('admin_menu', static function (): void {
	add_management_page(
		'Fixture field migration',
		'Fixture migration',
		'manage_options',
		'moustache-fixture-migration',
		'moustache_render_fixture_migration_page'
	);
});

function moustache_parse_score_text(mixed $value): ?array
{
	if (!is_string($value) || trim($value) === '') {
		return null;
	}

	if (!preg_match('/(\d+)\s*[–\-—]\s*(\d+)/u', $value, $matches)) {
		return null;
	}

	return [(int) $matches[1], (int) $matches[2]];
}

function moustache_migrate_relationship_id(mixed $raw): mixed
{
	if (is_array($raw) && $raw !== []) {
		$first = reset($raw);
		if (is_numeric($first)) {
			return (int) $first;
		}
		if ($first instanceof WP_Post) {
			return $first->ID;
		}
	}

	if ($raw instanceof WP_Post) {
		return $raw->ID;
	}

	if (is_numeric($raw) && (int) $raw > 0) {
		return (int) $raw;
	}

	return $raw;
}

/**
 * Keep only player post IDs (legacy opponent_goal sometimes stored a club).
 */
function moustache_migrate_player_id(mixed $raw): mixed
{
	$id = moustache_migrate_relationship_id($raw);
	if (!is_numeric($id) || (int) $id <= 0) {
		return null;
	}

	$post = get_post((int) $id);
	if (!$post || $post->post_type !== 'player') {
		return null;
	}

	return (int) $id;
}

function moustache_migrate_meta(int $post_id, string $key): mixed
{
	if (function_exists('get_field') && function_exists('acf_get_field') && acf_get_field($key)) {
		$value = get_field($key, $post_id);
		if ($value !== null && $value !== false && $value !== '') {
			return $value;
		}
	}

	$value = get_post_meta($post_id, $key, true);

	return $value === '' ? null : maybe_unserialize($value);
}

function moustache_legacy_goal_rows(int $post_id): array
{
	$rows = [];

	foreach (['first', 'second'] as $half) {
		$legacy = moustache_get_legacy_repeater_rows($post_id, "goals_assists_{$half}_half");

		foreach ($legacy as $row) {
			$side = (string) ($row['goal_for'] ?? '');
			$own_goal = !empty($row["own_goal_{$half}_half"]);
			$scorer = $row["goal_scorer_{$half}_half"] ?? null;
			if ($own_goal && $side === 'opponent') {
				$scorer = $row["own_goal_{$half}_half_kampbart_player"] ?? $scorer;
			} elseif ($own_goal && $side === 'kampbart') {
				$scorer = $row["own_goal_{$half}_half_opponent_player"] ?? $scorer;
			}

			$assist_type_raw = $row['assist'] ?? '';
			$assist_type = is_scalar($assist_type_raw) ? (string) $assist_type_raw : '';
			$assist_text_raw = $row["assist_{$half}_half_text"] ?? '';
			$assist_text = is_scalar($assist_text_raw) ? (string) $assist_text_raw : '';
			$rows[] = [
				'half' => $half,
				'side' => $side,
				'own_goal' => $own_goal ? 1 : 0,
				'scorer' => moustache_migrate_player_id($scorer),
				'assist_type' => $assist_type,
				'assist' => moustache_migrate_player_id($row["assist_{$half}_half"] ?? null),
				'assist_text' => $assist_text,
			];
		}
	}

	return $rows;
}

function moustache_legacy_card_rows(int $post_id): array
{
	$rows = [];
	$unknown = moustache_get_legacy_repeater_rows($post_id, 'cards');

	if ($unknown !== []) {
		foreach ($unknown as $row) {
			$rows[] = [
				'half' => 'unknown',
				'player' => moustache_migrate_player_id($row['card_player'] ?? null),
				'colour' => (string) ($row['card_colour'] ?? ''),
			];
		}

		return $rows;
	}

	foreach (['first', 'second'] as $half) {
		$legacy = moustache_get_legacy_repeater_rows($post_id, "cards_{$half}_half");

		foreach ($legacy as $row) {
			$rows[] = [
				'half' => $half,
				'player' => moustache_migrate_player_id($row["card_player_{$half}_half"] ?? null),
				'colour' => (string) ($row["card_colour_{$half}_half"] ?? ''),
			];
		}
	}

	return $rows;
}

function moustache_fixture_migration_payload(int $post_id): array
{
	$canceled = moustache_migrate_meta($post_id, 'canceled');
	$unplayed = 0;
	$unplayed_reason = '';

	if ($canceled === 'match_canceled') {
		$unplayed = 1;
		$unplayed_reason = 'walkover';
	} elseif (in_array($canceled, ['match_abandoned', 'match_abandonded'], true)) {
		$unplayed = 1;
		$unplayed_reason = 'abandoned';
	}

	$result_fulltime = moustache_migrate_meta($post_id, 'result_fulltime');
	$result_pause = moustache_migrate_meta($post_id, 'result_pause');
	$ft = moustache_parse_score_text(is_string($result_fulltime) ? $result_fulltime : '');
	$ht = moustache_parse_score_text(is_string($result_pause) ? $result_pause : '');
	$result_only = (bool) moustache_migrate_meta($post_id, 'only_result_fulltime');

	$home = moustache_migrate_meta($post_id, 'home_team');
	$away = moustache_migrate_meta($post_id, 'away_team');
	$pitch = moustache_migrate_meta($post_id, 'pitch');

	return [
		'home_team' => moustache_migrate_relationship_id($home),
		'away_team' => moustache_migrate_relationship_id($away),
		'pitch' => moustache_migrate_relationship_id($pitch),
		'goals' => moustache_legacy_goal_rows($post_id),
		'match_cards' => moustache_legacy_card_rows($post_id),
		'unplayed' => $unplayed,
		'unplayed_reason' => $unplayed_reason,
		'result_only' => $result_only ? 1 : 0,
		'result_home_ht' => $ht[0] ?? '',
		'result_away_ht' => $ht[1] ?? '',
		'result_home_ft' => $ft[0] ?? '',
		'result_away_ft' => $ft[1] ?? '',
		'score_parse_error' => ($result_only || $result_fulltime) && $ft === null
			&& is_string($result_fulltime) && $result_fulltime !== '',
	];
}

function moustache_run_fixture_migration(bool $dry_run): array
{
	$ids = get_posts([
		'post_type' => 'fixture',
		'post_status' => 'any',
		'posts_per_page' => -1,
		'fields' => 'ids',
		'no_found_rows' => true,
	]);

	$report = [
		'total' => count($ids),
		'updated' => 0,
		'skipped' => 0,
		'errors' => [],
		'samples' => [],
	];

	foreach ($ids as $post_id) {
		$payload = moustache_fixture_migration_payload((int) $post_id);

		if ($payload['score_parse_error']) {
			$report['errors'][] = [
				'id' => $post_id,
				'title' => get_the_title($post_id),
				'reason' => 'Could not parse result_fulltime',
			];
		}

		if (count($report['samples']) < 8) {
			$report['samples'][] = [
				'id' => $post_id,
				'title' => get_the_title($post_id),
				'goals' => count($payload['goals']),
				'cards' => count($payload['match_cards']),
				'unplayed_reason' => $payload['unplayed_reason'],
				'result_only' => $payload['result_only'],
			];
		}

		if ($dry_run) {
			$report['updated']++;
			continue;
		}

		foreach (['home_team', 'away_team', 'pitch', 'goals', 'match_cards', 'unplayed', 'unplayed_reason', 'result_only', 'result_home_ht', 'result_away_ht', 'result_home_ft', 'result_away_ft'] as $name) {
			$value = $payload[$name];
			if ($value === '' || $value === []) {
				continue;
			}
			update_field($name, $value, $post_id);
		}

		$report['updated']++;
	}

	if (!$dry_run) {
		update_option('moustache_fixtures_migrated', '1');
	}

	return $report;
}

function moustache_render_fixture_migration_page(): void
{
	if (!current_user_can('manage_options')) {
		return;
	}

		$form_ok = isset($_POST['moustache_migrate_fixtures']) && check_admin_referer('moustache_migrate_fixtures');
		$dry_run = true;
		$ran = false;
		$report = null;

		if ($form_ok) {
			$dry_run = empty($_POST['moustache_migrate_write']);
			$report = moustache_run_fixture_migration($dry_run);
			$ran = true;
		}
	?>
	<div class="wrap">
		<h1>Fixture field migration</h1>
		<p>Copies first/second-half goals and cards into the new <code>goals</code> / <code>match_cards</code> fields, unwraps home/away/pitch to a single ID, and maps canceled matches to <code>unplayed</code>.</p>
		<p><?php echo get_option('moustache_fixtures_migrated') ? '<strong>Migration has been written on this environment.</strong> Old meta keys remain in the database for rollback, but they are no longer in the ACF UI.' : 'Run a dry run first. Old fields are left in place until we cut over.'; ?></p>

		<?php if ($ran && $report) : ?>
			<div class="notice notice-<?php echo $report['errors'] ? 'warning' : 'success'; ?>">
				<p>
					<?php echo $dry_run ? 'Dry run' : 'Write'; ?>:
					<?php echo esc_html((string) $report['updated']); ?> / <?php echo esc_html((string) $report['total']); ?> fixtures.
					<?php echo esc_html((string) count($report['errors'])); ?> parse errors.
				</p>
			</div>
			<?php if ($report['errors']) : ?>
				<h2>Parse errors</h2>
				<ul>
					<?php foreach ($report['errors'] as $error) : ?>
						<li><?php echo esc_html($error['title'] . ' (#' . $error['id'] . '): ' . $error['reason']); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<h2>Sample</h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>ID</th>
						<th>Title</th>
						<th>Goals</th>
						<th>Cards</th>
						<th>Unplayed</th>
						<th>Result only</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($report['samples'] as $sample) : ?>
						<tr>
							<td><?php echo esc_html((string) $sample['id']); ?></td>
							<td><?php echo esc_html($sample['title']); ?></td>
							<td><?php echo esc_html((string) $sample['goals']); ?></td>
							<td><?php echo esc_html((string) $sample['cards']); ?></td>
							<td><?php echo esc_html($sample['unplayed_reason'] ?: '—'); ?></td>
							<td><?php echo $sample['result_only'] ? 'yes' : 'no'; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<form method="post" style="margin-top: 1.5rem;">
			<?php wp_nonce_field('moustache_migrate_fixtures'); ?>
			<input type="hidden" name="moustache_migrate_fixtures" value="1">
			<p>
				<button type="submit" class="button button-primary">Dry run</button>
				<button type="submit" name="moustache_migrate_write" class="button" value="1" onclick="return confirm('Write new field values for all fixtures? Old fields are kept.');">Write values</button>
			</p>
		</form>
	</div>
	<?php
}
