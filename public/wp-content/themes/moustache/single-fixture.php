<?php get_header(); ?>

<?php
while (have_posts()) :
  the_post();
?>

	<?php
  $posts = get_posts(
    array(
      // 'numberposts' 	=> -1,
      'post_type' => 'fixture',
      // 'orderby' 		=> 'title',
      // 'order' 		=> 'ASC',
      // 'meta_key'		=> 'present'
    )
  );

  if ($posts) :
  ?>

<strong><?php print_r(get_field('match_report')); ?></strong>
	<h1><?php opponents(); ?></h1>
	<!-- <pre><?php print_r($posts); ?></pre> -->

	<?php

    // Get home and away team field
    // Returns object
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
    if (get_field('goals_assists_first_half')) {

      echo '<ul>';

      $kampbart_goals_first_half = 0;
      $opponent_goals_first_half = 0;

      while (has_sub_field('goals_assists_first_half')) {

        $goals_for = get_sub_field('goal_for');

        if ($goals_for === 'kampbart') {
          $kampbart_goals_first_half++;
        } else {
          $opponent_goals_first_half++;
        }

        // Set goalscore
        // I need to check who's home and who's away again
        echo '<li>';

        if ($home_team === 'kampbart') {
          // echo 'Kampbart er hjemmelag';
          echo $kampbart_goals_first_half . '–' . $opponent_goals_first_half;
        } else {
          echo $opponent_goals_first_half . '–' . $kampbart_goals_first_half;
        }

        if (!empty($goals_for) && $goals_for === 'kampbart') {

          $goal = get_sub_field('goal_scorer_first_half');
          if (!empty($goal)) {

            foreach ($goal as $player) {
              $player_page = get_the_permalink($player->ID);
              echo ' – <a href="' . $player_page . '">' . get_the_title($player->ID) . '</a>';
            }
          }

          $assist = get_sub_field('assist_first_half');
          if (!empty($assist)) {

            foreach ($assist as $player) {
              $player_page = get_the_permalink($player->ID);
              echo ' (<a href="' . $player_page . '">' . get_the_title($player->ID) . '</a>)';
            }
          }

          echo '</li>';
        }
      }

      echo '</ul>';

      $kampbart_goals_pause = $kampbart_goals_first_half;
      $opponent_goals_pause = $opponent_goals_first_half;
    }

    echo '<hr>';

    if (get_field('goals_assists_second_half')) {

      echo '<ul>';

      $kampbart_goals_second_half = $kampbart_goals_pause;
      $opponent_goals_second_half = $opponent_goals_pause;

      while (has_sub_field('goals_assists_second_half')) {

        $goals_for = get_sub_field('goal_for');

        if ($goals_for === 'kampbart') {
          $kampbart_goals_second_half++;
        } else {
          $opponent_goals_second_half++;
        }

        // Set goalscore
        // I need to check who's home and who's away again
        echo '<li>';

        if ($home_team === 'kampbart') {
          echo $kampbart_goals_second_half . '–' . $opponent_goals_second_half;
        } else {
          echo $opponent_goals_second_half . '–' . $kampbart_goals_second_half;
        }

        if (!empty($goals_for) && $goals_for === 'kampbart') {

          $goal = get_sub_field('goal_scorer_second_half');
          if (!empty($goal)) {

            foreach ($goal as $player) {
              $player_page = get_the_permalink($player->ID);
              echo ' – <a href="' . $player_page . '">' . get_the_title($player->ID) . '</a>';
            }
          }

          $assist = get_sub_field('assist_second_half');
          if (!empty($assist)) {

            foreach ($assist as $player) {
              $player_page = get_the_permalink($player->ID);
              echo ' (<a href="' . $player_page . '">' . get_the_title($player->ID) . '</a>)';
            }
          }

          echo '</li>';
        }
      }

      // This just stores the two teams final score into a variable for each
      // Don't know if I really need this for anything though
      $kampbart_goals_final_score = $kampbart_goals_second_half;
      $opponent_goals_final_score = $opponent_goals_second_half;

      echo '</ul>';
    }
  ?>

<?php endif; ?>
<?php endwhile; ?>

<?php
get_footer();
