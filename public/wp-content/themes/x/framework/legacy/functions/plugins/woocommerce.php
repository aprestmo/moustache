<?php

// =============================================================================
// FUNCTIONS/GLOBAL/PLUGINS/WOOCOMMERCE.PHP
// -----------------------------------------------------------------------------
// Plugin setup for theme compatibility.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Global Setup
//   02. Styles
//   03. Shop
//   04. Product
//   05. Cart
//   06. Checkout
//   07. Single Product (Shortcode)
//   08. Cross Sells (Cart Only)
//   09. Related Products
//   10. Upsells
//   11. AJAX
//   12. Classic Navbar Cart
//   13. Fragments
//   14. Stack Wrapper
//   15. Template Includes
// =============================================================================

// Global Setup
// =============================================================================

// Remove Default Wrapper
// ----------------------

remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );


// Remove Page Title
// -----------------

function x_woocommerce_show_page_title() {
  return false;
}

add_filter( 'woocommerce_show_page_title', 'x_woocommerce_show_page_title' );



// Styles
// =============================================================================

function x_woocommerce_enqueue_styles( $stack, $ext ) {
  wp_deregister_style( 'woocommerce-layout' );
  wp_deregister_style( 'woocommerce-general' );
  wp_enqueue_style( 'x-woocommerce', X_TEMPLATE_URL . '/framework/dist/css/site/woocommerce/' . $stack . $ext . '.css', [ 'x-stack' ], X_ASSET_REV, 'all' );
}

add_action( 'x_enqueue_styles', 'x_woocommerce_enqueue_styles', 10, 2 );


// Shop
// =============================================================================

// Title
// -----

function x_woocommerce_template_loop_product_title() {
  echo '<h3><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h3>';
}

add_action( 'woocommerce_shop_loop_item_title', 'x_woocommerce_template_loop_product_title', 10 );
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );


// Get Shop Link
// -------------

function x_get_shop_link() {

  $id   = ( function_exists( 'wc_get_page_id' ) ) ? wc_get_page_id( 'shop' ) : woocommerce_get_page_id( 'shop' );
  $link = get_permalink( $id );

  return $link;

}


// Columns and Posts Per Page
// --------------------------

function x_woocommerce_shop_columns() {
  return x_get_option( 'x_woocommerce_shop_columns' );
}

add_filter( 'loop_shop_columns', 'x_woocommerce_shop_columns' );


function x_woocommerce_shop_posts_per_page() {
  return x_get_option( 'x_woocommerce_shop_count' );
}

add_filter( 'loop_shop_per_page', 'x_woocommerce_shop_posts_per_page' );


// Shop Product Thumbnails
// -----------------------

function x_woocommerce_shop_product_thumbnails() {

  GLOBAL $product;

  $id     = get_the_ID();
  $thumb  = 'woocommerce_thumbnail';
  $rating = ( function_exists( 'wc_get_rating_html' ) ) ? wc_get_rating_html( $product->get_average_rating() ) : $product->get_rating_html();

  woocommerce_show_product_sale_flash();

  echo '<div class="entry-featured">';
    echo '<a href="' . get_the_permalink() . '">';

      echo has_post_thumbnail() ? get_the_post_thumbnail( $id, $thumb ) : '<img src="' . x_woocommerce_shop_placeholder_thumbnail() . '" class="x-shop-placeholder-thumbnail">';

      if ( ! empty( $rating ) ) {
        echo '<div class="star-rating-container aggregate">' . $rating . '</div>';
      }

    echo '</a>';
  echo "</div>";

}

remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
add_action( 'woocommerce_before_shop_loop_item_title', 'x_woocommerce_shop_product_thumbnails', 10 );


function x_woocommerce_shop_placeholder_thumbnail() {

  $placeholder = x_get_option( 'x_woocommerce_shop_placeholder_thumbnail' );

  if ( empty( $placeholder ) && function_exists( 'cornerstone_make_placeholder_image_uri' ) ) {

    $sizes = wp_get_additional_image_sizes();
    $image_size = apply_filters( 'single_product_archive_thumbnail_size', 'woocommerce_thumbnail' );
    $size = isset($sizes[$image_size]) ? $sizes[$image_size] : array( 'height' => 300, 'width' => 300 );
    $placeholder = cornerstone_make_placeholder_image_uri( 'transparent', $size['height'], $size['width'] );

  }else{

    $image_url = cs_resolve_image_source( $placeholder, 'woocommerce_thumbnail');
    $placeholder = $image_url;

  }

  return $placeholder;

}


// Shop Item Wrapper
// -----------------

// remove the default opening and closing anchor tag added by woocommerce

remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

function x_woocommerce_before_shop_loop_item() {
  echo '<div class="entry-product">';
}

function x_woocommerce_after_shop_loop_item() {
  echo '</div>';
}

function x_woocommerce_before_shop_loop_item_title() {
  echo '<div class="entry-wrap"><header class="entry-header">';
}

function x_woocommerce_after_shop_loop_item_title() {
  woocommerce_template_loop_add_to_cart();
  echo '</header></div>';
}

add_action( 'woocommerce_before_shop_loop_item', 'x_woocommerce_before_shop_loop_item', 10 );
add_action( 'woocommerce_after_shop_loop_item', 'x_woocommerce_after_shop_loop_item', 10 );
add_action( 'woocommerce_before_shop_loop_item_title', 'x_woocommerce_before_shop_loop_item_title', 10 );
add_action( 'woocommerce_after_shop_loop_item_title', 'x_woocommerce_after_shop_loop_item_title', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );



// Product
// =============================================================================

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );


// Remove Sale Badge
// -----------------

remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );


// Product Wrapper
// ---------------

function x_woocommerce_before_single_product() {
  global $post;
  if ( empty($post->post_password) ) echo '<div class="entry-wrap"><div class="entry-content">';
}

function x_woocommerce_after_single_product() {
  global $post;
  if ( empty($post->post_password) ) echo '</div></div>';
}

add_action( 'woocommerce_before_single_product', 'x_woocommerce_before_single_product', 10 );
add_action( 'woocommerce_after_single_product', 'x_woocommerce_after_single_product', 10 );


// Add/Remove Product Tabs
// -----------------------

function x_woocommerce_add_remove_product_tabs( $tabs ) {

  if ( x_get_option( 'x_woocommerce_product_tab_description_enable' ) == '' ) {
    unset( $tabs['description'] );
  }

  if ( x_get_option( 'x_woocommerce_product_tab_additional_info_enable' ) == '' ) {
    unset( $tabs['additional_information'] );
  }

  if ( x_get_option( 'x_woocommerce_product_tab_reviews_enable' ) == '' ) {
    unset( $tabs['reviews'] );
  }

  return $tabs;

}

add_filter( 'woocommerce_product_tabs', 'x_woocommerce_add_remove_product_tabs', 98 );



// Cart
// =============================================================================

// Get Cart Link
// -------------

function x_get_cart_link() {

  if ( function_exists( 'wc_get_cart_url' ) ) {
    $link = wc_get_cart_url();
  } else {
    $link = WC()->cart->get_cart_url();
  }

  return $link;

}


// No Shipping Available HTML
// --------------------------

function x_woocommerce_cart_no_shipping_available_html( $message ) {

  if ( is_cart() ) {
    return '<div class="woocommerce-info x-alert x-alert-info x-alert-block"><p>' . $message . '</p></div>';
  } else {
    return '<p>' . $message . '</p>';
  }

}

add_filter( 'woocommerce_cart_no_shipping_available_html', 'x_woocommerce_cart_no_shipping_available_html' );



// Checkout
// =============================================================================

function x_woocommerce_add_submit_spinner() {
  echo '
    <div class="x-wc-loader">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
        <g fill="currentColor">
          <g class="nc-loop_circle-03-16" transform="rotate(31.62367660637084 8 8)">
            <path opacity="0.4" d="M8,16c-4.4111328,0-8-3.5888672-8-8s3.5888672-8,8-8s8,3.5888672,8,8S12.4111328,16,8,16z M8,2C4.6914062,2,2,4.6914062,2,8s2.6914062,6,6,6s6-2.6914062,6-6S11.3085938,2,8,2z"></path>
            <path d="M16,8h-2c0-3.3085938-2.6914062-6-6-6V0C12.4111328,0,16,3.5888672,16,8z"></path>
          </g>
          <script>!function(){function t(t){return.5>t?4*t*t*t:(t-1)*(2*t-2)*(2*t-2)+1}function i(t){this.element=t,this.animationId,this.start=null,this.init()}if(!window.requestAnimationFrame){var n=null;window.requestAnimationFrame=function(t,i){var e=(new Date).getTime();n||(n=e);var a=Math.max(0,16-(e-n)),o=window.setTimeout(function(){t(e+a)},a);return n=e+a,o}}i.prototype.init=function(){var t=this;this.animationId=window.requestAnimationFrame(t.triggerAnimation.bind(t))},i.prototype.reset=function(){var t=this;window.cancelAnimationFrame(t.animationId)},i.prototype.triggerAnimation=function(i){var n=this;this.start||(this.start=i);var e=i-this.start;900>e||(this.start=this.start+900),this.element.setAttribute("transform","rotate("+Math.min(900*t(e/900)/2.5,360)+" 8 8)");if(document.documentElement.contains(this.element))window.requestAnimationFrame(n.triggerAnimation.bind(n))};var e=document.getElementsByClassName("nc-loop_circle-03-16"),a=[];if(e)for(var o=0;e.length>o;o++)!function(t){a.push(new i(e[t]))}(o);document.addEventListener("visibilitychange",function(){"hidden"==document.visibilityState?a.forEach(function(t){t.reset()}):a.forEach(function(t){t.init()})})}();</script>
        </g>
      </svg>
    </div>
  ';
}

add_action( 'woocommerce_review_order_after_submit', 'x_woocommerce_add_submit_spinner' );



// Single Product (Shortcode)
// =============================================================================

function x_woocommerce_product_single_product_shortcode_columns( $atts ) {
  if ( ! absint( $atts['columns'] ) ) {
    $atts['columns'] = 1;
  }
  return $atts;
}

add_filter( 'shortcode_atts_product', 'x_woocommerce_product_single_product_shortcode_columns', 10, 1 );


/**
 * Hide Cart items quantity when they are sold inidividually
 */
add_filter( 'woocommerce_quantity_input_args', 'x_cart_hide_sold_individually', 20, 2 );
function x_cart_hide_sold_individually( $args, $product ) {
  // If product is sold individually
  if (
    !empty($product)
    && $product->get_sold_individually()
    && apply_filters('x_woocommerce_hide_sold_individually', true)
  ) {
    $args['classes'][] = 'quantity-hide';
  }

  return $args;
}


// Cross Sells (Cart Only)
// =============================================================================

function x_woocommerce_output_cross_sells() {
  if ( x_get_option( 'x_woocommerce_cart_cross_sells_enable' ) == '1' ) {
    $count   = x_get_option( 'x_woocommerce_cart_cross_sells_count' );
    $columns = x_get_option( 'x_woocommerce_cart_cross_sells_columns' );

    woocommerce_cross_sell_display( $count, $columns, 'rand' );
  }
}

remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
add_action( 'woocommerce_cart_collaterals', 'x_woocommerce_output_cross_sells', 21 );



// Related Products
// =============================================================================

function x_woocommerce_output_related_products() {
  if ( x_get_option( 'x_woocommerce_product_related_enable' ) == '1' ) {
    $count   = x_get_option( 'x_woocommerce_product_related_count' );
    $columns = x_get_option( 'x_woocommerce_product_related_columns' );

    $args = array(
      'posts_per_page' => $count,
      'columns'        => $columns,
      'orderby'        => 'rand'
    );

    woocommerce_related_products( $args, true, true );
  }
}

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
remove_action( 'woocommerce_after_single_product', 'woocommerce_output_related_products', 10 );
add_action( 'woocommerce_after_single_product_summary', 'x_woocommerce_output_related_products', 20 );



// Upsells
// =============================================================================

function x_woocommerce_output_upsells() {
  if ( x_get_option( 'x_woocommerce_product_upsells_enable' ) == '1' ) {
    $count   = x_get_option( 'x_woocommerce_product_upsell_count' );
    $columns = x_get_option( 'x_woocommerce_product_upsell_columns' );

    woocommerce_upsell_display( $count, $columns, 'rand' );
  }
}

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
remove_action( 'woocommerce_after_single_product', 'woocommerce_upsell_display', 10 );
add_action( 'woocommerce_after_single_product_summary', 'x_woocommerce_output_upsells', 21 );



// AJAX
// =============================================================================

if ( ! function_exists( 'x_woocommerce_navbar_cart_ajax_notification' ) ) :
  function x_woocommerce_navbar_cart_ajax_notification() {

    if ( get_option( 'woocommerce_enable_ajax_add_to_cart' ) == 'yes' ) {

      $fa_solid_enable = (bool) x_get_option( 'x_font_awesome_solid_enable' );
      $fa_regular_enable = (bool) x_get_option( 'x_font_awesome_regular_enable' );
      $fa_light_enable = (bool) x_get_option( 'x_font_awesome_light_enable' );

      if ( $fa_solid_enable || $fa_regular_enable || $fa_light_enable ){
        // light
        if ( $fa_light_enable ){
          $data_x_icon = 'data-x-icon-l';
        }

        // regular
        if ( $fa_regular_enable ){
          $data_x_icon = 'data-x-icon-o';
        }

        // solid
        if ( $fa_solid_enable ){
          $data_x_icon = 'data-x-icon-s';
        }
      }else{
        // default
        $data_x_icon = 'data-x-icon-l';
      }


      echo '<div class="x-cart-notification">'
                      . '<div class="x-cart-notification-icon loading">'
                        . '<i class="x-icon-cart-arrow-down" ' . $data_x_icon . '="&#xf218;" aria-hidden="true"></i>'
                      . '</div>'
                      . '<div class="x-cart-notification-icon added">'
                        . '<i class="x-icon-check" ' . $data_x_icon . '="&#xf00c;" aria-hidden="true"></i>'
                      . '</div>'
                    . '</div>';
    }

  }
  add_action( 'x_before_site_end', 'x_woocommerce_navbar_cart_ajax_notification' );
endif;



// Classic Navbar Cart
// =============================================================================

if ( ! function_exists( 'x_woocommerce_navbar_cart' ) ) :
  function x_woocommerce_navbar_cart() {

    $cart_info   = x_get_option( 'x_woocommerce_header_cart_info' );
    $cart_layout = x_get_option( 'x_woocommerce_header_cart_layout' );
    $cart_style  = x_get_option( 'x_woocommerce_header_cart_style' );
    $cart_outer  = x_get_option( 'x_woocommerce_header_cart_content_outer' );
    $cart_inner  = x_get_option( 'x_woocommerce_header_cart_content_inner' );

    $data = array(
      'icon'  => '<i class="x-icon-shopping-cart" data-x-icon-s="&#xf07a;" aria-hidden="true"></i>',
      'total' => WC()->cart->get_cart_total(),
      'count' => sprintf( _n( '%d Item', '%d Items', WC()->cart->cart_contents_count, '__x__' ), WC()->cart->cart_contents_count )
    );

    $modifiers = array(
      $cart_info,
      strpos( $cart_info, '-' ) === false ? 'inline' : $cart_layout,
      $cart_style
    );

    $cart_output = '<div class="x-cart ' . implode( ' ', $modifiers ) . '">';

      foreach ( explode( '-', $cart_info ) as $info ) {
        $key = ( $info == 'outer' ) ? $cart_outer : $cart_inner;
        $atts = $key === 'icon' ? '' : "data-x-wc-fragment=\"$key\" ";
        $cart_output .= '<span class="' . $info . '" ' . $atts .'>' . $data[$key] . '</span>';
      }

    $cart_output .= '</div>';

    return $cart_output;

  }
endif;

// Cart Fragment
// -------------

if ( ! function_exists( 'x_woocommerce_navbar_cart_fragment' ) ) :
  function x_woocommerce_navbar_cart_fragment( $fragments ) {

    if ( x_get_option( 'x_woocommerce_header_menu_enable' ) == '1' ) {

      $total = WC()->cart->get_cart_total();

      $fragments['span.inner[data-x-wc-fragment="total"]'] = '<span class="inner" data-x-wc-fragment="total">' . $total . '</span>';
      $fragments['span.outer[data-x-wc-fragment="total"]'] = '<span class="outer" data-x-wc-fragment="total">' . $total . '</span>';

      $count = sprintf( _n( '%d Item', '%d Items', WC()->cart->cart_contents_count, '__x__' ), WC()->cart->cart_contents_count );

      $fragments['span.inner[data-x-wc-fragment="count"]'] = '<span class="inner" data-x-wc-fragment="count">' . $count . '</span>';
      $fragments['span.outer[data-x-wc-fragment="count"]'] = '<span class="outer" data-x-wc-fragment="count">' . $count . '</span>';

    }

    return $fragments;

  }
  add_filter( 'woocommerce_add_to_cart_fragments', 'x_woocommerce_navbar_cart_fragment' );
endif;



// Cart Menu Item
// --------------

if ( ! function_exists( 'x_woocommerce_navbar_menu_item' ) ) :
  function x_woocommerce_navbar_menu_item( $items, $args ) {

    if ( $args->theme_location == 'primary' && x_get_option( 'x_woocommerce_header_menu_enable' ) == '1' && did_action( 'x_classic_headers' ) ) {

      if ( x_get_option( 'x_woocommerce_header_hide_empty_cart' ) != '1' || WC()->cart->get_cart_contents_count() > 0 ) {

          $items .= '<li class="menu-item current-menu-parent x-menu-item x-menu-item-woocommerce">'
                    . '<a href="' . x_get_cart_link() . '" class="x-btn-navbar-woocommerce">'
                      . x_woocommerce_navbar_cart()
                    . '</a>'
                  . '</li>';
      }

    }

    return $items;

  }
  add_filter( 'wp_nav_menu_items', 'x_woocommerce_navbar_menu_item', 9999, 2 );
endif;


// Stack Wrapper
// =============================================================================

function x_woocommerce_setup_stack_wrapper() {

  if ( apply_filters('x_woocommerce_add_wrapper', true ) ) {
    add_action( 'woocommerce_before_main_content', 'x_woocommerce_before_main_content', -999999 );
    add_action( 'woocommerce_after_main_content', 'x_woocommerce_after_main_content', 999999 );
    add_action( 'woocommerce_sidebar', 'x_woocommerce_sidebar', 999999 );
  }

}

add_action( 'template_redirect', 'x_woocommerce_setup_stack_wrapper' );



if ( ! function_exists( 'x_woocommerce_before_main_content' ) ) :

  function x_woocommerce_before_main_content ( ) {
    switch ( x_get_stack() ) {
      case 'icon':
        echo '<div class="x-main full" role="main">
        <div class="x-container max width offset-top offset-bottom">
        '.( x_is_shop() ? '
          <header class="entry-header shop">
            <h1 class="entry-title">'.x_get_option( 'x_icon_shop_title' ).'</h1>
          </header>
        ' : '');
      break;
      case 'ethos':

        $ethos_top_navigation = '';

        if ( x_is_product() ) {
          ob_start();
          x_ethos_entry_top_navigation();
          $ethos_top_navigation = ob_get_clean();
        }

        echo '<div class="x-container max width main">
        <div class="offset cf">
        <div class="'.x_main_content_class( false ).'" role="main">
        '.$ethos_top_navigation;

      break;
      default:
        echo '<div class="x-container max width offset">
        <div class="'.x_main_content_class( false ).'" role="main">';
        break;
    }
  }

endif;

if ( ! function_exists( 'x_woocommerce_after_main_content' ) ) :

  function x_woocommerce_after_main_content ( ) {

    switch ( x_get_stack() ) {
      case 'icon':
        echo '</div>
        </div>';
      break;
      default:
        echo '</div>';
      break;
    }

  }

endif;

if ( ! function_exists( 'x_woocommerce_sidebar' ) ) :

  function x_woocommerce_sidebar ( ) {
    switch ( x_get_stack() ) {
      case 'icon':
        echo '';
      break;
      case 'ethos':
        echo '</div>
        </div>';
      break;
      default:
        echo '</div>';
      break;
    }
  }

endif;



// Template Includes
// =============================================================================

function x_woocommerce_locate_template($template, $template_name, $template_path) {
  $theme_template = X_TEMPLATE_PATH . '/framework/legacy/templates/woocommerce/' . $template_name;
  if ( file_exists( $theme_template ) && strpos( $template, get_stylesheet_directory() ) === false) {
    return $theme_template;
  }
  return $template;
}

add_filter( 'woocommerce_locate_template', 'x_woocommerce_locate_template', 10, 3 );
