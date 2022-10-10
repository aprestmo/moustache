<?php

// Set the two playing teams
// @status: Uncertain (may get new fields - opponent vs. kampbart)

function opponents()
{
  $home_team = get_field('home_team');
  $away_team = get_field('away_team');

  if ($home_team || $away_team) :

    foreach ($home_team as $hosts) {
      echo get_the_title($hosts->ID);
    }

    echo '–';

    foreach ($away_team as $guests) {
      echo get_the_title($guests->ID);
    }

  endif;
}
