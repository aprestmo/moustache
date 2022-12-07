<?php

// Check for yellow cards
// @TODO: Check if the same player has got two yellow cards, and send him off

function cards()
{

  $yellow_cards_first_half = get_field('yellow_card_first_half');
  $yellow_cards_second_half = get_field('yellow_card_second_half');

  // We can differentiate on which half it has happened in

  if (!empty($yellow_cards_first_half)) {

		echo '<strong>';
			esc_html_e('Warning in 1st half', 'moustache');
		echo '</strong>';

    echo '<ul>';

    foreach ($yellow_cards_first_half as $player) {
      echo '<li>' . get_the_title($player->ID) . '</li>';
    }

    echo '</ul>';
  }

  if (!empty($yellow_cards_second_half)) {

		echo '<strong>';
			esc_html_e('Warning in 2nd half', 'moustache');
		echo '</strong>';

    echo '<ul>';

    foreach ($yellow_cards_second_half as $player) {
      echo '<li>' . get_the_title($player->ID) . '</li>';
    }

    echo '</ul>';
  }

  // Check for red cards
  // @TODO: If one player is sent off, he can not be chosen in the other
  // half as well

  $red_cards_first_half = get_field('red_card_first_half');
  $red_cards_second_half = get_field('red_card_second_half');

  // We can differentiate on which half it has happened in

  if (!empty($red_cards_first_half)) {

		echo '<strong>';
			esc_html_e('Sent off in 1st half', 'moustache');
		echo '</strong>';

    echo '<ul>';

    foreach ($red_cards_first_half as $player) {
      echo '<li>' . get_the_title($player->ID) . '</li>';
    }

    echo '</ul>';
  }

  if (!empty($red_cards_second_half)) {

		echo '<strong>';
			esc_html_e('Sent off in 2nd half', 'moustache');
		echo '</strong>';

    echo '<ul>';

    foreach ($red_cards_second_half as $player) {
      echo '<li>' . get_the_title($player->ID) . '</li>';
    }

    echo '</ul>';
  }
}
