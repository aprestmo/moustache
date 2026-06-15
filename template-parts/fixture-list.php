<?php

/**
 * Template part for displaying fixture list
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="table-scroll" role="region" aria-labelledby="caption" tabindex="0">
	<table id="terminliste">
		<caption><?php esc_html_e('Terminliste', 'moustache'); ?></caption>
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

				// Get match data
				$home_team = get_field('home_team');
				$away_team = get_field('away_team');
				$is_withdrawn = false;

				// Check if any team has withdrawn
				if ($clubs_withdrawn) {
					foreach ($home_team as $team) {
						if (in_array($team, $clubs_withdrawn, true)) {
							$is_withdrawn = true;
							break;
						}
					}
					if (!$is_withdrawn) {
						foreach ($away_team as $team) {
							if (in_array($team, $clubs_withdrawn, true)) {
								$is_withdrawn = true;
								break;
							}
						}
					}
				}
			?>
				<tr style="<?php echo $is_withdrawn ? 'filter: grayscale(100%); opacity: 0.5; text-decoration: line-through;' : ''; ?>">
					<?php
					$date_time = get_field('date_time');
					$postponed = get_field('postponed');
					$new_date_time = get_field('new_date_time');
					$display_date_time = $new_date_time ? $new_date_time : $date_time;

					if (!empty($postponed) && empty($new_date_time)) : ?>
						<td colspan="3"><em><?php esc_html_e('Nytt tidspunkt kommer', 'moustache'); ?></em></td>
					<?php else : ?>
						<td><?php echo esc_html(ucfirst(date_i18n('l', strtotime($display_date_time)))); ?></td>
						<td><?php echo esc_html(date_i18n('d.m', strtotime($display_date_time))); ?></td>
						<td><?php echo esc_html(date_i18n('H.i', strtotime($display_date_time))); ?></td>
					<?php endif; ?>

					<td>
						<?php
						foreach ($home_team as $team) {
							if ('kampbart' !== $team->post_name) {
								printf(
									'<a href="/klubb/%1$s">%2$s</a>',
									esc_attr($team->post_name),
									esc_html($team->post_title)
								);
							} else {
								echo esc_html($team->post_title);
							}
						}
						?>
					</td>
					<td>
						<?php
						foreach ($away_team as $team) {
							if ('kampbart' !== $team->post_name) {
								printf(
									'<a href="/klubb/%1$s">%2$s</a>',
									esc_attr($team->post_name),
									esc_html($team->post_title)
								);
							} else {
								echo esc_html($team->post_title);
							}
						}
						?>
					</td>
					<td>
						<?php
						$pitches = get_field('pitch');
						if ($pitches) {
							foreach ($pitches as $pitch) {
								printf(
									'<a href="/bane/%1$s">%2$s</a>',
									esc_attr($pitch->post_name),
									esc_html($pitch->post_title)
								);
							}
						}
						?>
					</td>
					<?php include(locate_template('template-parts/fixture-result.php')); ?>
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
						$club_titles = array_map(function ($club) {
							return get_the_title($club);
						}, $clubs_withdrawn);

						echo wp_kses(
							sprintf(
								'<span>%s</span> har trukket seg.',
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