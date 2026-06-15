<hr>

<div class="table-scroll" role="region" aria-labelledby="caption" tabindex="0">
	<table>
		<caption><?php esc_html_e('Player Stats', 'moustache'); ?></caption>
		<thead>
			<tr>
				<th><?php esc_html_e('Season', 'moustache'); ?></th>
				<th><?php esc_html_e('Matches', 'moustache'); ?></th>
				<th><?php esc_html_e('Goals', 'moustache'); ?></th>
				<th><?php esc_html_e('Assists', 'moustache'); ?></th>
				<th><?php esc_html_e('Yellow Cards', 'moustache'); ?></th>
				<th><?php esc_html_e('Red Cards', 'moustache'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($stats as $season_stats) :
				$season_totals = [
					'goals' => $season_stats->goalsFirst + $season_stats->goalsSecond,
					'assists' => $season_stats->assistsFirst + $season_stats->assistsSecond,
					'yellowCards' => $season_stats->yellowCardsFirst + $season_stats->yellowCardsSecond,
					'redCards' => $season_stats->redCardsFirst + $season_stats->redCardsSecond
				];

				// Update totals
				$totals['matches'] += $season_stats->count;
				foreach ($season_totals as $key => $value) {
					$totals[$key] += $value;
				}
			?>
				<tr>
					<th>
						<a href="<?php echo esc_url("/turnering/{$season_stats->slug}"); ?>">
							<?php echo esc_html($season_stats->name); ?>
						</a>
					</th>
					<td><?php echo esc_html($season_stats->count); ?></td>
					<td><?php echo esc_html($season_totals['goals']); ?></td>
					<td><?php echo esc_html($season_totals['assists']); ?></td>
					<td><?php echo esc_html($season_totals['yellowCards']); ?></td>
					<td><?php echo esc_html($season_totals['redCards']); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<th><?php esc_html_e('Total', 'moustache'); ?></th>
				<?php foreach ($totals as $total) : ?>
					<th><?php echo esc_html($total); ?></th>
				<?php endforeach; ?>
			</tr>
		</tfoot>
	</table>
</div>