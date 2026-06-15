<?php get_header(); ?>

<?php
if (have_posts()) :
	while (have_posts()) :
		the_post();
?>

		<h2><?php the_title(); ?></h2>

		<?php
		$teams = get_field('opponents');
		?>

		<?php if ($teams) : ?>

			<div class="table-scroll" role="region" aria-labelledby="caption" tabindex="0">
				<table>
					<tbody>
						<?php foreach ($teams as $team) : ?>
							<tr>
								<td>
									<?php /* Her burde det være en lenke til rapporten hvis den finnes */ ?>
									<?php echo get_the_title($team->ID); ?></td>
							</tr>

						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

		<?php endif; ?>

<?php
	endwhile;
endif;
?>

<!-- new loop -->

<div class="table-scroll" role="region" aria-labelledby="caption" tabindex="0">
	<table>
		<thead>
			<tr>
				<th>Dag</th>
				<th>Dato</th>
				<th>Tid</th>
				<th>Hjemmelag</th>
				<th>Bortelag</th>
				<th>Bane</th>
				<th>Resultat</th>
			</tr>
		</thead>
		<?php
		// Setup a loop to get all posts assigned to current league
		global $post;
		$league = get_post($post)->post_name;

		$args = array(
			'post_type'      => 'fixture',
			'league'         => $league,
			'posts_per_page' => -1,
			'meta_key'       => 'date_time', // name of custom field
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		);

		$myposts = get_posts($args);
		foreach ($myposts as $post) :
			setup_postdata($post);
		?>

			<tr>
				<td class="weekday">
					<?php
					// Get date field for match
					$weekday = date('l', get_field('date_time'));
					if (!empty($weekday)) {

						esc_attr_e($weekday);
					} else {
						echo '&nbsp;';
					}
					?>
				</td>

				<td>
					<?php
					// Get date field for match
					$date = date('d.m', get_field('date_time'));
					if (!empty($date)) :
					?>

						<?php esc_attr_e($date); ?>

					<?php else : ?>
						&nbsp;
					<?php endif; ?>
				</td>

				<td>
					<?php
					// Get date field for match
					$time = date('H.i', get_field('date_time'));
					if (!empty($time)) :
					?>

						<?php esc_attr_e($time); ?>

					<?php else : ?>
						&nbsp;
					<?php endif; ?>
				</td>

				<td>
					<?php
					// Get home team
					$home_team = get_field('home_team');

					// If a home team is set, continue
					if (isset($home_team)) :

						// It is an object so i need to loop it
						foreach ($home_team as $hosts) :
					?>

							<?php echo $hosts->post_title; ?>

						<?php endforeach;
					else : ?>

						&nbsp;

					<?php endif; ?>
				</td>

				<td>
					<?php
					// Get away team
					$away_team = get_field('away_team');

					// If a away team is set, continue
					if (isset($away_team)) :

						// It is an object so i need to loop it
						foreach ($away_team as $guests) :
					?>

							<?php echo $guests->post_title; ?>

						<?php endforeach;
					else : ?>

						&nbsp;

					<?php endif; ?>
				</td>

				<td>
					<?php
					// Get the match pitch
					$pitches = get_field('pitch');

					// If pitch is found echo it to the table
					if (!empty($pitches)) :
						foreach ($pitches as $pitch) :
					?>

							<?php echo $pitch->post_title; ?>

						<?php
						endforeach;
					// If no pitch is found create a empty table cell
					else :
						?>
						&nbsp;
					<?php endif; ?>
				</td>

				<?php

				$home_team = get_field('home_team');
				$away_team = get_field('away_team');

				// Get post_name
				$home_team = $home_team[0]->post_name;
				$away_team = $away_team[0]->post_name;

				// Who is home team?
				// Set variable accordingly
				if ($home_team === 'kampbart') {
					$kampbart_away = $away_team;
					$opponent_away = $away_team;
				} else {
					$kampbart_home = $home_team;
					$opponent_home = $home_team;
				}
				?>

				<?php

				// Set vars in the start to not get PHP notices
				$kampbart_goals_first_half  = null;
				$opponent_goals_first_half  = null;
				$kampbart_goals_second_half = null;
				$opponent_goals_second_half = null;

				if (get_field('goals_assists_first_half')) {

					$kampbart_goals_first_half = 0;
					$opponent_goals_first_half = 0;

					while (has_sub_field('goals_assists_first_half')) {

						$goals_for = get_sub_field('goal_for');

						if ($goals_for === 'kampbart') {
							$kampbart_goals_first_half++;
						} else {
							$opponent_goals_first_half++;
						}
					}
				}

				if (get_field('goals_assists_second_half')) {

					$kampbart_goals_second_half = 0;
					$opponent_goals_second_half = 0;

					while (has_sub_field('goals_assists_second_half')) {

						$goals_for = get_sub_field('goal_for');

						if ($goals_for === 'kampbart') {
							$kampbart_goals_second_half++;
						} else {
							$opponent_goals_second_half++;
						}
					}
				}

				// Output final score and pause result
				$kampbart_final_score = $kampbart_goals_first_half + $kampbart_goals_second_half;
				$opponent_final_score = $opponent_goals_first_half + $opponent_goals_second_half;

				$matchday = strtotime(get_field('date_time'));
				$today    = get_the_time('U');

				if ($matchday <= $today) {
					if ($home_team === 'kampbart') {
						echo '<td class="';

						// Check outcome of match and add apropriate class
						if ($kampbart_final_score > $opponent_final_score) {
							echo 'win';
						} elseif ($kampbart_final_score === $opponent_final_score) {
							echo 'draw';
						} else {
							echo 'loss';
						}

						echo '"><a href="' . get_the_permalink() . '">';
						echo $kampbart_final_score . '–' . $opponent_final_score . ' (' . $kampbart_goals_first_half . '–' . $opponent_goals_first_half . ')';
						echo '</a></td>';
					} else {
						echo '<td class="';

						// Check outcome of match and add apropriate class
						if ($kampbart_final_score > $opponent_final_score) {
							echo 'win';
						} elseif ($kampbart_final_score === $opponent_final_score) {
							echo 'draw';
						} else {
							echo 'loss';
						}

						echo '"><a href="' . get_the_permalink() . '">';
						echo $opponent_final_score . '–' . $kampbart_final_score . ' (' . $opponent_goals_first_half . '–' . $kampbart_goals_first_half . ')';
						echo '</td>';
						echo '</a></td>';
					}
				} else {
					echo '<td>&nbsp;</td>';
				}
				?>
				</td>
			</tr>
		<?php endforeach; ?>

		<?php wp_reset_postdata(); ?>

	</table>
</div>

<?php get_footer(); ?>