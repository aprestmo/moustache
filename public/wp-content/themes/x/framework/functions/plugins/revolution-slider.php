<?php

// =============================================================================
// FUNCTIONS/GLOBAL/PLUGINS/REVOLUTION-SLIDER.PHP
// -----------------------------------------------------------------------------
// Plugin setup for theme compatibility.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Remove Notices
// =============================================================================

// Remove Notices
// =============================================================================

function x_revolution_slider_remove_plugin_row_notices() {
  remove_action( 'after_plugin_row_revslider/revslider.php', array('RevSliderAdmin', 'show_purchase_notice') );
  remove_action( 'after_plugin_row_revslider/revslider.php', array('RevSliderAdmin', 'show_update_notice') );
  remove_action( 'after_plugin_row_revslider/revslider.php', array('RevSliderAdmin', 'add_notice_wrap_pre') );
  remove_action( 'after_plugin_row_revslider/revslider.php', array('RevSliderAdmin', 'add_notice_wrap_post') );
}

add_action( 'admin_notices', 'x_revolution_slider_remove_plugin_row_notices' );
