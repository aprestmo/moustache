<?php

/**
 * Tournament template
 *
 * @package Moustache
 */

get_header();

$term = get_queried_object();
?>

<div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
	<div class="o-grid u-text-center">
		<div class="o-grid__item u-1/1">
			<section class="o-section-md">
				<header>
					<h1><?php echo esc_html(single_term_title('', false)); ?></h1>
					<h2>
						<?php
						$division = get_field('tournament_division', $term);
						if ($division) {
							echo esc_html($division->name);
						}
						?>
					</h2>
					<nav class="u-soft-bottom-md">
						<a href="#terminliste"><?php esc_html_e('Terminliste', 'moustache'); ?></a>
						<a href="#tabell"><?php esc_html_e('Tabell', 'moustache'); ?></a>
						<a href="#statistikk"><?php esc_html_e('Statistikk', 'moustache'); ?></a>
					</nav>
				</header>

				<div class="u-flow">
					<?php
					$clubs           = get_field('tournament_clubs', $term);
					$withdrawals     = get_field('tournament_withdrawals', $term);
					$clubs_withdrawn = get_field('tournament_whitdrawn_clubs', $term);

					if ($clubs) {
						echo '<p>' . esc_html__('Disse lagene deltar:', 'moustache') . '</p>';
						echo '<ul>';
						foreach ($clubs as $club) {
							if ('Kampbart' === get_the_title($club)) {
								echo '<li>' . esc_html(get_the_title($club)) . '</li>';
							} else {
								printf(
									'<li><a href="%1$s">%2$s</a></li>',
									esc_url(get_permalink($club)),
									esc_html(get_the_title($club))
								);
							}
						}
						echo '</ul>';

						if ($withdrawals) {
							echo '<p>' . esc_html__('Disse lagene har trukket seg:', 'moustache') . '</p>';
							echo '<ul>';
							foreach ($clubs_withdrawn as $club) {
								printf(
									'<li><a href="%1$s">%2$s</a></li>',
									esc_url(get_permalink($club)),
									esc_html(get_the_title($club))
								);
							}
							echo '</ul>';
						}
					}

					if (is_tax()) {
						$args = array(
							'posts_per_page' => -1,
							'post_type'      => 'fixture',
							'tax_query'      => array(
								array(
									'taxonomy' => get_queried_object()->taxonomy,
									'field'    => 'term_id',
									'terms'    => get_queried_object()->term_id,
								),
							),
							'meta_query'     => array(
								'relation' => 'OR',
								array(
									'key'     => 'new_date_time',
									'compare' => 'EXISTS',
								),
								array(
									'key'     => 'date_time',
									'compare' => 'EXISTS',
								),
							),
						);

						$query = new WP_Query($args);

						if ($query->have_posts()) {
							$posts = $query->posts;

							usort(
								$posts,
								function ($a, $b) {
									$a_new_date_time = get_field('new_date_time', $a->ID);
									$a_date_time     = get_field('date_time', $a->ID);
									$a_datetime      = $a_new_date_time ? strtotime($a_new_date_time) : strtotime($a_date_time);

									$b_new_date_time = get_field('new_date_time', $b->ID);
									$b_date_time     = get_field('date_time', $b->ID);
									$b_datetime      = $b_new_date_time ? strtotime($b_new_date_time) : strtotime($b_date_time);

									return $a_datetime - $b_datetime;
								}
							);
						}
					}

					if (! empty($posts)) :
					?>
						<div role="region" aria-labelledby="caption" tabindex="0">
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
									foreach ($posts as $post) :
										setup_postdata($post);
										$home_team    = get_field('home_team');
										$away_team    = get_field('away_team');
										$is_withdrawn = false;

										if ($clubs_withdrawn) {
											foreach ($home_team as $team) {
												if (in_array($team, $clubs_withdrawn, true)) {
													$is_withdrawn = true;
													break;
												}
											}
											if (! $is_withdrawn) {
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
											$date_time       = get_field('date_time');
											$postponed       = get_field('postponed');
											$new_date_time   = get_field('new_date_time');
											$display_date_time = $new_date_time ? $new_date_time : $date_time;

											if (! empty($postponed) && empty($new_date_time)) :
											?>
												<td colspan="3"><em><?php esc_html_e('Nytt tidspunkt kommer', 'moustache'); ?></em></td>
											<?php else : ?>
												<td>
													<?php
													$day = strtotime($display_date_time);
													echo esc_html(ucfirst(date_i18n('l', $day)));
													?>
												</td>
												<td>
													<?php
													$date = strtotime($display_date_time);
													echo esc_html(date_i18n('d.m', $date));
													?>
												</td>
												<td>
													<?php
													$time = strtotime($display_date_time);
													echo esc_html(date_i18n('H.i', $time));
													?>
												</td>
											<?php endif; ?>
											<td>
												<?php
												$home_team = get_field('home_team');
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
												$away_team = get_field('away_team');
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

											<?php
											$walkover             = get_field('walkover');
											$walkover_result      = esc_html(get_field('walkover_result'));
											$walkover_winner      = esc_html(get_field('walkover_winner'));
											$result_only          = get_field('result_only');
											$result_only_fulltime = esc_html(get_field('result_only_fulltime'));
											$result_fulltime      = esc_html(get_field('result_fulltime'));
											$result_pause         = esc_html(get_field('result_pause'));

											if ($walkover && $result_only) {
												echo '<td style="color: red">';
												esc_html_e('You can\'t select both walkover and inadequate report', 'moustache');
												echo '</td>';
											} elseif ($walkover) {
												$result_type = 'kampbart' === $walkover_winner ? 'u-tc--green' : 'u-tc--red';
												printf(
													'<td class="%1$s">%2$s <abbr title="Walkover">WO</abbr></td>',
													esc_attr($result_type),
													esc_html(get_field('walkover_result'))
												);
											} elseif ($result_only) {
												printf(
													'<td class="%1$s">%2$s</td>',
													esc_attr($result_type),
													$result_only_fulltime ? esc_html($result_fulltime) : esc_html($result_fulltime . ' (' . $result_pause . ')')
												);
											} else {
												$kampbart_goals_first_half  = 0;
												$opponent_goals_first_half  = 0;
												$kampbart_goals_second_half = 0;
												$opponent_goals_second_half = 0;

												if (have_rows('goals_assists_first_half')) {
													while (have_rows('goals_assists_first_half')) {
														the_row();
														$goals_for = get_sub_field('goal_for');
														if ('kampbart' === $goals_for) {
															$kampbart_goals_first_half++;
														} else {
															$opponent_goals_first_half++;
														}
													}
												}

												if (have_rows('goals_assists_second_half')) {
													while (have_rows('goals_assists_second_half')) {
														the_row();
														$goals_for = get_sub_field('goal_for');
														if ('kampbart' === $goals_for) {
															$kampbart_goals_second_half++;
														} else {
															$opponent_goals_second_half++;
														}
													}
												}

												$kampbart_final_score = $kampbart_goals_first_half + $kampbart_goals_second_half;
												$opponent_final_score = $opponent_goals_first_half + $opponent_goals_second_half;

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
															printf(
																'<td class="%1$s">%2$s</td>',
																esc_attr($result_type),
																'kampbart' === $team->post_name
																	? esc_html($kampbart_final_score . '&ndash;' . $opponent_final_score . ' (' . $kampbart_goals_first_half . '&ndash;' . $opponent_goals_first_half . ')')
																	: esc_html($opponent_final_score . '&ndash;' . $kampbart_final_score . ' (' . $opponent_goals_first_half . '&ndash;' . $kampbart_goals_first_half . ')')
															);
														}
													} else {
														echo '<td></td>';
													}
												}
											}
											?>
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
												$club_titles = array();
												foreach ($clubs_withdrawn as $club) {
													$club_titles[] = get_the_title($club);
												}

												echo wp_kses(
													'<span>' . implode('</span>, <span>', explode(', ', formatClubTitlesWithOg($club_titles))) . '</span> har trukket seg.',
													array('span' => array())
												);
												?>
											</td>
										</tr>
									</tfoot>
								<?php endif; ?>
							</table>
						</div>
					<?php endif; ?>

					<div id="tabell" role="region" aria-labelledby="caption" tabindex="0">
						<?php echo wp_kses_post(get_field('tournament_content', $term)); ?>
					</div>

					<div id="statistikk" role="region" aria-labelledby="caption" tabindex="0">
						<?php echo wp_kses_post(get_field('tournament_stats', $term)); ?>
					</div>
				</div>
			</section>
		</div>
	</div>
</div>

<?php
get_footer();
