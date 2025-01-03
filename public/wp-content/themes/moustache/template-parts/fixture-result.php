<?php

/**
 * Template part for displaying fixture result
 *
 * @package Moustache
 */

$walkover = get_field('walkover');
$walkover_result = get_field('walkover_result');
$walkover_winner = get_field('walkover_winner');
$result_only = get_field('result_only');
$result_only_fulltime = get_field('result_only_fulltime');
$result_fulltime = get_field('result_fulltime');
$result_pause = get_field('result_pause');

if ($walkover && $result_only) : ?>
	<td style="color: red">
		<?php esc_html_e('You can\'t select both walkover and inadequate report', 'moustache'); ?>
	</td>
<?php elseif ($walkover) :
	$result_type = 'kampbart' === $walkover_winner ? 'u-tc--green' : 'u-tc--red';
?>
	<td class="<?php echo esc_attr($result_type); ?>">
		<?php echo esc_html($walkover_result); ?>
		<abbr title="<?php esc_attr_e('Walkover', 'moustache'); ?>">WO</abbr>
	</td>
<?php elseif ($result_only) : ?>
	<td class="<?php echo esc_attr($result_type); ?>">
		<?php
		if ($result_only_fulltime) {
			echo esc_html($result_fulltime);
		} else {
			printf(
				'%s (%s)',
				esc_html($result_fulltime),
				esc_html($result_pause)
			);
		}
		?>
	</td>
	<?php else :
	$kampbart_goals_first_half = 0;
	$opponent_goals_first_half = 0;
	$kampbart_goals_second_half = 0;
	$opponent_goals_second_half = 0;

	// Count first half goals
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

	// Count second half goals
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

	// Determine result type
	if ($kampbart_final_score > $opponent_final_score) {
		$result_type = 'u-tc--green';
	} elseif ($kampbart_final_score === $opponent_final_score) {
		$result_type = 'u-tc--orange';
	} elseif ($kampbart_final_score < $opponent_final_score) {
		$result_type = 'u-tc--red';
	}

	foreach ($home_team as $team) :
		if (strtotime($date_time) < strtotime(date_i18n('h:i:s'))) :
			if ($postponed && empty($new_date_time)) : ?>
				<td></td>
			<?php else : ?>
				<td class="<?php echo esc_attr($result_type); ?>">
					<?php
					if ('kampbart' === $team->post_name) {
						printf(
							'%d&ndash;%d (%d&ndash;%d)',
							$kampbart_final_score,
							$opponent_final_score,
							$kampbart_goals_first_half,
							$opponent_goals_first_half
						);
					} else {
						printf(
							'%d&ndash;%d (%d&ndash;%d)',
							$opponent_final_score,
							$kampbart_final_score,
							$opponent_goals_first_half,
							$kampbart_goals_first_half
						);
					}
					?>
				</td>
			<?php endif;
		else : ?>
			<td></td>
<?php endif;
	endforeach;
endif;
?>