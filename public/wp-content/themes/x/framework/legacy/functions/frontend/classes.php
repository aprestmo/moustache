<?php

// =============================================================================
// FUNCTIONS/GLOBAL/CLASSES.PHP
// -----------------------------------------------------------------------------
// Outputs custom classes for various elements, sometimes depending on options
// selected in the Customizer.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Body Class
//   02. Post Class
//   03. Main Content Class
//   04. Sidebar Class
//   05. Portfolio Entry Class
//   06. Responsive Embeds
// =============================================================================

// Body Class
// =============================================================================

if ( ! function_exists( 'x_body_class' ) ) :
  function x_body_class( $output ) {

    $stack     = x_get_stack();
    $entry_id  = get_the_ID();

    //
    // Stack.
    //

    if ( $stack == 'icon' ) {

      $output[] .= 'x-stack-' . $stack;

    } else {

      $output[] .= 'x-' . $stack;

      if ( $stack == 'integrity' ) {
        if ( x_get_option( 'x_integrity_design' ) == 'dark' ) {
          $output[] .= 'x-integrity-dark';
        } else {
          $output[] .= 'x-integrity-light';
        }
      }

    }


    //
    // Custom.
    //

    $custom_class = get_post_meta( $entry_id, '_x_entry_body_css_class', true );

    if ( $custom_class != '' && is_singular() ) {
      $output[] .= $custom_class;
    }


    //
    // Child Theme.
    //

    if ( is_child_theme() ) {
      $output[] = 'x-child-theme-active';
    }


    //
    // Site layout.
    //

    switch ( x_get_site_layout() ) {
      case 'boxed' :
        $output[] .= 'x-boxed-layout-active';
        break;
      case 'full-width' :
        $output[] .= 'x-full-width-layout-active';
        break;
    }


    //
    // Don't continue adding classes if the Layout Builder is being used
    //

    if ( did_action( 'cs_will_output_layout' ) ) {
      return $output;
    }


    //
    // Content layout.
    //

    switch ( x_get_content_layout() ) {
      case 'content-sidebar' :
        $output[] .= 'x-content-sidebar-active';
        break;
      case 'sidebar-content' :
        $output[] .= 'x-sidebar-content-active';
        break;
      case 'full-width' :
        $output[] .= 'x-full-width-active';
        break;
    }


    //
    // Blog and posts.
    //

    if ( is_home() ) {
      if ( x_get_option( 'x_blog_style' ) == 'masonry' ) {
        $output[] .= 'x-masonry-active x-blog-masonry-active';
      } else {
        $output[] .= 'x-blog-standard-active';
      }
    }

    if ( x_get_option( 'x_blog_enable_post_meta' ) == '' ) {
      $output[] .= 'x-post-meta-disabled';
    }


    //
    // Archives.
    //

    if ( is_archive() && ! x_is_shop() ) {
      if ( x_get_option( 'x_archive_style' ) == 'masonry' ) {
        $output[] .= 'x-masonry-active x-archive-masonry-active';
      } else {
        $output[] .= 'x-archive-standard-active';
      }
    }


    //
    // Pages.
    //

    if ( is_page() && get_post_meta( $entry_id, '_x_entry_disable_page_title', true ) == 'on' ) {
      $output[] .= 'x-page-title-disabled';
    }


    //
    // Portfolio.
    //

    if ( is_page_template( 'template-layout-portfolio.php' ) ) {
      $output[] .= 'x-masonry-active x-portfolio-masonry-active';
    }

    if ( x_get_option( 'x_portfolio_enable_post_meta' ) == '' ) {
      $output[] .= 'x-portfolio-meta-disabled';
    }


    //
    // Icon.
    //

    if ( $stack == 'icon' && get_post_meta( $entry_id, '_x_icon_blank_template_sidebar', true ) == 'Yes' ) {
      $output[] .= 'x-blank-template-sidebar-active';
    }


    //
    // Ethos.
    //

    if ($stack == 'ethos') {

      if ( is_home() && x_get_option( 'x_ethos_post_slider_blog_enable' ) == 1 ) {
        $output[] .= 'x-post-slider-blog-active';
      }

      if ( ( is_category() || is_tag() ) && x_get_option( 'x_ethos_post_slider_archive_enable' ) == 1 ) {
        $output[] .= 'x-post-slider-archive-active';
      }

    }


    return $output;

  }
  add_filter( 'body_class', 'x_body_class' );
endif;



// Post Class
// =============================================================================

if ( ! function_exists( 'x_post_class' ) ) :
  function x_post_class( $output, $class, $post_id ) {

    switch ( has_post_thumbnail( $post_id ) ) {
      case true :
        $output[] = 'has-post-thumbnail';
        break;
      case false :
        $output[] = 'no-post-thumbnail';
        break;
    }

    return $output;

  }
  add_filter( 'post_class', 'x_post_class', 10, 3 );
endif;



// Main Content Class
// =============================================================================

if ( ! function_exists( 'x_main_content_class' ) ) :
  function x_main_content_class( $echo = true ) {

    $output = '';

    switch ( x_get_content_layout() ) {
      case 'content-sidebar' :
        $output = 'x-main left';
        break;
      case 'sidebar-content' :
        $output = 'x-main right';
        break;
      case 'full-width' :
        $output = 'x-main full';
        break;
    }

    if ( $echo ) echo $output;
    else return $output;

  }
endif;



// Sidebar Class
// =============================================================================

if ( ! function_exists( 'x_sidebar_class' ) ) :
  function x_sidebar_class() {

    switch ( x_get_content_layout() ) {
      case 'content-sidebar' :
        $output = 'x-sidebar right';
        break;
      case 'sidebar-content' :
        $output = 'x-sidebar left';
        break;
      default :
        $output = 'x-sidebar right';
    }

    echo $output;

  }
endif;

// Responsive Embeds
// =============================================================================

add_filter('embed_oembed_html', 'x_responsive_embed_class', 10, 4);

if ( ! function_exists( 'x_responsive_embed_class' ) ) :
  function x_responsive_embed_class($html, $url, $attr, $post_id) {

    if (! function_exists( 'cs_tag' ) ) {
      return $html;
    }

    // Dont show embed for our site
    if (
      ! x_get_option("x_site_link_oembed")
      || (
        ! x_get_option("x_site_link_oembed_own_site")
        && strpos($url, get_site_url()) !== false
      )
    ) {
      return $url;
    }

    $embed = x_get_embed_cache( $url, $attr, $post_id );

    $type = !empty($embed['info'])
      ? $embed['info']->type
      : "unknown";

    $providerName = !empty($embed['info'])
      ? $embed['info']->provider_name
      : "unknown";

    $classes = [ $type == 'video' ? 'x-resp-embed' : 'x-embed' ]; //responsive wrapper or not
    $classes[] = 'x-is-' . $type;
    $classes[] = 'x-is-' . sanitize_title ( $providerName );

    return cs_tag( 'div', [ 'class' => $classes], $html );

  }
endif;
