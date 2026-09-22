<?php

function scores()
{
	$post_id = get_the_ID();
	$unplayed_reason = moustache_fixture_unplayed_reason($post_id);
	$walkover = get_field('walkover', $post_id);
	$walkover_winner = get_field('walkover_winner', $post_id);

	if ($unplayed_reason === 'abandoned') {
		return;
	}

	if ($unplayed_reason === 'canceled' && !$walkover) {
		return;
	}

	if ($walkover) {
		echo '<hr>';
		if ($walkover_winner === 'kampbart') {
			echo wp_kses_post(__('<p>Opponent did not show up.<br> <strong>Kampbart wins by walkover.</strong></p>', 'moustache'));
		} else {
			echo wp_kses_post(__('<p>Kampbart did not show up.<br> <strong>Opponent wins by walkover</strong></p>', 'moustache'));
		}
		return;
	}

	$goals = moustache_get_goals($post_id);
	if ($goals === []) {
		return;
	}

	$kampbart_home = moustache_is_kampbart(moustache_get_home_team($post_id));
	$kampbart_goals = 0;
	$opponent_goals = 0;
	$current_half = '';

	foreach ($goals as $goal) {
		if ($current_half !== '' && $current_half !== $goal['half']) {
			echo '</ul><hr>';
			$current_half = $goal['half'];
			echo '<ul>';
		} elseif ($current_half === '') {
			$current_half = $goal['half'];
			echo '<ul>';
		}

		if ($goal['side'] === 'kampbart') {
			$kampbart_goals++;
		} else {
			$opponent_goals++;
		}

		$home_goals = $kampbart_home ? $kampbart_goals : $opponent_goals;
		$away_goals = $kampbart_home ? $opponent_goals : $kampbart_goals;

		echo '<li>';
		echo esc_html($home_goals . '–' . $away_goals);

		if ($goal['side'] === 'kampbart') {
			if ($goal['own_goal']) {
				echo ' – ' . esc_html__('Own goal', 'moustache');
				if ($goal['scorer']) {
					printf(
						' (<a href="%s">%s</a>)',
						esc_url(get_permalink($goal['scorer'])),
						esc_html(get_the_title($goal['scorer']))
					);
				}
			} elseif ($goal['scorer']) {
				printf(
					' &ndash; <a href="%s">%s</a>',
					esc_url(get_permalink($goal['scorer'])),
					esc_html(get_the_title($goal['scorer']))
				);
			}

			if ($goal['assist']) {
				printf(
					' (<a href="%s">%s</a>)',
					esc_url(get_permalink($goal['assist'])),
					esc_html(get_the_title($goal['assist']))
				);
			} elseif ($goal['assist_text'] !== '' && $goal['assist_text'] !== 'player_assist') {
				echo ' (' . esc_html($goal['assist_text']) . ')';
			}
		}

		echo '</li>';
	}

	if ($current_half !== '') {
		echo '</ul>';
	}
}
