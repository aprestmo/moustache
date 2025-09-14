<?php

// =============================================================================
// FUNCTIONS/GLOBAL/PLUGINS/CORNERSTONE.PHP
// -----------------------------------------------------------------------------
// Plugin setup for theme compatibility.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Setup
//   01. MEJS [audio]
//   02. MEJS [video]
//   03. Validation Box Replacement
//   04. Validation Overlay Replacement
//   05. Hide X validation notice on Cornertstone home page
//   06. Remove Cornerstone Validation Notice
//   07. Cornerstone Home Scripts
//   08. Label Replacements
//   09. Typekit output hook
// =============================================================================

// Front End
// =============================================================================

add_theme_support( 'cornerstone-managed', [ 'handle' => 'x-stack' ] );


add_filter( 'cornerstone_scrolltop_selector', function() {
  return '.x-navbar-fixed-top';
});

add_action( 'cornerstone_load_preview', function () {
  if ( defined( 'X_VIDEO_LOCK_VERSION' ) ) {
    remove_action( 'wp_footer', 'x_video_lock_output' );
  }
});

add_filter( 'cs_fa_config', function ( $fa ) {
  return array_merge( $fa, array(
    'fa_solid_enable'   => (bool) x_get_option( 'x_font_awesome_solid_enable' ),
    'fa_regular_enable' => (bool) x_get_option( 'x_font_awesome_regular_enable' ),
    'fa_light_enable'   => (bool) x_get_option( 'x_font_awesome_light_enable' ),
    'fa_brands_enable'  => (bool) x_get_option( 'x_font_awesome_brands_enable' ),
  ) );
} );


// Remove empty p and br HTML elements for legacy pages not using Cornerstone sections
// This is for compatibility with content created with X shortcodes
function x_cs_legacy_the_content( $the_content ) {

  if ( $the_content && function_exists('cs_noemptyp')) {

    global $cs_shortcode_aliases;

    if ( is_array($cs_shortcode_aliases) ) {
      $legacy = false;

      foreach ($cs_shortcode_aliases as $shortcode) {

        if ( false == strpos($the_content, "[$shortcode" ) ) {
          $legacy = true;
          break;
        }

      }

      if ( $legacy ) {
        return cs_noemptyp($the_content);
      }

    }
  }

  return $the_content;

}

add_filter( 'the_content', 'x_cs_legacy_the_content' );



// Admin
// =============================================================================

// Prevent Cornerstone from messaging about validation
add_filter('_cornerstone_integration_remove_global_validation_notice', '__return_true' );

add_filter( '_cs_validation_url', 'x_addons_get_link_home' );

add_filter( 'pre_option_cs_product_validation_key', function( $key ) {
  return get_option( 'x_product_validation_key', false );
} );

add_action( 'admin_init', function() {
  if ( ! has_action( '_cornerstone_home_not_validated' ) ) {
    add_action( '_cornerstone_home_not_validated', '__return_empty_string' );
  }
});


// Misc
// =============================================================================

add_filter( 'cs_recent_posts_post_types', function( $types ) {
  $types['portfolio'] = 'x-portfolio';
  return $types;
});



// Classic Elements
// =============================================================================

add_filter( 'cornerstone_looks_like_support', '__return_true' ); // used on classic elements to output a

// Alias legacy shortcode names.
add_action( 'cornerstone_shortcodes_loaded', function () {

  //
  // Alias [social] to [icon] for backwards compatability.
  //

  cs_alias_shortcode( 'social', 'x_icon', false );

  //
  // Alias deprecated shortcode names.
  //

  // Mk2
  cs_alias_shortcode( array( 'alert', 'x_alert' ), 'cs_alert' );
  cs_alias_shortcode( array( 'x_text' ), 'cs_text' );
  cs_alias_shortcode( array( 'icon_list', 'x_icon_list' ), 'cs_icon_list' );
  cs_alias_shortcode( array( 'icon_list_item', 'x_icon_list_item' ), 'cs_icon_list_item' );

  // Mk1 backwards compatibility pre Cornerstone
  cs_alias_shortcode( 'accordion',            'x_accordion', false );
  cs_alias_shortcode( 'accordion_item',       'x_accordion_item', false );
  cs_alias_shortcode( 'author',               'x_author', false );
  cs_alias_shortcode( 'block_grid',           'x_block_grid', false );
  cs_alias_shortcode( 'block_grid_item',      'x_block_grid_item', false );
  cs_alias_shortcode( 'blockquote',           'x_blockquote', false );
  cs_alias_shortcode( 'button',               'x_button', false );
  cs_alias_shortcode( 'callout',              'x_callout', false );
  cs_alias_shortcode( 'clear',                'x_clear', false );
  cs_alias_shortcode( 'code',                 'x_code', false );
  cs_alias_shortcode( 'column',               'x_column', false );
  cs_alias_shortcode( 'columnize',            'x_columnize', false );
  cs_alias_shortcode( 'container',            'x_container', false );
  cs_alias_shortcode( 'content_band',         'x_content_band', false );
  cs_alias_shortcode( 'counter',              'x_counter', false );
  cs_alias_shortcode( 'custom_headline',      'x_custom_headline', false );
  cs_alias_shortcode( 'dropcap',              'x_dropcap', false );
  cs_alias_shortcode( 'extra',                'x_extra', false );
  cs_alias_shortcode( 'feature_headline',     'x_feature_headline', false );
  cs_alias_shortcode( 'gap',                  'x_gap', false );
  cs_alias_shortcode( 'google_map',           'x_google_map', false );
  cs_alias_shortcode( 'google_map_marker',    'x_google_map_marker', false );
  cs_alias_shortcode( 'highlight',            'x_highlight', false );
  cs_alias_shortcode( 'icon',                 'x_icon', false );
  cs_alias_shortcode( 'image',                'x_image', false );
  cs_alias_shortcode( 'lightbox',             'x_lightbox', false );
  cs_alias_shortcode( 'line',                 'x_line', false );
  cs_alias_shortcode( 'map',                  'x_map', false );
  cs_alias_shortcode( 'pricing_table',        'x_pricing_table', false );
  cs_alias_shortcode( 'pricing_table_column', 'x_pricing_table_column', false );
  cs_alias_shortcode( 'promo',                'x_promo', false );
  cs_alias_shortcode( 'prompt',               'x_prompt', false );
  cs_alias_shortcode( 'protect',              'x_protect', false );
  cs_alias_shortcode( 'pullquote',            'x_pullquote', false );
  cs_alias_shortcode( 'raw_output',           'x_raw_output', false );
  cs_alias_shortcode( 'recent_posts',         'x_recent_posts', false );
  cs_alias_shortcode( 'responsive_text',      'x_responsive_text', false );
  cs_alias_shortcode( 'search',               'x_search', false );
  cs_alias_shortcode( 'share',                'x_share', false );
  cs_alias_shortcode( 'skill_bar',            'x_skill_bar', false );
  cs_alias_shortcode( 'slider',               'x_slider', false );
  cs_alias_shortcode( 'slide',                'x_slide', false );
  cs_alias_shortcode( 'tab_nav',              'x_tab_nav', false );
  cs_alias_shortcode( 'tab_nav_item',         'x_tab_nav_item', false );
  cs_alias_shortcode( 'tabs',                 'x_tabs', false );
  cs_alias_shortcode( 'tab',                  'x_tab', false );
  cs_alias_shortcode( 'toc',                  'x_toc', false );
  cs_alias_shortcode( 'toc_item',             'x_toc_item', false );
  cs_alias_shortcode( 'visibility',           'x_visibility', false );

  Cornerstone_Shortcode_Preserver::preserve( 'code' );

});



// MEJS [audio]
// =============================================================================

//
// 1. Library.
// 2. Output.
// 3. Class.
//



if ( !function_exists( 'x_native_wp_audio_shortcode_library' ) ) :

  function x_native_wp_audio_shortcode_library() { // 1
    wp_enqueue_script( 'mediaelement' );
    return false;
  }

  add_filter( 'wp_audio_shortcode_library', 'x_native_wp_audio_shortcode_library' );
endif;


if ( !function_exists( 'x_native_wp_audio_shortcode' ) ) :

  function x_native_wp_audio_shortcode( $html ) { // 2
    return '<div class="x-audio player" data-x-element-mejs>' . $html . '</div>';
  }

  add_filter( 'wp_audio_shortcode', 'x_native_wp_audio_shortcode' );
endif;


if ( !function_exists( 'x_native_wp_audio_shortcode_class' ) ) :

  function x_native_wp_audio_shortcode_class() { // 3
    return 'x-mejs x-wp-audio-shortcode advanced-controls';
  }

  add_filter( 'wp_audio_shortcode_class', 'x_native_wp_audio_shortcode_class' );
endif;

// MEJS [video]
// =============================================================================

//
// 1. Library.
// 2. Output.
// 3. Class.
//

if ( !function_exists( 'x_native_wp_video_shortcode_library' ) ) :

  function x_native_wp_video_shortcode_library() { // 1
    wp_enqueue_script( 'mediaelement' );
    return false;
  }

  add_filter( 'wp_video_shortcode_library', 'x_native_wp_video_shortcode_library' );
endif;


if ( !function_exists( 'x_native_wp_video_shortcode' ) ) :

  function x_native_wp_video_shortcode( $output ) { // 2
    return '<div class="x-video player" data-x-element-mejs>' . preg_replace('/<div(.*?)>/', '<div class="x-video-inner">', $output ) . '</div>';
  }

  add_filter( 'wp_video_shortcode', 'x_native_wp_video_shortcode' );
endif;


if ( !function_exists( 'x_native_wp_video_shortcode_class' ) ) :

  function x_native_wp_video_shortcode_class() { // 3
    return 'x-mejs x-wp-video-shortcode advanced-controls';
  }

  add_filter( 'wp_video_shortcode_class', 'x_native_wp_video_shortcode_class' );
endif;
