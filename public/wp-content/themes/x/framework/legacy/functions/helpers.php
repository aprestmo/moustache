<?php

// =============================================================================
// FUNCTIONS/GLOBAL/HELPERS.PHP
// -----------------------------------------------------------------------------
// Helper functions for various tasks.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Get Option
//   02. Get Stack
//   03. Get Site Layout
//   04. X Is Validated
//   05. Make Protocol Relative
//   06. Get Featured Image URL
//   07. Get Featured Image URL (With Social Fallback Image)
//   08. Return an Array of Integer Values from String
//   09. Get The ID
//   10. Get Taxonomy Meta
//   11. Get Slider Shortcode
//   12. Get Clean CSS
//   13. Generate HTML Attribute
//   14. Generate HTML Attributes
//   15. Generate Class Attribute
//   16. Generate Data Attribute JSON
//   17. Action Defer Helper
//   18. Action Defer Class
//   19. Prepare Post CSS Value
//   20. Round Nearest
//   21. Get Current Admin Color Scheme
//   22. i18n helper
//   23. Coerce Value
//   24. Deprecated
// =============================================================================

// Get Option
// =============================================================================

function x_get_option( $option, $default = false ) {

  if ( $default === false ) {
    $default = x_theme_options_get_default( $option );
  }

  $output = get_option( $option, $default );

  return apply_filters( 'x_option_' . $option, $output );

}



// Get Stack
// =============================================================================

function x_get_stack() {
  return x_get_option( 'x_stack' );
}



// Get Site Layout
// =============================================================================

function x_get_site_layout() {
  return x_get_option( 'x_layout_site' );
}



// X Is Validated
// =============================================================================

function x_is_validated() {
  return get_option( 'x_product_validation_key' ) != false;
}



// Make Protocol Relative
// =============================================================================
// Accepts a string and replaces any instances of "http://" and "https://" with
// the protocol relative "//" instead.

function x_make_protocol_relative( $url ) {

  if ( function_exists('cs_apply_image_atts' ) ) {
    $img = cs_apply_image_atts( array( 'src' => $url ) );
    if ( isset( $img ) && $img['src'] ) {
      $url = $img['src'];
    }
  }

  return str_replace( 'https:', '', set_url_scheme( $url, 'https' ) );
}



// Get Featured Image URL
// =============================================================================

if ( ! function_exists( 'x_get_featured_image_url' ) ) :
  function x_get_featured_image_url( $size = 'full' ) {

    $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(), $size );
    return ( is_array($featured_image) && count($featured_image) > 0 ) ? $featured_image[0] : '';

  }
endif;



// Get Featured Image URL (With Social Fallback Image)
// =============================================================================

if ( ! function_exists( 'x_get_featured_image_with_fallback_url' ) ) :
  function x_get_featured_image_with_fallback_url( $size = 'full' ) {

    $featured_image_url = x_get_featured_image_url( $size );

    if (!$featured_image_url) {
      // Get attachment
      $img = get_option( 'x_social_fallback_image' );

      // None
      if (empty($img)) {
        return "";
      }

      // URL
      if (preg_match("/https?:/", $img)) {
        return $img;
      }

      // Get attachment full URL
      $parts = explode(':', $img);
      $attachment_id = intval($parts[0]);

      $featured_image = wp_get_attachment_image_src( $attachment_id, $size );

      // Return fallback raw or string from featured image
      return empty($featured_image)
        ? $img
        : $featured_image[0];
    }

    return $featured_image_url;

  }
endif;



// Get The ID
// =============================================================================
// Gets the ID of the current page, post, et cetera. Can be used outside of the
// loop and also returns the ID for blog and shop index pages.

function x_get_the_ID() {

  GLOBAL $post;

  if ( is_home() ) {
    $id = get_option( 'page_for_posts' );
  } elseif ( x_is_shop() ) {
    $id = ( function_exists( 'wc_get_page_id' ) ) ? wc_get_page_id( 'shop' ) : woocommerce_get_page_id( 'shop' );
  } elseif ( is_a( $post, 'WP_Post') ) {
    $id = $post->ID;
  } else {
    $id = NULL;
  }

  return $id;

}



// Get Taxonomy Meta
// =============================================================================

function x_get_taxonomy_meta( $id = false ) {

  if ( !$id ) {
    $object = get_queried_object();
    $id     = $object->term_id;
  }

  $meta   = get_option( 'taxonomy_' . $id );
  return wp_parse_args( $meta, array(
    'archive-title' => '',
    'archive-subtitle' => '',
    'accent' => '#ffffff'
  ) );

}



// Get Slider Shortcode
// =============================================================================
// Accepts an identifier string to determine which shortcode should be output.
// These strings are generated by default in the slider meta options and look
// something like "x-slider-ls-2", which explains that this is a slider from
// the LayerSlider plugin with an ID of 2. If a string not beginning with
// "x-slider" is input, it is assumed to be a slug for Revolution Slider and
// is output using that shortcode.

function x_get_slider_shortcode( $string ) {

  //
  // Conditionals.
  //

  $is_new_slug             = strpos( $string, 'x-slider-' ) !== false;
  $is_new_layerslider_slug = strpos( $string, 'x-slider-ls-' ) !== false;


  //
  // Get shortcode.
  //

  $shortcode = ( $is_new_layerslider_slug ) ? 'layerslider' : 'rev_slider';


  //
  // Get shortcode parameter.
  //

  $parameter = ( $is_new_layerslider_slug ) ? 'id' : 'alias';


  //
  // Get shortcode parameter value.
  //

  if ( $is_new_slug ) {
    $string_pieces   = explode( '-', $string );
    $slider_id       = end( $string_pieces );
    $parameter_value = $slider_id;
  } else {
    $parameter_value = $string;
  }


  //
  // Return shortcode format.
  //

  return "[{$shortcode} {$parameter}=\"{$parameter_value}\"]";

}



// Get Clean CSS
// =============================================================================
// 1. Remove comments.
// 2. Remove duplicate linebreaks
// 3. Remove whitespace.
// 4. Remove starting whitespace.

function x_get_clean_css( $css ) {

  $css = preg_replace( '#/\*.*?\*/#s', '', $css );         // 1
  $css = preg_replace( '/\s+/', ' ', $css );               // 2
  $css = preg_replace( '/\s*([{}|:;,])\s+/', '$1', $css ); // 2
  $css = preg_replace( '/\s\s+(.*)/', '$1', $css );        // 3
  return $css;

}



// Generate HTML Attributes
// =============================================================================
// 01. Merge all incoming arguments together
// 02. Treat strings as JSON and decode before merging
// 03. Additional consideration when merging the class attribute
// 04. Combine the output into a string
// 05. Values that are explicitly set to false will be omitted
// 06. When attribute is null, treat as an attribute without a value [ 'data-thing' => null ] becomes "data-thing"
// 07. Create attribute name/value pair

function x_ensure_class_array( $input ) {
  if ( is_string($input ) ) {
    return array_filter( explode(' ', $input ) );
  }

  return array_reduce($input,function($acc,$next) {
    $items = x_ensure_class_array( $next );
    foreach( $items as $item ) {
      if ( is_array($item ) ) {
        $acc = array_merge( $acc, x_ensure_class_array( $input ) ) ;
      } else {
        $acc[] = $item;
      }
    }
    return $acc;
  }, []);
}

function x_atts() {

  $args = func_get_args();
  $merged = array();

  foreach($args as $set) { // 01
    if (is_string($set)) {
      $set = json_decode( $set, true ); // 02
    }
    if (!is_array($set)) {
      continue;
    }
    if (isset($merged['class']) && isset($set['class']) ) { // 03
      $set['class'] = array_unique(array_merge( x_ensure_class_array( $merged['class']), x_ensure_class_array( $set['class'])));
    }
    $merged = array_merge( $merged, $set );
  }


  $results = [];

  foreach ( $merged as $attr => $value ) { // 04

    if ($value === false) { // 05
      continue;
    }

    if ( is_null( $value ) ) { // 06
      $results[] = esc_attr($attr);
    } else {
      $results[] = esc_attr($attr) . '="' . esc_attr( is_array( $value ) ? implode( ' ', array_filter( $value ) ) : $value ) . '"'; // 07
    }

  }

  return implode(' ', $results);

}







// Prepare Post CSS Value
// =============================================================================

function x_post_css_value( $value, $designation ) {
  return ( function_exists('cornerstone_post_process_color') ) ? "%%post $designation%%$value%%/post%%" : $value;
}

function x_post_process_color( $value ) {
  return ( function_exists('cornerstone_post_process_color') ) ? cornerstone_post_process_color( $value ) : $value;
}



// Round Nearest
// =============================================================================

function x_round_nearest( $num, $to_nearest ) {
  return floor( $num / $to_nearest ) * $to_nearest;
}


function x_targeted_link_rel( $rel = '', $is_target_blank = true ) { // 01

  if ( $is_target_blank && apply_filters( 'tco_targeted_link_rel', ! is_ssl() ) ) {

		$more = apply_filters( 'tco_targeted_link_rel', array( 'noopener', 'noreferrer' ) );

		foreach ($more as $str ) {
			if ( false === strpos($rel, $str ) ) {
				$rel .= " $str";
			}
		}

	}

  return ltrim($rel);

}


function x_output_target_blank($echo = true) {
	$output = 'target="_blank" rel="' . x_targeted_link_rel() .'"';
	if ($echo) {
		echo $output;
	}
	return $output;
}



// Coerce Value
// =============================================================================

function x_coerce_value( $input, $unit ) {
  if ( strpos( $input, $unit ) === false ) {
    return $input . $unit;
  } else {
    return $input;
  }
}



// Embed data cache handling
// =============================================================================

function x_get_embed_cache ( $url, $attr, $post_id, $flush = false, $cache_key_suffix = '_x_cs' ) {

  global $wp_embed;

  $unknown = array( 'provider_name' => 'unknown_provider', 'type' => 'unknown' );

  $wp_oembed = _wp_oembed_get_object();

  $key_suffix    = md5( $url . serialize( $attr ) . $cache_key_suffix );

  $cachekey      = '_oembed_' . $key_suffix;
  $cachekey_time = '_oembed_time_' . $key_suffix;
  $cachekey_info = '_oembed_info_' . $key_suffix;

  $ttl = apply_filters( 'oembed_ttl', DAY_IN_SECONDS, $url, $attr, $post_id );

  $cache      = '';
  $cache_time = 0 ;

  $cached_post_id = $wp_embed->find_oembed_post_id( $key_suffix );

  if ( $post_id ) {

      $cache      = get_post_meta( $post_id, $cachekey, true );
      $cache_time = get_post_meta( $post_id, $cachekey_time, true );
      $cache_info = get_post_meta( $post_id, $cachekey_info, true );

      if ( ! $cache_time ) {
          $cache_time = 0;
      }

  } elseif ( $cached_post_id ) {

      $cached_post = get_post( $cached_post_id );
      $cache      = $cached_post->post_content;
      $cache_info = get_post_meta( $cached_post_id, $cachekey_info, true );
      $cache_time = strtotime( $cached_post->post_modified_gmt );

  }


  $cached_recently = ( time() - $cache_time ) < $ttl;

  if ( !$flush && ( $wp_embed->usecache || $cached_recently ) ) {
    // Failures are cached. Serve one if we're using the cache.
    if ( '{{unknown}}' === $cache ) {
        return array( 'html' => $wp_embed->maybe_make_link( $url  ), 'info' => $unknown );
    }

    if ( ! empty( $cache ) ) {
        return array( 'html' => $cache, 'info' => $cache_info ); //Return if cached
    }
  }

  $html = $wp_oembed->get_html( $url, $attr );
  $info = $wp_oembed->get_data( $url, $attr );

  if ( $post_id ) {
      if ( $html ) {
          update_post_meta( $post_id, $cachekey, $html );
          update_post_meta( $post_id, $cachekey_time, time() );
          update_post_meta( $post_id, $cachekey_info, $info );
      } elseif ( ! $cache ) {
          update_post_meta( $post_id, $cachekey, '{{unknown}}' );
      }
  } else {
    $has_kses = false !== has_filter( 'content_save_pre', 'wp_filter_post_kses' );

    if ( $has_kses ) {
        // Prevent KSES from corrupting JSON in post_content.
        kses_remove_filters();
    }

    $insert_post_args = array(
        'post_name'   => $key_suffix,
        'post_status' => 'publish',
        'post_type'   => 'oembed_cache',
    );

    if ( $html ) {
        if ( $cached_post_id ) {
            wp_update_post(
                wp_slash(
                    array(
                        'ID'           => $cached_post_id,
                        'post_content' => $html,
                    )
                )
            );
            update_post_meta( $cached_post_id, $cachekey_info, $info );
        } else {
            $new_id = wp_insert_post(
                wp_slash(
                    array_merge(
                        $insert_post_args,
                        array(
                            'post_content' => $html,
                        )
                    )
                )
            );

            update_post_meta( $new_id, $cachekey_info, $info );
        }
    } elseif ( ! $cache ) {
        $new_id = wp_insert_post(
            wp_slash(
                array_merge(
                    $insert_post_args,
                    array(
                        'post_content' => '{{unknown}}',
                    )
                )
            )
        );
        update_post_meta( $new_id, $cachekey_info, $unknown );
    }

    if ( $has_kses ) {
        kses_init_filters();
    }
  }

  // If there was a result, return it.
  if ( $html ) {
    return array( 'html' => $html, 'info' => $info ); //Return if cached
  }

  // Still unknown.
  return array( 'html' => $wp_embed->maybe_make_link( $url  ), 'info' => $unknown );

}



// Deprecated
// =============================================================================

if ( ! function_exists( 'x_header_widget_areas_count' ) ) :
  function x_header_widget_areas_count() {
    return x_get_option( 'x_header_widget_areas' );
  }
endif;

if ( ! function_exists( 'x_footer_widget_areas_count' ) ) :
  function x_footer_widget_areas_count() {
    return x_get_option( 'x_footer_widget_areas' );
  }
endif;
