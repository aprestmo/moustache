<?php

// =============================================================================
// FUNCTIONS/GLOBAL/PLUGINS/LAYERSLIDER.PHP
// -----------------------------------------------------------------------------
// Plugin setup for theme compatibility.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Theme Setup
//   02. Get Slider Meta
//   03. Add Slider Meta
// =============================================================================

// Theme Setup
// =============================================================================

function x_layerslider_theme_setup() {
  $GLOBALS['lsAutoUpdateBox'] = false;
  remove_action( 'after_plugin_row_' . LS_PLUGIN_BASE, 'layerslider_plugins_purchase_notice' );
}

add_action( 'layerslider_ready', 'x_layerslider_theme_setup' );
