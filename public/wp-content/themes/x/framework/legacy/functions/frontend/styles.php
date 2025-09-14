<?php

// =============================================================================
// FUNCTIONS/FRONT-END/SETUP.PHP
// -----------------------------------------------------------------------------
// Generated scripts and styles.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Enqueue Site Styles
//   02. Generate Styles
//   03. Output Generated Styles
//   04. Caching
// =============================================================================

// Enqueue Site Styles
// =============================================================================

function x_enqueue_site_styles() {

  // Stack Data
  // ----------

  $stack  = x_get_stack();
  $design = x_get_option( 'x_integrity_design' );

  if ( $stack == 'integrity' && $design == 'light' ) {
    $ext = '-light';
  } elseif ( $stack == 'integrity' && $design == 'dark' ) {
    $ext = '-dark';
  } else {
    $ext = '';
  }


  // Enqueue Styles
  // --------------

  wp_enqueue_style( 'x-stack', X_TEMPLATE_URL . '/framework/dist/css/site/stacks/' . $stack . $ext . '.css', NULL, X_ASSET_REV, 'all' );

  do_action( 'x_enqueue_styles', $stack, $ext );

  if ( is_rtl() ) {
    wp_enqueue_style( 'x-rtl', X_TEMPLATE_URL . '/framework/dist/css/site/rtl/' . $stack . '.css', [ 'x-stack' ], X_ASSET_REV, 'all' );
  }

  if ( is_child_theme() && apply_filters( 'x_enqueue_parent_stylesheet', false ) ) {
    $rev = ( defined( 'X_CHILD_ASSET_REV' ) ) ? X_CHILD_ASSET_REV : X_ASSET_REV;
    wp_enqueue_style( 'x-child', get_stylesheet_directory_uri() . '/style.css', [ 'x-stack' ], $rev, 'all' );
  }

}

add_action( 'wp_enqueue_scripts', 'x_enqueue_site_styles' );



// Generate Styles
// =============================================================================

function x_get_generated_css() {

  $outp_path = X_TEMPLATE_PATH . '/framework/legacy/functions/frontend/generated-css';

  include( $outp_path . '/variables.php' );

  ob_start();

    include( $outp_path . '/' . $x_stack . '.php' );
    include( $outp_path . '/base.php' );
    include( $outp_path . '/buttons.php' );
    include( $outp_path . '/widgets.php' );
    include( $outp_path . '/bbpress.php' );
    include( $outp_path . '/buddypress.php' );
    include( $outp_path . '/woocommerce.php' );
    include( $outp_path . '/gravity-forms.php' );

  do_action( 'x_legacy_css' );

  return ob_get_clean();
}


// Output Generated Styles
// =============================================================================

function x_output_generated_styles() {

  if ( function_exists('cornerstone_register_styles') ) {
    cornerstone_register_styles('x', x_get_generated_css(), 0 );
  } else {
    wp_register_style( 'x-generated', false, [ 'x-stack' ] );
    wp_enqueue_style( 'x-generated' );
    wp_add_inline_style( 'x-generated', x_get_clean_css( x_get_generated_css() ) );
  }

}

add_action( 'wp_enqueue_scripts', 'x_output_generated_styles', 9998 );
