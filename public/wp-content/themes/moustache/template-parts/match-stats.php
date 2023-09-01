<?php
global $post;
$posts = get_field('match_report');

if ($posts) :
?>

  <?php foreach ($posts as $post) : setup_postdata($post); ?>

    <header class="u-soft-bottom-md">
      <h4 class="u-flush-bottom"><?php opponents(); ?></h4>
      <?php date_time(); ?>
    </header>

    <!-- resultat -->
    <?php /*

		// Setup teams
		// @TODO: Make this as a function
		$home_team = get_field( 'home_team' );
		$away_team = get_field( 'away_team' );

		// Get post_name
		$home_team = $home_team[0]->post_name;
		$away_team = $away_team[0]->post_name;

		// Who is home team?
		// Set variable accordingly

		if ( $home_team === 'kampbart' ) {
			$kampbart_away = $away_team;
			$opponent_away = $away_team;
		} else {
			$kampbart_home = $home_team;
			$opponent_home = $home_team;
		}

		// Set vars in the start to not get PHP notices
		$kampbart_goals_first_half = null;
		$opponent_goals_first_half = null;
		$kampbart_goals_second_half = null;
		$opponent_goals_second_half = null;

		if ( get_field( 'goals_assists_first_half' ) ) {

			$kampbart_goals_first_half = 0;
			$opponent_goals_first_half = 0;

			while ( has_sub_field( 'goals_assists_first_half' ) ) {

				$goals_for = get_sub_field( 'goal_for' );

				if ( $goals_for === 'kampbart' ) {
					$kampbart_goals_first_half++;
				} else {
					$opponent_goals_first_half++;
				}
			}
		}

		if ( get_field( 'goals_assists_second_half' ) ) {

			$kampbart_goals_second_half = 0;
			$opponent_goals_second_half = 0;

			while ( has_sub_field( 'goals_assists_second_half' ) ) {

				$goals_for = get_sub_field( 'goal_for' );

				if ( $goals_for === 'kampbart' ) {
					$kampbart_goals_second_half++;
				} else {
					$opponent_goals_second_half++;
				}
			}
		}

		// Output final score and pause result
		$kampbart_final_score = $kampbart_goals_first_half + $kampbart_goals_second_half;
		$opponent_final_score = $opponent_goals_first_half + $opponent_goals_second_half;

		$matchday = strtotime( get_field( 'date_time' ) );
		$today = get_the_time( 'U' );

		if ( $matchday <= $today ) {
			if ( $home_team === 'kampbart' ) {

				echo $kampbart_final_score . '–' . $opponent_final_score . ' (' . $kampbart_goals_first_half . '–' . $opponent_goals_first_half . ')';
			} else {

				echo $opponent_final_score . '–' . $kampbart_final_score . ' (' . $opponent_goals_first_half . '–' . $kampbart_goals_first_half . ')';
			}
		}
	*/ ?>

    <?php weather(); ?>
    <?php attendance(); ?>
    <?php scores(); ?>
    <hr>
		<div>
			<?php cards(); ?>
		</div>
		<div>
			<?php present(); ?>
		</div>

  <?php endforeach;
  wp_reset_postdata(); ?>

<?php endif; ?>
