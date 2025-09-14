<?php

// X i18n Lookup
// =============================================================================

function x_i18n( $namespace, $key ) {

  static $i18n = array();

  if ( ! isset( $i18n[$namespace] ) ) {
    $filename = X_I18N_PATH . "/theme-{$namespace}.php";
    if ( file_exists( $filename ) ) {
      $i18n[$namespace] = include( $filename );
    } else {
      $i18n[$namespace] = array();
    }
  }

  return isset( $i18n[$namespace][$key] ) ? $i18n[$namespace][$key] : '';

}