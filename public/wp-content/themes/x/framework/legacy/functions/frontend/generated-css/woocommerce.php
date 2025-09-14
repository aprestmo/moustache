<?php

// =============================================================================
// FUNCTIONS/GLOBAL/ADMIN/CUSTOMIZER/OUTPUT/WOOCOMMERCE.PHP
// -----------------------------------------------------------------------------
// Global CSS output for WooCommerce.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. AJAX Add to Cart
//   02. Product Reviews
//   03. WooCommerce Widget Images
// =============================================================================

?>

<?php if ( class_exists( 'WC_API' ) ) : ?>

  /* AJAX Add to Cart
  // ========================================================================== */

  <?php if ( get_option( 'woocommerce_enable_ajax_add_to_cart' ) == 'yes' ) : ?>

    .x-cart-notification-icon.loading {
      color: <?php echo x_post_css_value( x_get_option( 'x_woocommerce_ajax_add_to_cart_color' ), 'color' ); ?>;
    }

    .x-cart-notification:before {
      background-color: <?php echo x_post_css_value( x_get_option( 'x_woocommerce_ajax_add_to_cart_bg_color' ), 'color' ); ?>;
    }

    .x-cart-notification-icon.added {
      color: <?php echo x_post_css_value( x_get_option( 'x_woocommerce_ajax_add_to_cart_color_hover' ), 'color' ); ?>;
    }

    .x-cart-notification.added:before {
      background-color: <?php echo x_post_css_value( x_get_option( 'x_woocommerce_ajax_add_to_cart_bg_color_hover' ), 'color' ); ?>;
    }

  <?php endif; ?>



  /* Account Navigation
  // ========================================================================== */

  .woocommerce-MyAccount-navigation-link a {
    color: <?php echo $x_body_font_color; ?>;
  }

  .woocommerce-MyAccount-navigation-link a:hover,
  .woocommerce-MyAccount-navigation-link.is-active a {
    color: <?php echo $x_headings_font_color; ?>;
  }



  /* Cart
  // ========================================================================== */

  .cart_item .product-remove a {
    color: <?php echo $x_body_font_color; ?>;
  }

  .cart_item .product-remove a:hover {
    color: <?php echo $x_headings_font_color; ?>;
  }

  .cart_item .product-name a {
    color: <?php echo $x_headings_font_color; ?>;
  }

  .cart_item .product-name a:hover {
    color: <?php echo $x_site_link_color; ?>;
  }



  /* Product Reviews
  // ========================================================================== */

  .woocommerce p.stars span a {
    background-color: <?php echo $x_site_link_color; ?>;
  }



  /* WooCommerce Widget Images
  // ========================================================================== */

  <?php if ( x_get_option( 'x_woocommerce_widgets_image_alignment' ) == 'right' ) : ?>

    .widget_best_sellers ul li a img,
    .widget_shopping_cart ul li a img,
    .widget_products ul li a img,
    .widget_featured_products ul li a img,
    .widget_onsale ul li a img,
    .widget_random_products ul li a img,
    .widget_recently_viewed_products ul li a img,
    .widget_recent_products ul li a img,
    .widget_recent_reviews ul li a img,
    .widget_top_rated_products ul li a img {
      float: right;
      margin-left: 0.65em;
      margin-right: 0;
    }

  <?php endif; ?>

<?php endif; ?>
