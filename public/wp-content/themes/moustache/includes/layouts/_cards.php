<?php

// Check for yellow cards
// @TODO: Check if the same player has got two yellow cards, and send him off

function cards()
{
	if (have_rows('cards_first_half')):
		echo '<strong>';
		esc_html_e('Card in 1st half', 'moustache');
		echo '</strong>';
		echo '<ul class="c-report u-soft-bottom-md">';
		while (have_rows('cards_first_half')) : the_row();
			$player = get_sub_field('card_player_first_half');
			$colour = get_sub_field('card_colour_first_half');
			echo '<li data-card="' . $colour . '">';
			echo '<a href="/spiller/' . $player->post_name . '">';
			esc_html_e($player->post_title);
			echo '</a>';
			echo '</li>';
		endwhile;
		echo '</ul>';
	endif;

	if (have_rows('cards_second_half')):
		echo '<strong>';
		esc_html_e('Card in 2nd half', 'moustache');
		echo '</strong>';
		echo '<ul class="c-report u-soft-bottom-md">';
		while (have_rows('cards_second_half')) : the_row();
			$player = get_sub_field('card_player_second_half');
			$colour = get_sub_field('card_colour_second_half');
			echo '<li data-card="' . $colour . '">';
			echo '<a href="/spiller/' . $player->post_name . '">';
			esc_html_e($player->post_title);
			echo '</a>';
			echo '</li>';
		endwhile;
		echo '</ul>';
	endif;
}
