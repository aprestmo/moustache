<?php

/**
 * Remove Widget Types
 * Comment out the ones you need if widgets your site uses widgets
 *
 * @since 1.0
 */
add_action('widgets_init', function () {
  $widgets = array(
    'WP_Widget_Pages',
    'WP_Widget_Calendar',
    'WP_Widget_Archives',
    'WP_Widget_Links',
    'WP_Widget_Meta',
    'WP_Widget_Search',
    'WP_Widget_Text',
    'WP_Widget_Categories',
    'WP_Widget_Recent_Posts',
    'WP_Widget_Recent_Comments',
    'WP_Widget_RSS',
    'WP_Widget_Tag_Cloud',
    'WP_Nav_Menu_Widget',
    'bcn_widget',
    'aksimet_widget',
  );

  if (is_array($widgets)) {
    foreach ($widgets as $w) {
      unregister_widget($w);
    }
  }
});
