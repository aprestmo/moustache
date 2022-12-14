<?php

/**
 * Tournament template
 */
get_header(); ?>

<div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
  <div class="o-grid u-text-center">
    <div class="o-grid__item u-1/1 u-2/3@sm">
      <section class="o-section-md">
        <header>
          <h1 class="u-text-center"><?php esc_html_e(single_term_title()); ?></h1>
        </header>

        <?php
        // Needs escaping and probably more fields
        $term = get_queried_object();
        the_field('tournament_content', $term);
        ?>

        <div>
          <?php
          if (is_tax()) {
            query_posts($query_string . '&posts_per_page=-1&order=ASC');
          }
          ?>

          <?php if (have_posts()) : ?>
            <table>
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
              <?php
              while (have_posts()) :
                the_post();
              ?>
                <tr>
                  <td>
                    <?php
                    $day = get_field('date_time');
                    $day = strtotime($day);
                    echo ucfirst(date_i18n('l', $day));
                    ?>
                  </td>
                  <td>
                    <?php
                    $date = get_field('date_time');
                    $date = strtotime($date);
                    echo date_i18n('d.m', $date);
                    ?>
                  </td>
                  <td>
                    <?php
                    $time = get_field('date_time');
                    $time = strtotime($time);
                    echo date_i18n('H.i', $time);
                    ?>
                  </td>
                  <td>
                    <?php
                    $home_team = get_field('home_team');

                    foreach ($home_team as $team) {
                      echo $team->post_title;
                    }
                    ?>
                  </td>
                  <td>
                    <?php
                    $away_team = get_field('away_team');

                    foreach ($away_team as $team) {
                      echo $team->post_title;
                    }
                    ?>
                  </td>
                  <td>
                    <?php
                    $pitches = get_field('pitch');

                    foreach ($pitches as $pitch) {
                      echo $pitch->post_title;
                    }
                    ?>
                  </td>

                  <?php
                  $walkover                          = get_field('walkover');
                  $walkover_result                   = esc_html(get_field('walkover_result'));
                  $walkover_winner                   = esc_html(get_field('walkover_winner'));

                  $result_only                    = get_field('result_only');
                  $result_only_fulltime           = esc_html(get_field('result_only_fulltime'));
                  $result_fulltime           = esc_html(get_field('result_fulltime'));
                  $result_pause             = esc_html(get_field('result_pause'));

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
                    $kampbart_goals_first_half  = 0;
                    $opponent_goals_first_half  = 0;
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

                      echo '<td class="' . $result_type . '">';
                      if ($team->post_name === 'kampbart') {
                        esc_html_e($kampbart_final_score . '&ndash;' . $opponent_final_score . ' (' . $kampbart_goals_first_half . '&ndash;' . $opponent_goals_first_half . ')');
                      } else {
                        esc_html_e($opponent_final_score . '&ndash;' . $kampbart_final_score . ' (' . $opponent_goals_first_half . '&ndash;' . $kampbart_goals_first_half . ')');
                      }
                      echo '</td>';
                    }
                  }

                  ?>
                </tr>
              <?php endwhile; ?>
            </table>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</div>

<?php get_footer(); ?>
