<?php

// =============================================================================
// FUNCTIONS/GLOBAL/PLUGINS/GRAVITY-FORMS.PHP
// -----------------------------------------------------------------------------
// Plugin setup for theme compatibility.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Styles
// =============================================================================

// Styles
// =============================================================================

function x_gravity_forms_enqueue_styles() {

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

  wp_enqueue_style( 'x-gravity-forms', X_TEMPLATE_URL . '/framework/dist/css/site/gravity_forms/' . $stack . $ext . '.css', array( 'x-stack', 'gforms_reset_css', 'gforms_formsmain_css', 'gforms_ready_class_css', 'gforms_browsers_css' ), X_ASSET_REV, 'all' );

}

function x_gravity_forms_checker () {

  //Enqueue it globally but only when element or shortcode is present
  //The hook gform_enqueue_scripts is late binded, it appears after other enqueue or in the footer if it's called within the content.
  $checker = cornerstone('ShortcodeFinder');
  $shortcode = 'gravityform';

  global $post;

  if ( is_a( $post, 'WP_POST' ) ) {

      //check content of the current post
      $checker->process_content($shortcode, $post->ID);//shortcode
      $checker->process_content($shortcode, $post->ID, false); //for classic gravity form element

  }

  $header = cornerstone('Assignments')->get_last_active_header();

  if (! is_null( $header ) ) {
    $checker->process_content( $shortcode, $header->id(), false );
  }

  $footer = cornerstone('Assignments')->get_last_active_footer();

  if (! is_null( $footer ) ) {
    $checker->process_content( $shortcode, $footer->id(), false );
  }

  $layout = cornerstone('Assignments')->get_last_active_layout();

  if (! is_null( $layout ) ) {
    $checker->process_content( $shortcode, $layout->id(), false );
  }

  //if present, then enqueue it
  if ( $checker->has($shortcode) ) x_gravity_forms_enqueue_styles();

}

add_action('x_enqueue_styles', 'x_gravity_forms_checker', -1);