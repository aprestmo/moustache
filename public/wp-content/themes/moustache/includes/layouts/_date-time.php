<?php

// Setup date, time and pitch

function date_time()
{
  $matchday = get_field('date_time');

  $machineDay = date('Y-m-d', strtotime($matchday));
  $machineTime = date('H:i:s', strtotime($matchday));

  $day = date_i18n('j. F Y', strtotime($matchday));
  $time = date_i18n('H.i', strtotime($matchday));

  if (!empty($matchday)) {
    echo '<time datetime="' . $machineDay . 'T' . $machineTime . '">';
    echo $day . ' ' . esc_html__('at', 'moustache') . ' ' . $time;
    echo '</time>';
  }
}
