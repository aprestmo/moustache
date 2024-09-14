<?php

/**
 * Tournament template
 */
get_header(); ?>

<?php $term = get_queried_object(); ?>

<div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
	<div class="o-grid u-text-center">
		<div class="o-grid__item u-1/1">
			<section class="o-section-md">
				<header>
					<h1><?php esc_html_e(single_term_title()); ?></h1>
					<h2><?php $division = get_field('tournament_division', $term);
						if ($division) {
							echo $division->name;
						}
						?>
					</h2>
					<nav class="u-soft-bottom-md">
						<a href="#terminliste">Terminliste</a>
						<a href="#tabell">Tabell</a>
						<a href="#statistikk">Statistikk</a>
					</nav>
				</header>

				<div class="u-flow">
					<?php
					$clubs = get_field('tournament_clubs', $term);
					$withdrawals = get_field('tournament_withdrawals', $term);
					$clubs_withdrawn = get_field('tournament_whitdrawn_clubs', $term);

					if ($clubs) {
						echo '<p>Disse lagene deltar:</p>';
						echo '<ul>';
						foreach ($clubs as $club) {
							echo '<li>' . get_the_title($club) . '</li>';
						}
						echo '</ul>';

						if ($withdrawals) {
							echo '<p>Disse lagene har trukket seg:</p>';
							echo '<ul>';
							foreach ($clubs_withdrawn as $club) {
								echo '<li>' .  get_the_title($club) . '</li>';
							}
							echo '</ul>';
						}
					}
					?>
					<?php
					if (is_tax()) {
						$args = array(
							'posts_per_page' => -1, // Ensure all posts are retrieved
							'post_type' => 'fixture', // Replace 'your_post_type' with your actual post type
							'tax_query' => array(
								array(
									'taxonomy' => get_queried_object()->taxonomy,
									'field' => 'term_id',
									'terms' => get_queried_object()->term_id,
								),
							),
							'meta_query' => array(
								'relation' => 'OR',
								array(
									'key' => 'new_date_time',
									'compare' => 'EXISTS',
								),
								array(
									'key' => 'date_time',
									'compare' => 'EXISTS',
								),
							),
						);

						$query = new WP_Query($args);

						if ($query->have_posts()) {
							$posts = $query->posts;

							// Custom sort function
							usort($posts, function ($a, $b) {
								$a_new_date_time = get_field('new_date_time', $a->ID);
								$a_date_time = get_field('date_time', $a->ID);
								$a_datetime = $a_new_date_time ? strtotime($a_new_date_time) : strtotime($a_date_time);

								$b_new_date_time = get_field('new_date_time', $b->ID);
								$b_date_time = get_field('date_time', $b->ID);
								$b_datetime = $b_new_date_time ? strtotime($b_new_date_time) : strtotime($b_date_time);

								return $a_datetime - $b_datetime;
							});
						}
					}
					?>

					<?php if (!empty($posts)) : ?>
						<div role="region" aria-labelledby="caption" tabindex="0">
							<table id="terminliste">
								<caption>Terminliste</caption>
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
									<?php foreach ($posts as $post) : setup_postdata($post); ?>
										<?php
										$home_team = get_field('home_team');
										$away_team = get_field('away_team');
										$is_withdrawn = false;

										// Check if any of the home or away team is in the withdrawn clubs list
										foreach ($home_team as $team) {
											if (in_array($team, $clubs_withdrawn)) {
												$is_withdrawn = true;
												break;
											}
										}
										if (!$is_withdrawn) {
											foreach ($away_team as $team) {
												if (in_array($team, $clubs_withdrawn)) {
													$is_withdrawn = true;
													break;
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
											?>

											<?php if (!empty($postponed) && empty($new_date_time)) : ?>
												<td colspan="3"><em>Nytt tidspunkt kommer</em></td>
											<?php else : ?>
												<td>
													<?php
													$day = strtotime($display_date_time);
													echo ucfirst(date_i18n('l', $day));
													?>
												</td>
												<td>
													<?php
													$date = strtotime($display_date_time);
													echo date_i18n('d.m', $date);
													?>
												</td>
												<td>
													<?php
													$time = strtotime($display_date_time);
													echo date_i18n('H.i', $time);
													?>
												</td>
											<?php endif; ?>
											<td>
												<?php
												$home_team = get_field('home_team');
												foreach ($home_team as $team) {
													if ($team->post_name !== 'kampbart') {
														echo '<a href="/klubb/' . $team->post_name . '">' . $team->post_title . '</a>';
													} else {
														echo $team->post_title;
													}
												}
												?>
											</td>
											<td>
												<?php
												$away_team = get_field('away_team');
												foreach ($away_team as $team) {
													if ($team->post_name !== 'kampbart') {
														echo '<a href="/klubb/' . $team->post_name . '">' . $team->post_title . '</a>';
													} else {
														echo $team->post_title;
													}
												}
												?>
											</td>
											<td>
												<?php
												$pitches = get_field('pitch');
												if ($pitches) {
													foreach ($pitches as $pitch) {
														echo '<a href="/bane/' . $pitch->post_name . '">' . $pitch->post_title . '</a>';
													}
												}
												?>
											</td>

											<?php
											$walkover = get_field('walkover');
											$walkover_result = esc_html(get_field('walkover_result'));
											$walkover_winner = esc_html(get_field('walkover_winner'));

											$result_only = get_field('result_only');
											$result_only_fulltime = esc_html(get_field('result_only_fulltime'));
											$result_fulltime = esc_html(get_field('result_fulltime'));
											$result_pause = esc_html(get_field('result_pause'));

											if ($walkover && $result_only) {
												echo '<td style="color: red">';
												esc_html_e('You can\'t select both walkover and inadequate report', 'moustache');
												echo '</td>';
											} elseif ($walkover) {
												if ($walkover_winner === 'kampbart') {
													$result_type = 'u-tc--green';
												} else {
													$result_type = 'u-tc--red';
												}

												// Need to differentiate who won on WO in backend and here
												echo '<td class="' . $result_type . '">';
												esc_html_e(get_field('walkover_result'));
												echo ' <abbr title=\'Walkover\'>WO</abbr>';
												echo '</td>';
											} elseif ($result_only) {
												// Set class based on result

												echo '<td class="' . $result_type . '">';

												if ($result_only_fulltime) {
													echo $result_fulltime;
												} else {
													echo $result_fulltime . ' (' . $result_pause . ')';
												}

												echo '</td>';
											} else {
												// Set vars in the start to not get PHP notices
												$kampbart_goals_first_half = 0;
												$opponent_goals_first_half = 0;
												$kampbart_goals_second_half = 0;
												$opponent_goals_second_half = 0;

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

												// Set class based on result
												if ($kampbart_final_score > $opponent_final_score) {
													$result_type = 'u-tc--green';
												} elseif ($kampbart_final_score === $opponent_final_score) {
													$result_type = 'u-tc--orange';
												} elseif ($kampbart_final_score < $opponent_final_score) {
													$result_type = 'u-tc--red';
												} else {
													$result_type = null;
												}

												foreach ($home_team as $team) {
													if (strtotime(get_field('date_time')) < strtotime(date_i18n('h:i:s'))) {
														if (get_field('postponed') && empty(get_field('new_date_time'))) {
															echo '<td></td>';
														} else {
															echo '<td class="' . $result_type . '">';
															if ($team->post_name === 'kampbart') {
																echo esc_html($kampbart_final_score . '&ndash;' . $opponent_final_score . ' (' . $kampbart_goals_first_half . '&ndash;' . $opponent_goals_first_half . ')');
															} else {
																echo esc_html($opponent_final_score . '&ndash;' . $kampbart_final_score . ' (' . $opponent_goals_first_half . '&ndash;' . $kampbart_goals_first_half . ')');
															}
															echo '</td>';
														}
													} else {
														echo '<td></td>';
													}
												}
											}
											?>
										</tr>
									<?php endforeach;
									wp_reset_postdata(); ?>
								</tbody>
								<?php if ($withdrawals) : ?>
									<tfoot>
										<tr>
											<td colspan="7">
												<?php
												// First, create an array of the club titles
												$club_titles = [];
												foreach ($clubs_withdrawn as $club) {
													$club_titles[] = get_the_title($club); // Assuming get_the_title() returns the club name
												}

												// Function to format club titles with 'og'
												function formatClubTitlesWithOg($titles)
												{
													$count = count($titles);

													if ($count == 1) {
														return '<span>' . $titles[0] . '</span>';
													} elseif ($count == 2) {
														return '<span>' . $titles[0] . '</span> og <span>' . $titles[1] . '</span>';
													} elseif ($count > 2) {
														$lastItem = array_pop($titles);  // Remove the last title
														return '<span>' . implode('</span>, <span>', $titles) . '</span> og <span>' . $lastItem . '</span>';
													}

													return '';  // If the array is empty, return an empty string
												}

												// Now, output the formatted list of club titles
												echo formatClubTitlesWithOg($club_titles) . ' har trukket seg.';
												?>
											</td>
										</tr>
									</tfoot>
								<?php endif; ?>
							</table>
						</div>
					<?php endif; ?>

					<?php
					// $term = get_queried_object();
					echo '<div id="tabell" role="region" aria-labelledby="caption" tabindex="0">';
					echo get_field('tournament_content', $term);
					echo '</div>';
					?>

					<?php
					/** Hent dynamisk senere */
					// $term = get_queried_object();
					echo '<div id="statistikk" role="region" aria-labelledby="caption" tabindex="0">';
					echo get_field('tournament_stats', $term);
					echo '</div>';
					?>
				</div>
			</section>
		</div>
	</div>
</div>

<?php get_footer(); ?>