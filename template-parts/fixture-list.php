<?php

/**
 * Template part for displaying fixture list
 *
 * @package Moustache
 */

if (!defined('ABSPATH')) {
	exit;
}

$clubs_withdrawn = moustache_acf_posts($clubs_withdrawn ?? []);
$withdrawals = $withdrawals ?? false;
?>

<div class="table-scroll" role="region" aria-labelledby="caption" tabindex="0">
	<table id="terminliste">
		<caption><?php esc_html_e('Fixtures', 'moustache'); ?></caption>
		<thead>
			<tr>
				<th><?php esc_html_e('Day', 'moustache'); ?></th>
				<th><?php esc_html_e('Date', 'moustache'); ?></th>
				<th><?php esc_html_e('Time', 'moustache'); ?></th>
				<th><?php esc_html_e('Home team', 'moustache'); ?></th>
				<th><?php esc_html_e('Away team', 'moustache'); ?></th>
				<th><?php esc_html_e('Pitch', 'moustache'); ?></th>
				<th><?php esc_html_e('Result', 'moustache'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($fixtures as $post) :
				setup_postdata($post);

				$fixture_id = get_the_ID();
				$home_team = moustache_get_home_team($fixture_id);
				$away_team = moustache_get_away_team($fixture_id);
				$unplayed_reason = moustache_fixture_unplayed_reason($fixture_id);
				$withdrawn_ids = array_map(static fn(WP_Post $club) => $club->ID, $clubs_withdrawn);
				$is_withdrawn = ($home_team && in_array($home_team->ID, $withdrawn_ids, true))
					|| ($away_team && in_array($away_team->ID, $withdrawn_ids, true));

				$row_classes = array_filter([
					$unplayed_reason === 'canceled' ? 'is-canceled' : null,
					$unplayed_reason === 'abandoned' ? 'is-abandoned' : null,
				]);

				$postponed = get_field('postponed', $fixture_id);
				$new_date_time = get_field('new_date_time', $fixture_id);
				$display_date_time = moustache_get_fixture_datetime($fixture_id);
			?>
				<tr<?php echo $row_classes ? ' class="' . esc_attr(implode(' ', $row_classes)) . '"' : ''; ?> style="<?php echo $is_withdrawn ? 'filter: grayscale(100%); opacity: 0.5; text-decoration: line-through;' : ''; ?>">
					<?php if (!empty($postponed) && empty($new_date_time)) : ?>
						<td colspan="3"><em><?php esc_html_e('New time to be announced', 'moustache'); ?></em></td>
					<?php else : ?>
						<td><?php echo esc_html(ucfirst(date_i18n('l', strtotime($display_date_time)))); ?></td>
						<td><?php echo esc_html(date_i18n('d.m', strtotime($display_date_time))); ?></td>
						<td><?php echo esc_html(date_i18n('H.i', strtotime($display_date_time))); ?></td>
					<?php endif; ?>

					<td>
						<?php
						if ($home_team) {
							if (moustache_is_kampbart($home_team)) {
								echo esc_html($home_team->post_title);
							} else {
								printf(
									'<a href="%1$s">%2$s</a>',
									esc_url(get_permalink($home_team)),
									esc_html($home_team->post_title)
								);
							}
						}
						?>
					</td>
					<td>
						<?php
						if ($away_team) {
							if (moustache_is_kampbart($away_team)) {
								echo esc_html($away_team->post_title);
							} else {
								printf(
									'<a href="%1$s">%2$s</a>',
									esc_url(get_permalink($away_team)),
									esc_html($away_team->post_title)
								);
							}
						}
						?>
					</td>
					<td>
						<?php
						$pitch = moustache_get_pitch($fixture_id);
						if ($pitch) {
							printf(
								'<a href="%1$s">%2$s</a>',
								esc_url(get_permalink($pitch)),
								esc_html($pitch->post_title)
							);
						}
						?>
					</td>
					<?php include locate_template('template-parts/fixture-result.php'); ?>
				</tr>
			<?php
			endforeach;
			wp_reset_postdata();
			?>
		</tbody>
		<?php if ($withdrawals) : ?>
			<tfoot>
				<tr>
					<td colspan="7">
						<?php
						$club_titles = array_map(static function (WP_Post $club) {
							return get_the_title($club);
						}, $clubs_withdrawn);

						echo wp_kses(
							sprintf(
								__('<span>%s</span> have withdrawn.', 'moustache'),
								implode('</span>, <span>', explode(', ', formatClubTitlesWithOg($club_titles)))
							),
							['span' => []]
						);
						?>
					</td>
				</tr>
			</tfoot>
		<?php endif; ?>
	</table>
</div>
