<?php

// Versions
// =============================================================================

if ( ! defined( 'X_VERSION' ) ) {
  define( 'X_VERSION', '10.1.9' );
}

if ( ! defined( 'X_CS_VERSION' ) ) {
  define( 'X_CS_VERSION', '7.1.9' );
}


// Theme Constants
// =============================================================================

define( 'X_SLUG', 'x' );
define( 'X_TITLE', 'X' );
define( 'X_I18N_PATH', X_TEMPLATE_PATH . '/framework/functions/x/i18n');


// Legacy Constants - these are no longer utilized in the codebase, but may still be referenced by child themes

define( 'X_LAYERSLIDER_IS_ACTIVE', class_exists( 'LS_Sliders' ) );
define( 'X_REVOLUTION_SLIDER_IS_ACTIVE', class_exists( 'RevSlider' ) );
define( 'X_WOOCOMMERCE_IS_ACTIVE', class_exists( 'WC_API' ) );

// Require Cornerstone
// =============================================================================

function x_require_cornerstone( $tgmpa ) {

  $tgmpa->register( array(
    'name'        => 'Cornerstone',
    'slug'        => 'cornerstone',
    'source'      => X_TEMPLATE_PATH . '/framework/cornerstone.zip',
    'required'    => true,
    'version'     => X_CS_VERSION,
    'is_callable' => 'CS'
  ) );

}

add_action( 'x_tgmpa_register', 'x_require_cornerstone' );

// add_filter( 'cornerstone_default_app_slug', function() {
//   return 'x';
// });


// Environment Setup
// =============================================================================

add_filter( '_cornerstone_app_env', function($env) {
  $env['product'] = 'x';
  $env['title'] = X_TITLE;
  $env['version'] = X_VERSION;
  $env['productKey'] = esc_attr( get_option( 'x_product_validation_key', '' ) );
  $env['siteBuilder'] = false;
  return $env;
} );

add_action( 'after_setup_theme', function() {

  if ( ! function_exists( 'tco_common' ) ) {
    require_once( X_TEMPLATE_PATH . '/framework/functions/x/tco.php' );
  }

  tco_common()->init( array(
    'url' => X_TEMPLATE_URL . '/framework/dist/tco'
  ) );

}, 0);




// Version Body Class
// =============================================================================

if ( ! function_exists( 'x_body_class_version' ) ) :
  function x_body_class_version( $output ) {

    $output[] = 'x-v' . str_replace( '.', '_', X_VERSION );
    return $output;

  }
  add_filter( 'body_class', 'x_body_class_version', 10000 );
endif;



// Overview Page Modules
// =============================================================================

add_action( 'x_overview_init', 'x_validation_modules' );

function x_validation_modules() {

  require_once( X_TEMPLATE_PATH . '/framework/functions/x/validation/class-validation-cornerstone.php' );

  X_Validation_Cornerstone::instance();

}

function x_load_preinit() {
  require_once X_TEMPLATE_PATH . '/framework/functions/x/migration.php';
}

add_action('x_boot_preinit', 'x_load_preinit' );


add_filter( 'x_boot_legacy_includes', function() {
  return include X_TEMPLATE_PATH . '/framework/legacy/includes.php';
} );


// Admin Menu
// =============================================================================

//
// Style admin menu items (Dashboard only)
//

function x_get_admin_menu_css() {
  ob_start(); ?>

  #adminmenu .toplevel_page_x-addons-home .wp-menu-image img {
    width: 20px;
    height: auto;
    padding: 7px 0 0 0;
  }

  <?php return ob_get_clean();
}

add_filter( 'x_admin_menu_css', 'x_get_admin_menu_css' );



function x_admin_menu_logo() {

  // this is already encoded because envato doesn't allow base64_encode
  return "data:image/svg+xml;base64,ICAgIDxzdmcgZmlsbD0iI2E3YWFhZCIgdmlld0JveD0iMCAwIDQwMCA0MDAiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICAgIDxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKC02MDAuMDAwMDAwLCAwLjAwMDAwMCkiPgogICAgICAgIDxwYXRoIGQ9Ik04MDAsMCBDOTEwLjQ1Njk1LDAgMTAwMCw4OS41NDMwNSAxMDAwLDIwMCBDMTAwMCwzMTAuNDU2OTUgOTEwLjQ1Njk1LDQwMCA4MDAsNDAwIEM2ODkuNTQzMDUsNDAwIDYwMCwzMTAuNDU2OTUgNjAwLDIwMCBDNjAwLDg5LjU0MzA1IDY4OS41NDMwNSwwIDgwMCwwIFogTTgwMCwzMiBDNzA3LjIxNjE2MiwzMiA2MzIsMTA3LjIxNjE2MiA2MzIsMjAwIEM2MzIsMjkyLjc4MzgzOCA3MDcuMjE2MTYyLDM2OCA4MDAsMzY4IEM4OTIuNzgzODM4LDM2OCA5NjgsMjkyLjc4MzgzOCA5NjgsMjAwIEM5NjgsMTA3LjIxNjE2MiA4OTIuNzgzODM4LDMyIDgwMCwzMiBaIE04NzAuNTc0ODQxLDEyOS4xNjczNTggTDg3MC42MDcwMDMsMTI5LjE5OTUxOSBMODcwLjYwNzAwMywxMjkuMTk5NTE5IEM4NzcuMTkzOTI0LDEzNS44MDQwNCA4NzcuMTk0MTA1LDE0Ni40OTMzNzMgODcwLjYwNzQwOSwxNTMuMDk4MTE4IEw4MjMuODY2MDgyLDE5OS45NjcwODIgTDg3MC44MDAwNzUsMjQ2Ljc3MjkwMiBDODc3LjM4MTc5MiwyNTMuMzM2NjMzIDg3Ny4zOTYzNzMsMjYzLjk5MzEyNCA4NzAuODMyNjQyLDI3MC41NzQ4NDEgTDg3MC44MDA0ODEsMjcwLjYwNzAwMyBMODcwLjgwMDQ4MSwyNzAuNjA3MDAzIEM4NjQuMTk1OTYsMjc3LjE5MzkyNCA4NTMuNTA2NjI3LDI3Ny4xOTQxMDUgODQ2LjkwMTg4MiwyNzAuNjA3NDA5IEw4MDAuMDMyMDgyLDIyMy44NjYwODIgTDc1My4yMjcwOTgsMjcwLjgwMDA3NSBDNzQ2LjY2MzM2NywyNzcuMzgxNzkyIDczNi4wMDY4NzYsMjc3LjM5NjM3MyA3MjkuNDI1MTU5LDI3MC44MzI2NDIgQzcyOS40MTQ0MjQsMjcwLjgyMTkzNiA3MjkuNDAzNzAzLDI3MC44MTEyMTYgNzI5LjM5Mjk5NywyNzAuODAwNDgxIEM3MjIuODA2MDc2LDI2NC4xOTU5NiA3MjIuODA1ODk1LDI1My41MDY2MjcgNzI5LjM5MjU5MSwyNDYuOTAxODgyIEw3NzYuMTMzMDgyLDIwMC4wMzIwODIgTDcyOS4xOTk5MjUsMTUzLjIyNzA5OCBDNzIyLjYxODIwOCwxNDYuNjYzMzY3IDcyMi42MDM2MjcsMTM2LjAwNjg3NiA3MjkuMTY3MzU4LDEyOS40MjUxNTkgQzcyOS4xNzgwNjQsMTI5LjQxNDQyNCA3MjkuMTg4Nzg0LDEyOS40MDM3MDMgNzI5LjE5OTUxOSwxMjkuMzkyOTk3IEM3MzUuODA0MDQsMTIyLjgwNjA3NiA3NDYuNDkzMzczLDEyMi44MDU4OTUgNzUzLjA5ODExOCwxMjkuMzkyNTkxIEw3OTkuOTY3MDgyLDE3Ni4xMzMwODIgTDg0Ni43NzI5MDIsMTI5LjE5OTkyNSBDODUzLjMzNjYzMywxMjIuNjE4MjA4IDg2My45OTMxMjQsMTIyLjYwMzYyNyA4NzAuNTc0ODQxLDEyOS4xNjczNTggWiIgaWQ9IlgtKE91dGxpbmVkKSI+PC9wYXRoPgogICAgICA8L2c+CiAgICA8L3N2Zz4KICA=";



  // $fill = '#a7aaad';

  // ob_start();
  // ?>
  //   <svg fill="<?php echo $fill; ?>" viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
  //     <g transform="translate(-600.000000, 0.000000)">
  //       <path d="M800,0 C910.45695,0 1000,89.54305 1000,200 C1000,310.45695 910.45695,400 800,400 C689.54305,400 600,310.45695 600,200 C600,89.54305 689.54305,0 800,0 Z M800,32 C707.216162,32 632,107.216162 632,200 C632,292.783838 707.216162,368 800,368 C892.783838,368 968,292.783838 968,200 C968,107.216162 892.783838,32 800,32 Z M870.574841,129.167358 L870.607003,129.199519 L870.607003,129.199519 C877.193924,135.80404 877.194105,146.493373 870.607409,153.098118 L823.866082,199.967082 L870.800075,246.772902 C877.381792,253.336633 877.396373,263.993124 870.832642,270.574841 L870.800481,270.607003 L870.800481,270.607003 C864.19596,277.193924 853.506627,277.194105 846.901882,270.607409 L800.032082,223.866082 L753.227098,270.800075 C746.663367,277.381792 736.006876,277.396373 729.425159,270.832642 C729.414424,270.821936 729.403703,270.811216 729.392997,270.800481 C722.806076,264.19596 722.805895,253.506627 729.392591,246.901882 L776.133082,200.032082 L729.199925,153.227098 C722.618208,146.663367 722.603627,136.006876 729.167358,129.425159 C729.178064,129.414424 729.188784,129.403703 729.199519,129.392997 C735.80404,122.806076 746.493373,122.805895 753.098118,129.392591 L799.967082,176.133082 L846.772902,129.199925 C853.336633,122.618208 863.993124,122.603627 870.574841,129.167358 Z" id="X-(Outlined)"></path>
  //     </g>
  //   </svg>
  // <?php

  // return ob_get_clean();
}

function x_admin_menu() {
  $title = __( 'Validation', '__x__' );
  add_menu_page( $title, X_TITLE, 'manage_options', 'x-addons-home', 'x_addons_page_home', 'data:image/svg+xml;base64,' . x_admin_menu_logo(), 3 );
  add_submenu_page( 'x-addons-home', $title, $title, 'manage_options', 'x-addons-home', 'x_addons_page_home' );
}

add_action( 'admin_menu', 'x_admin_menu', 5 );


add_theme_support( 'cornerstone' );
add_theme_support( 'cornerstone-managed', [ 'handle' => 'x-theme' ] );
