<?php

// =============================================================================
// FUNCTIONS/GLOBAL/BREADCRUMBS.PHP
// -----------------------------------------------------------------------------
// Sets up the breadcrumb navigation for the theme.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Breadcrumbs Output
//   02. Breadcrumbs Output Items
//   03. Breadcrumbs Data
// =============================================================================

// Breadcrumbs Output
// =============================================================================

if ( ! function_exists( 'x_breadcrumbs' ) ) :

  function x_breadcrumbs() {

    if ( ! x_get_option( 'x_breadcrumb_display' ) ) {
      return;
    }

    $args = [
      'item_before'        => '<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">',
      'item_after'         => '</span>',
      'label_before'       => '<span itemprop="name">',
      'label_after'        => '</span>',
      'delimiter_before'   => ' <span class="delimiter">',
      'delimiter_after'    => '</span> ',
      'delimiter_ltr'      => '<i class="x-icon-angle-right" data-x-icon-s="&#xf105;"></i>',
      'delimiter_rtl'      => '<i class="x-icon-angle-left" data-x-icon-s="&#xf104;"></i>',
      'current_class'      => 'current',
      'anchor_atts'        => array( 'itemtype' => 'http://schema.org/Thing', 'itemprop' => 'item' ),
      'include_meta'       => true,
      'include_taxonomies' => false,
      'home_label'         => '<span class="home"><i class="x-icon-home" data-x-icon-s="&#xf015;"></i></span><span class="visually-hidden">' . __( 'Home', '__x__' ) . '</span>',
      ];

    if ( ! get_option( 'page_for_posts' ) ) {

      $stack = x_get_stack();

      $blog_label = '';
      if ( 'integrity' === $stack ) {
        $blog_label = x_get_option('x_integrity_blog_title', '' );
      } elseif ( 'renew' === $stack ) {
        $blog_label = x_get_option('x_renew_blog_title', '' );
      }

      if ($blog_label) {
        $args['blog_label'] = $blog_label;
      }

    }

    $breadcrumbs_output = apply_filters( 'x_breadcrumbs', '', $args);

    echo '<div class="x-breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList" aria-label="Breadcrumb Navigation">' . $breadcrumbs_output . '</div>';

  }

endif;


// Prevent fatal errors in child themes using old functions

if ( ! function_exists( 'x_breadcrumbs_data' ) ) {
  function x_breadcrumbs_data( $args = array() ) {
    return [];
  }
}

if ( ! function_exists( 'x_breadcrumbs_items' ) ) {
  function x_breadcrumbs_items( $data = [], $args = [] ) {
    return '';
  }
}