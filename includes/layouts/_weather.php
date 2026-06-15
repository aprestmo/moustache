<?php

// Attendance and weather
// @status: Needs icons and styling

function weather()
{
  $weather = get_field('weather');

  if ($weather) {
    echo '<dl>';

    echo '<dt>';
    esc_attr_e('Weather', 'moustache');
    echo '</dt>';

    echo '<dd>' . $weather . '<dd>';

    echo '</dl>';
  }
}
