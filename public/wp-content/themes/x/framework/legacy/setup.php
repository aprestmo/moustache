<?php

// =============================================================================
// LEGACY/SETUP.PHP
// -----------------------------------------------------------------------------
// Sets up the legacy theme views, features, options, et cetera.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Theme Support
//   02. Theme Options
//   02. Template Routing
//   03. Content Width
//   04. Widget Areas
//   05. Responsive Styling
// =============================================================================

// Theme Support
// =============================================================================

add_theme_support( 'cornerstone-classic' );

add_theme_support( 'cornerstone-legacy-portfolio' );
add_theme_support( 'cornerstone-legacy-sidebars' );


remove_action( 'wp_head', 'x_meta' );

// Template Routing
// =============================================================================

add_action('template_redirect', function() {

  remove_all_actions('cs_header');
  remove_all_actions('cs_footer');
  remove_all_actions('cs_sidebar');

  add_action('cs_header', function() {
    x_get_view( 'header', 'base' );
  });

  add_action('cs_footer', function() {
    x_get_view( 'footer', 'base' );
  });

  add_action('cs_sidebar', function() {
    x_get_view( x_get_stack(), 'wp', 'sidebar' );
  });

  // Search Form
  // -----------

  add_filter( 'get_search_form', function($form, $args ) {
    if ( is_child_theme() && file_exists( get_stylesheet_directory() . '/searchform.php' ) ) {
      return $form;
    }
    ob_start();
    include X_TEMPLATE_PATH . '/framework/legacy/templates/searchform.php';
    return ob_get_clean();
  },-9999,2);

  // Comments
  // -----------

  add_filter( 'comments_template', function($existing) {
    if ( is_child_theme() && file_exists( $existing ) ) {
      return $existing;
    }
    return X_TEMPLATE_PATH . '/framework/legacy/templates/comments.php';
  },-9999);




  // Top Level Templates
  // -------------------

  $types = apply_filters( 'x_template_hierarchy_types', ['index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date', 'embed', 'home', 'frontpage', 'privacypolicy', 'page', 'paged', 'search', 'single', 'singular', 'attachment'] );

  $is_child = is_child_theme();
  foreach( $types as $type ) {

    add_filter( $type . '_template_hierarchy', function($templates) use ($type, $is_child) {
      $adjusted = [];
      foreach ($templates as $template) {
        if ( ! $is_child || ! file_exists( get_stylesheet_directory() . '/' . $template ) ) {
          $adjusted[] = "framework/legacy/templates/$template";
        }
        $adjusted[] = $template;
      }

      return $adjusted;
    });
  }


  add_action('x_before_site_end', function() {
    do_action( 'cs_deferred' );
  }, 20 );

  if (X_SLUG === 'pro') {

    remove_all_actions( 'cs_connect_masthead' );
    remove_all_actions( 'cs_connect_colophon' );

    add_action( 'cs_connect_masthead', function() {
      add_action('x_after_site_begin', function() {
        x_get_view( 'header', 'masthead', '' );
      });
    });

    add_action( 'cs_connect_colophon', function() {
      add_action( 'x_before_site_end', function() {
        x_get_view( 'footer', 'colophon', '' );
      }, -100);
    });

  }

  // Reset Layout Attributes
  // -----------------------

  add_filter( 'cs_layout_atts', function( $atts ) {
    $atts['class'] = ['x-layout'];

    if (is_singular() || is_single() ) {
      $atts['class'][] = 'x-layout-single';
    }

    if ( is_archive() ) {
      $atts['class'][] = 'x-layout-archive';
    }

    return $atts;
  });

  add_action('x_colophon', function() {
    do_action( 'cs_colophon' );
  });

  add_action('x_masthead', function() {
    do_action( 'cs_masthead' );
  });

}, 0 );


// Dequeue
// -------

add_action( 'x_enqueue_styles', function() {
  // Stop outputting primary styles since stack styles will be present
  wp_dequeue_style( 'x-theme' );
  wp_dequeue_style( 'x-child-theme' );
}, 100 );


// Content Width
// -------------

global $content_width;

if ( ! isset( $content_width ) ) :
  $stack = x_get_stack();
  switch ( $stack ) {
    case 'integrity' :
      $content_width = x_post_thumbnail_width() - 120;
      break;
    case 'renew' :
      $content_width = x_post_thumbnail_width();
      break;
    case 'icon' :
      $content_width = x_post_thumbnail_width();
      break;
    case 'ethos' :
      $content_width = x_post_thumbnail_width();
      break;
  }
endif;

// Ensure index.php has content. Sometimes plugins resolve this template
add_action('tco_theme_template', function() {

  if (is_singular() || is_single()) {
    x_get_view( x_get_stack(), 'wp', 'single' );
  } else {
    x_get_view( x_get_stack(), 'wp', 'index' );
  }

});


add_filter( 'theme_templates', function( $post_templates, $theme, $post, $post_type ) {

  $cache_key = md5('x-stack-legacy-page-templates');

  $more_post_templates = null;// wp_cache_get($cache_key, 'x-legacy' );

  if ( ! is_array( $more_post_templates ) ) {
    $more_post_templates = array();

    $path = X_TEMPLATE_PATH . '/framework/legacy/templates/';
    $relative_path = '';
    $results = scandir( $path );
    $files   = array();

    foreach ( $results as $result ) {
      if ( '.' === $result[0] || is_dir( $path . '/' . $result )) {
        continue;
      }

      $files[ $relative_path . $result ] = $path . '/' . $result;

    }

    $get_file = function($path ) {
      return call_user_func(implode('_', ['file', 'get', 'contents' ]), $path);
    };

    foreach ( $files as $file => $full_path ) {
      if ( ! preg_match( '|Template Name:(.*)$|mi', $get_file( $full_path ), $header ) ) {
        continue;
      }

      $types = array( 'page' );
      if ( preg_match( '|Template Post Type:(.*)$|mi', $get_file( $full_path ), $type ) ) {
        $types = explode( ',', _cleanup_header_comment( $type[1] ) );
      }

      foreach ( $types as $type ) {
        $type = sanitize_key( $type );
        if ( ! isset( $more_post_templates[ $type ] ) ) {
          $more_post_templates[ $type ] = array();
        }

        $more_post_templates[ $type ][ $file ] = _cleanup_header_comment( $header[1] );
      }
    }

    wp_cache_add( $cache_key, $more_post_templates, 'x-legacy', 1800 );
  }

  if ( isset( $more_post_templates[ $post_type ] ) ) {
    return array_merge( $post_templates, $more_post_templates[ $post_type ] );
  }

  return $post_templates;


}, 10, 4 );



// Widget Areas
// =============================================================================

if ( ! function_exists( 'x_legacy_widgets_init' ) ) :
  function x_legacy_widgets_init() {

    // Header
    // ------

    $i = 0;
    while ( $i < 4 ) : $i++;
      register_sidebar( array( // 2
        'name'          => __( 'Header ', '__x__' ) . $i,
        'id'            => 'header-' . $i,
        'description'   => __( 'Widgetized header area.', '__x__' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="h-widget">',
        'after_title'   => '</h4>',
      ) );
    endwhile;


    // Footer
    // ------

    $i = 0;
    while ( $i < 4 ) : $i++;
      register_sidebar( array( // 3
        'name'          => __( 'Footer ', '__x__' ) . $i,
        'id'            => 'footer-' . $i,
        'description'   => __( 'Widgetized footer area.', '__x__' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="h-widget">',
        'after_title'   => '</h4>',
      ) );
    endwhile;

  }
  add_action( 'widgets_init', 'x_legacy_widgets_init' );
endif;



// Responsive Styling
// =============================================================================


add_filter( 'cs_breakpoint_base', function() {
  return x_get_option('x_breakpoint_base');
});

add_filter( 'cs_breakpoint_ranges', function() {
  return x_get_option('x_breakpoint_ranges');
});

add_filter( 'cs_theme_option_preview_exclusions', function($exclusions) {
  $exclusions[] = 'x_breakpoint_base';
  $exclusions[] = 'x_breakpoint_ranges';
  return $exclusions;
});

// Extend term meta to allow accessing custom stack meta stored in wp_options

function x_dynamic_content_taxonomy_meta( $result, $field, $args = array() ) {

  if ( empty($result) && $field === 'meta' && isset($args['key']) ) {

    $term = cornerstone('DynamicContent')->get_contextual_term( $args );

    if ( is_a( $term, 'WP_Term') ) {
      $term_meta = x_get_taxonomy_meta( $term->term_id );

      if ( isset( $term_meta[$args['key']] )) {
        $result = $term_meta[$args['key']];
      }
    }

  }

  return $result;

}

add_filter('cs_dynamic_content_term', 'x_dynamic_content_taxonomy_meta', 20, 3 );
add_filter('cs_dynamic_content_archive', 'x_dynamic_content_taxonomy_meta', 20, 3 );