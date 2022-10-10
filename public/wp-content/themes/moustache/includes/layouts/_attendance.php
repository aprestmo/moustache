<?php

// Attendance
// @status: Needs icons and styling

function attendance()
{
  $attendance = get_field('attendance');

  if ($attendance) {
    echo '<dl>';

    echo '<dt>';
    esc_attr_e('Attendace', 'moustache');
    echo '</dt>';

    echo '<dd>' . $attendance . '</dd>';

    echo '</dl>';
  }
}
