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
      echo '<li><a href="/spiller/' . $player->post_name . '">' . get_the_title($player->ID) . '</a></li>';
    }

    echo '</ul>';
  endif;
}
