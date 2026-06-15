<?php
//Die if accessed directly
defined('ABSPATH') || die('Shame on you');

/** Opponents */
include get_template_directory() . '/includes/layouts/_opponents.php';

/** Date and time */
include get_template_directory() . '/includes/layouts/_date-time.php';

/** Attendance */
include get_template_directory() . '/includes/layouts/_attendance.php';

/** Weather */
include get_template_directory() . '/includes/layouts/_weather.php';

/** Scores */
include get_template_directory() . '/includes/layouts/_scores.php';

/** Present */
include get_template_directory() . '/includes/layouts/_present.php';

/** Cards */
include get_template_directory() . '/includes/layouts/_cards.php';
