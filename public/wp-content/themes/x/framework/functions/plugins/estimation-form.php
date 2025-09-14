<?php

// =============================================================================
// FUNCTIONS/GLOBAL/PLUGINS/estimation-form.php
// -----------------------------------------------------------------------------
// Plugin setup for theme compatibility.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Disable Licensing and Auto Updates
// =============================================================================

// Disable Licensing and Auto Updates
// =============================================================================

add_action('init', 'lfb_setThemeMode');

// Hack to hide validation message
add_action('admin_init', function() {
  // A bypass incase
  if (apply_filters('x_disable_estimation_form_hack', false)) {
    return;
  }

  try {
    // Run lfb code
    $instance = lfb_Core::instance(__FILE__, 'hack');
    $admin = lfb_Admin::instance($instance);

    $settings = $admin->getSettings();

    // Fine or LFB is not ready yet
    if (empty($settings) || !empty($settings->purchaseCode)) {
      return;
    }

    // Update DB purchase code
    global $wpdb;
    $table_name = $wpdb->prefix . "lfb_settings";
    $wpdb->update($table_name, [
      'purchaseCode' => 8,
    ], [
      'id' => $settings->id,
    ]);

  } catch(\Exception $e) {
    if (WP_DEBUG) {
      trigger_error($e->getMessage(), E_USER_NOTICE);
    }
  }
});
