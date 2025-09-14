<?php

// =============================================================================
// FUNCTIONS.PHP
// -----------------------------------------------------------------------------
// Theme functions for X.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Autoloader
//   02. Bootstrap Theme
// =============================================================================

if ( file_exists( get_template_directory() . '/dev.php' ) ) {
  require_once( get_template_directory() . '/dev.php' );
}

// Bootstrap Theme
// =============================================================================

require_once( __DIR__ . '/framework/classes/Theme.php' );
require_once( __DIR__ . '/framework/classes/Util/IocContainer.php' );

\Themeco\Theme\Theme::instantiate(
  get_template_directory(),
  get_template_directory_uri()
);



function x_bootstrap() {
  return \Themeco\Theme\Theme::instance();
}

\Themeco\Theme\Theme::instance()->boot([
  // Global Services
  'preinit' => [
    '\Themeco\Theme\Services\ViewRouter',
    '\Themeco\Theme\Services\Enqueue'
  ]
],[

  // Main Includes

  'preinit' => [
    'functions/i18n',
    'functions/setup',
    'functions/plugins/cornerstone',

    // Plugin Integrations
    [ class_exists( 'acf_pro' ), 'functions/plugins/acf-pro' ],
    [ class_exists( 'Convert_Plug' ), 'functions/plugins/convertplug' ],
    [ class_exists( 'Envira_Gallery' ), 'functions/plugins/envira-gallery' ],
    [ class_exists( 'Essential_Grid' ), 'functions/plugins/essential-grid' ],
    [ class_exists( 'LFB_Core' ), 'functions/plugins/estimation-form' ],
    [ class_exists( 'WPLeadInAdmin' ) || class_exists( 'LeadinAdmin' ), 'functions/plugins/hubspot'],
    [ class_exists( 'LS_Sliders' ), 'functions/plugins/layerslider' ],
    [ class_exists( 'MEC' ), 'functions/plugins/modern-events-calendar' ],
    [ class_exists( 'RevSlider' ), 'functions/plugins/revolution-slider' ],
    [ class_exists( 'Soliloquy' ), 'functions/plugins/soliloquy'],
    [ class_exists( 'UberMenu' ), 'functions/plugins/ubermenu']
  ]
]);
