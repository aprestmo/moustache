<?php

// List players who played the match
// @TODO: Check if we can set playernames by first letter automaticly

function present()
{
  $present = get_field('present');

  if ($present) :

    echo '<strong>';
    esc_html_e('Present', 'moustache');
    echo '</strong>';

    echo '<ul>';

    foreach ($present as $player) {
      // var_dump($present);
      echo '<li>' . get_the_title($player->ID) . '</li>';
    }

    echo '</ul>';
  endif;
}
