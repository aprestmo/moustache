<?php

// =============================================================================
// FUNCTIONS/GLOBAL/CLASS-VIEW-ROUTING.PHP
// -----------------------------------------------------------------------------
// View Routing in X.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. View Rendering
// =============================================================================

// View Rendering
// =============================================================================

function x_render_view( $_template_file, $_view_data = array()) {

  global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $comment, $user_ID;

  if ( is_array( $wp_query->query_vars ) ) {
      extract( $wp_query->query_vars, EXTR_SKIP );
  }

  if ( isset( $s ) ) {
    $s = esc_attr( $s );
  }

  $_extractable_data = ( is_callable( $_view_data ) ) ? call_user_func( $_view_data ) : $_view_data;

  if ( is_array( $_extractable_data ) ) {
    extract( $_extractable_data );
  }

  include( $_template_file );

}

function x_get_view( $directory, $file_base, $file_extension = '', $view_data = array(), $echo = true ) {

  $file_action = $directory . '_' . $file_base . ( empty( $file_extension ) ? '' : '-' . $file_extension );

  $view = array(
    'base'      => 'framework/views/' . $directory . '/' . $file_base,
    'extension' => $file_extension
  );

  $view = apply_filters( 'x_get_view', $view, $directory, $file_base, $file_extension );

  if ( '' === $view['base'] ) {
    return;
  }

  $slug = $view['base'];
  $ext_name = (string) $view['extension'];
  $templates = array();
  if ( '' !== $ext_name )
    $templates[] = "{$slug}-{$ext_name}.php";

  $templates[] = "{$slug}.php";

  $located = locate_template( $templates, false, false );


  $template = apply_filters('x_locate_template', $located, $view, $directory, $file_base, $file_extension );

  if ( ! $template ) {
    return;
  }

  if ( ! $echo ) {
    ob_start();
  }

  do_action( 'x_before_view_' . $file_action );
  x_render_view( $template, $view_data, $echo );
  do_action( 'x_after_view_' . $file_action );

  if ( !$echo ) {
    return ob_get_clean();
  }

  return '';

}
