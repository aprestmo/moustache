<?php

// =============================================================================
// LEGACY/SETUP.PHP
// -----------------------------------------------------------------------------
// Sets up the legacy theme views, features, options, et cetera.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Cranium Detection
//   02. Legacy Page Templates
// =============================================================================

// Cranium Detection
// =============================================================================

add_action('cs_will_output_header', function() {
  add_filter( 'x_legacy_cranium_headers', '__return_false' );
});

add_action('cs_will_output_footer', function() {
  add_filter( 'x_legacy_cranium_footers', '__return_false' );
});

add_action('cs_will_output_layout', function( $layout ) {

  $layout_settings = $layout->settings();

  if (!$layout_settings['header_enabled']) {
    add_filter( 'x_legacy_cranium_headers', '__return_false');
  }

  if (!$layout_settings['footer_enabled']) {
    add_filter('x_legacy_cranium_footers', '__return_false');
  }

});

add_filter( 'template_include', function( $template ) { // Run code after template_redirect

  $lgcy_path = X_TEMPLATE_PATH . '/framework/legacy';

  $cranium_headers = apply_filters( 'x_legacy_cranium_headers', true );
  $cranium_footers = apply_filters( 'x_legacy_cranium_footers', true );

  if ( $cranium_headers ) {
    require_once( $lgcy_path . '/cranium/headers/setup.php' );
    do_action( 'x_classic_headers' );
  }

  if ( $cranium_footers ) {
    require_once( $lgcy_path . '/cranium/footers/setup.php' );
    do_action( 'x_classic_footers' );
  }

  return $template;

}, 99999 );



// Legacy Page Templates
// =============================================================================

add_filter( 'cs_output_header', function( $output_header ) {
  $output_header = ! x_is_blank( 3 ) && ! x_is_blank( 6 ) && ! x_is_blank( 7 ) && ! x_is_blank( 8 );
  return $output_header;
} );

add_filter( 'cs_output_footer', function( $output_footer ) {
  $output_footer = ! x_is_blank( 2 ) && ! x_is_blank( 3 ) && ! x_is_blank( 5 ) && ! x_is_blank( 6 );
  return $output_footer;
} );