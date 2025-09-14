<?php

// =============================================================================
// FUNCTIONS/GLOBAL/PLUGINS/ENVIRA-GALLERY.PHP
// -----------------------------------------------------------------------------
// Plugin setup for theme compatibility.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Hide License Functionality
// =============================================================================

// Hide License Functionality
// =============================================================================

function x_envira_gallery_hide_licensing() { ?>
  <style>
    #envira-settings-key-box, .envira-notice[data-notice*=warning-invalid-license-key] {
      display: none;
    }
  </style>
  <?php
}

add_action( 'envira_gallery_admin_styles', 'x_envira_gallery_hide_licensing' );
