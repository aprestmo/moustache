<?php

// =============================================================================
// TCO.PHP
// -----------------------------------------------------------------------------
// Code commonly used across Themeco products.
// NOTE: When editing this file, only update the version in cornerstone/tco.php
// =============================================================================

namespace Themeco\Common {

  if ( ! function_exists('tco_common') ) :

  class Shared {

    // Boilerplate
    // -----------

    private static $instance;
    protected $url = '';
    protected $version = '1.0.0';
    protected $updates;

    public function __construct() {
      add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), -999 );
      if (defined('CS_VERSION')) {
        $this->version = CS_VERSION;
      } elseif (defined('X_VERSION')) {
        $this->version = X_VERSION;
      }
    }

    public function init( $options ) {
      if ( isset( $options['url'] ) ) {
        $this->url = trailingslashit( $options['url'] );
      }
      if ( isset( $options['version'] ) ) {
        $this->version = $options['version'];
      }
    }

    public static function instance() {
      if ( ! isset( self::$instance ) ) {
        self::$instance = new self( __FILE__ );
      }
      return self::$instance;
    }



    // Script & Style Registration
    // ---------------------------

    public function admin_enqueue_scripts() {
      wp_enqueue_style( $this->handle( 'admin-css' ), $this->url( 'tco.css' ), array(), $this->version );
    }


    public function handle( $handle = 'admin-js' ) {
      return 'tco-common-' . $handle . '-' . str_replace( '.', '-', $this->version );
    }

    public function url( $more = '' ) {
      return $this->url . $more;
    }

    public function validator( $code, $product ) {
      return new Validator( $this, $code, $product );
    }

    public function updates() {
      if ( !isset( $this->updates ) ) {
        $this->updates = new Updates($this);
      }
      return $this->updates;
    }

    public function get_admin_image( $image ) {
      $image = $this->url( 'img/' . $image );
      return $image;
    }

    public function admin_image( $image ) {
      echo $this->get_admin_image( $image );
    }

    public function get_admin_icon( $icon, $class = '', $style = '' ) {
      $href   = $this->url( 'img/icons.svg#' . $icon );
      $class  = ( $class == '' ) ? '' : ' class="' . $class . '"';
      $style  = ( $style == '' ) ? '' : ' style="' . $style . '"';
      $output = '<svg' . $class . $style . '><use xlink:href="' . $href . '"></use></svg>';
      return $output;
    }

    public function admin_icon( $icon, $class = '', $style = '' ) {
      echo $this->get_admin_icon( $icon, $class, $style );
    }

    public function get_themeco_svg( $class = '', $style = '' ) {

      $class  = ( $class == '' ) ? '' : ' class="' . $class . '"';
      $style  = ( $style == '' ) ? '' : ' style="' . $style . '"';

      $logo = '<svg' . $class . $style . ' version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 4320 504" style="enable-background:new 0 0 4320 504;" xml:space="preserve">
                  <polygon points="198,0 0,0 0,108 198,108 198,504 306,504 306,108 504,108 504,0 306,0     "/>
                  <polygon points="1008,198 720,198 720,0 612,0 612,198 612,306 612,504 720,504 720,306 1008,306 1008,504 1116,504 1116,306 1116,198 1116,0 1008,0    "/>
                  <rect x="1224" width="504" height="108"/>
                  <rect x="1224" y="198" width="504" height="108"/>
                  <rect x="1224" y="396" width="504" height="108"/>
                  <polygon points="2214,0 2106,0 1944,0 1836,0 1836,108 1836,504 1944,504 1944,108 2106,108 2106,504 2214,504 2214,108 2376,108 2376,504 2484,504 2484,108 2484,0 2376,0    "/>
                  <rect x="2592" width="504" height="108"/>
                  <rect x="2592" y="198" width="504" height="108"/>
                  <rect x="2592" y="396" width="288" height="108"/>
                  <rect x="2988" y="396" width="108" height="108"/>
                  <polygon points="3204,0 3204,108 3204,396 3204,504 3312,504 3708,504 3708,396 3312,396 3312,108 3708,108 3708,0 3312,0     "/>
                  <path d="M4212,0h-288h-108v108v288v108h108h288h108V396V108V0H4212z M4212,396h-288V108h288V396z"/>
                </svg>';

      return $logo;

    }

    public function themeco_svg( $class = '', $style = '' ) {
      echo $this->get_themeco_svg( $class, $style );
    }

    public function get_x_svg( $class = '', $style = '' ) {

      $class  = ( $class == '' ) ? '' : ' class="' . $class . '"';
      $style  = ( $style == '' ) ? '' : ' style="' . $style . '"';

      $logo = '<svg' . $class . $style . ' viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                  <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <g transform="translate(-600.000000, 0.000000)" fill="currentColor">
                      <path d="M800,0 C910.45695,0 1000,89.54305 1000,200 C1000,310.45695 910.45695,400 800,400 C689.54305,400 600,310.45695 600,200 C600,89.54305 689.54305,0 800,0 Z M800,32 C707.216162,32 632,107.216162 632,200 C632,292.783838 707.216162,368 800,368 C892.783838,368 968,292.783838 968,200 C968,107.216162 892.783838,32 800,32 Z M870.574841,129.167358 L870.607003,129.199519 L870.607003,129.199519 C877.193924,135.80404 877.194105,146.493373 870.607409,153.098118 L823.866082,199.967082 L870.800075,246.772902 C877.381792,253.336633 877.396373,263.993124 870.832642,270.574841 L870.800481,270.607003 L870.800481,270.607003 C864.19596,277.193924 853.506627,277.194105 846.901882,270.607409 L800.032082,223.866082 L753.227098,270.800075 C746.663367,277.381792 736.006876,277.396373 729.425159,270.832642 C729.414424,270.821936 729.403703,270.811216 729.392997,270.800481 C722.806076,264.19596 722.805895,253.506627 729.392591,246.901882 L776.133082,200.032082 L729.199925,153.227098 C722.618208,146.663367 722.603627,136.006876 729.167358,129.425159 C729.178064,129.414424 729.188784,129.403703 729.199519,129.392997 C735.80404,122.806076 746.493373,122.805895 753.098118,129.392591 L799.967082,176.133082 L846.772902,129.199925 C853.336633,122.618208 863.993124,122.603627 870.574841,129.167358 Z" id="X-(Outlined)"></path>
                    </g>
                  </g>
                </svg>';

      return $logo;

    }

    public function get_pro_svg( $class = '', $style = '' ) {

      $class  = ( $class == '' ) ? '' : ' class="' . $class . '"';
      $style  = ( $style == '' ) ? 'fill: currentColor;' : $style;

      $logo = '<svg' . $class . ' style="' . $style . '" viewBox="0 0 400 450" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
          <g transform="translate(-10.000000, -600.000000)" fill="currentColor">
            <path d="M230.016931,605.368943 L308.738808,650.870363 L390.016931,697.849298 C402.383432,704.997163 410,718.196895 410,732.480534 L410,917.519466 C410,931.803105 402.383432,945.002837 390.016931,952.150702 L230.016931,1044.63106 C217.632285,1051.78941 202.367715,1051.78941 189.983069,1044.63106 L29.983069,952.150702 C17.616568,945.002837 10,931.803105 10,917.519466 L10,732.480534 C10,718.196895 17.616568,704.997163 29.983069,697.849298 L189.983069,605.368943 C202.367715,598.21059 217.632285,598.21059 230.016931,605.368943 Z M214,881.001863 L139,881.001863 C134.029437,881.001863 130,885.0313 130,890.001863 L130,890.001863 L130.003837,890.267824 C130.144209,895.115648 134.118197,899.002462 139,899.002462 L139,899.002462 L214,899.002462 C218.970563,899.002462 223,894.973024 223,890.002462 L223,890.002462 L222.996163,889.736501 C222.855791,884.888676 218.881803,881.001863 214,881.001863 L214,881.001863 Z M214,848.000765 L139,848.000765 C134.029437,848.000765 130,852.030202 130,857.000765 L130,857.000765 L130.003837,857.266726 C130.144209,862.11455 134.118197,866.001364 139,866.001364 L139,866.001364 L214,866.001364 C218.970563,866.001364 223,861.971927 223,857.001364 L223,857.001364 L222.996163,856.735403 C222.855791,851.887579 218.881803,848.000765 214,848.000765 L214,848.000765 Z M274,815.999701 L139,815.999701 C134.029437,815.999701 130,820.029138 130,824.999701 L130,824.999701 L130.003837,825.265662 C130.144209,830.113486 134.118197,834.000299 139,834.000299 L139,834.000299 L274,834.000299 C278.970563,834.000299 283,829.970862 283,825.000299 L283,825.000299 L282.996163,824.734338 C282.855791,819.886514 278.881803,815.999701 274,815.999701 L274,815.999701 Z M298,783.998636 L139,783.998636 C134.029437,783.998636 130,788.028073 130,792.998636 L130,792.998636 L130.003837,793.264597 C130.144209,798.112421 134.118197,801.999235 139,801.999235 L139,801.999235 L298,801.999235 C302.970563,801.999235 307,797.969798 307,792.999235 L307,792.999235 L306.996163,792.733274 C306.855791,787.88545 302.881803,783.998636 298,783.998636 L298,783.998636 Z M274,750.997538 L139,750.997538 C134.029437,750.997538 130,755.026976 130,759.997538 L130,759.997538 L130.003837,760.263499 C130.144209,765.111324 134.118197,768.998137 139,768.998137 L139,768.998137 L274,768.998137 C278.970563,768.998137 283,764.9687 283,759.998137 L283,759.998137 L282.996163,759.732176 C282.855791,754.884352 278.881803,750.997538 274,750.997538 L274,750.997538 Z"></path>
          </g>
        </g>
      </svg>';

      return $logo;

    }

    public function get_cornerstone_svg( $class = '', $style = '' ) {

      $class  = ( $class == '' ) ? '' : ' class="' . $class . '"';
      $style  = ( $style == '' ) ? '' : ' style="' . $style . '"';

      $logo = '<svg' . $class . $style . ' viewBox="0 0 792 780" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                  <g stroke="none" stroke-width="1" fill-rule="evenodd">
                    <path d="M43.3636095,86.9641283 L736.363609,0.386827599 C763.490826,-3.00220732 788.229136,16.2413912 791.618171,43.3686079 C791.872478,45.4041834 792,47.4535991 792,49.5049985 L792,729.625941 C792,756.964036 769.838095,779.125941 742.5,779.125941 C740.653098,779.125941 738.807643,779.022576 736.972292,778.816331 L43.9722921,700.941331 C18.9307987,698.127325 -1.0349541e-13,676.950049 -1.0658141e-13,651.750941 L-1.13686838e-13,136.082299 C-1.16744203e-13,111.117019 18.5909046,90.0590113 43.3636095,86.9641283 Z M373.599475,463.342808 C355.383475,481.548777 328.059475,491.443326 303.903475,491.443326 C235.395475,491.443326 208.863475,443.553711 208.467475,397.643005 C208.071475,351.336517 236.979475,301.467992 303.903475,301.467992 C328.059475,301.467992 352.611475,309.779413 370.827475,327.5896 L405.675475,293.948135 C377.163475,265.847617 341.523475,251.599467 303.903475,251.599467 C203.715475,251.599467 156.591475,325.214909 156.987475,397.643005 C157.383475,469.675319 200.943475,540.520287 303.903475,540.520287 C343.899475,540.520287 380.727475,527.459483 409.239475,499.358965 L373.599475,463.342808 Z M638.919475,302.655338 C617.931475,259.910888 573.183475,247.641647 530.019475,247.641647 C478.935475,248.037429 422.703475,271.388564 422.703475,328.381164 C422.703475,390.51893 474.975475,405.558644 531.603475,412.286937 C568.431475,416.244756 595.755475,426.930869 595.755475,453.052477 C595.755475,483.131905 564.867475,494.609582 531.999475,494.609582 C498.339475,494.609582 466.263475,481.152995 453.987475,450.677786 L410.427475,473.237357 C431.019475,523.897446 474.579475,541.311851 531.207475,541.311851 C592.983475,541.311851 647.631475,514.794461 647.631475,453.052477 C647.631475,386.956892 593.775475,371.917178 535.959475,364.793103 C502.695475,360.835284 474.183475,354.106991 474.183475,329.964292 C474.183475,309.383631 492.795475,293.156571 531.603475,293.156571 C561.699475,293.156571 587.835475,308.196285 597.339475,324.027563 L638.919475,302.655338 Z"></path>
                  </g>
                </svg>';

      return $logo;

    }

    public function get_svg( $name, $class = '', $style = '' ) {
      $function = array( $this, "get_{$name}_svg" );
      if ( is_callable( $function ) ) {
        return call_user_func( $function, $class, $style );
      }
      return '';
    }

    public function svg( $name, $class = '', $style = '' ) {
      echo $this->get_svg( $name, $class, $style );
    }

    public function admin_notice( $msg = '', $args = array() ) {

      if ( is_array( $msg ) ) {
        $args = $msg;
      }

      $args = wp_parse_args( $args, array(
        'message'     => is_string( $msg ) ? $msg : '',
        'handle'      => false,
        'echo'        => true,
        'class'       => '',
        'dismissible'  => false,
        'ajax_dismiss' => false
      ) );

      extract( $args );

      $script = '';

      if ( is_string( $ajax_dismiss ) ) {

        if ( ! $handle ) {
          $handle = 'tco_' . uniqid();
        }

        ob_start(); ?>

        <script type="text/javascript">
        jQuery( function( $ ) {
          $('[data-tco-notice="<?php echo $handle; ?>"]').on( 'click', '.notice-dismiss', function(){
            $.post('<?php echo admin_url('admin-ajax.php?action=' . esc_attr( $ajax_dismiss ) ); ?>');
          });
        } );
        </script>
        <?php

        $script = ob_get_clean();

      }

      $class = ( $dismissible ) ? ' ' . $class . ' is-dismissible' : ' ' . $class;

      $logo_svg = $this->get_themeco_svg();
      $logo = "<a class=\"tco-notice-logo\" href=\"https://theme.co/\" target=\"_blank\">{$logo_svg}</a>";

      if ( $handle ) {
      $handle = "data-tco-notice=\"$handle\"";
      }

      $notice = "<div class=\"tco-notice notice {$class}\" {$handle}>{$logo}<p>{$message}</p></div>{$script}";

      if ( $echo ) {
        echo $notice;
      }

      return $notice;

    }

    public function get_site_url() {
      return esc_attr( trailingslashit( network_home_url() ) );
    }

  }


  class Updates {

    private $tco;

    private $base_url = 'https://theme.co/apex/api-v2/packages/';
    private $errors = array();
    private $updated = false;

    public function __construct($tco) {
      $this->tco = $tco;
    }

    private function getBaseURL() {
      return !defined("THEMECO_DOMAIN")
        ? $this->base_url
        : \THEMECO_DOMAIN . "/apex/api-v2/packages/";
    }

    public function remote_request() {

      $args = apply_filters( 'themeco_update_api', array() );

      $args = wp_parse_args( $args, array(
        'api-key'  => 'unverified',
        'siteurl'  => urlencode( $this->tco->get_site_url() ),
      ) );

      if ( !$args['api-key'] )
        $args['api-key'] = 'unverified';

      $request_url = $this->getBaseURL() . trailingslashit( $args['api-key'] );

      unset($args['api-key']);

      $uri = add_query_arg( $args, $request_url );

      $request = wp_remote_get( $uri, array( 'timeout' => 15 ) );
      $connection_error = array( 'code' => 4, 'message' => $this->connection_error_message( ) );

      if ( is_wp_error( $request ) || $request['response']['code'] != 200 ) {
        self::store_error( $request );
        return $connection_error;
      }

      $data = json_decode( $request['body'], true );

      if ( defined('THEMECO_PRERELEASES') && THEMECO_PRERELEASES ) {
      $data = $this->edge_filter( $data );
    }

    return $data;

    }

    public function connection_error_message() {
      return 'Could not establish connection. For assistance, please start by reviewing our article on troubleshooting <a href="https://theme.co/docs/problems-with-product-validation/">connection issues.</a>';
    }


    //
    // Save connection errors.
    //

    public function store_error( $wp_error ) {

      if ( ! isset( $this->errors ) ) {
        $this->errors = array();
      }

      array_push( $this->errors, (array) $wp_error );

    }

    public function get_update_cache() {
      return get_site_option( 'themeco_update_cache', array() );
    }

    //
    // Return any saved errors.
    //

    public function get_errors() {
      return isset( $this->errors ) ? $this->errors : array();
    }

    public function edge_filter( $data ) {

    if ( isset( $data['themes'] ) ) {

      foreach ($data['themes'] as $theme => $theme_data ) {

        if ( !isset( $theme_data['edge'] ) ) continue;

          $edge = $theme_data['edge'];
          unset($theme_data['edge']);
        $data['themes'][$theme] = array_merge( $theme_data, $edge );

      }

    }

    if ( isset( $data['plugins'] ) ) {

      foreach ($data['plugins'] as $plugin => $plugin_data ) {

        if ( !isset( $plugin_data['edge'] ) ) continue;

          $edge = $plugin_data['edge'];
          unset($plugin_data['edge']);
        $data['plugins'][$plugin] = array_merge( $theme_data, $edge );

      }

    }

    return $data;

    }

    public function update( $force = false ) {
      if ( $this->updated && !$force ) return;
      $response = $this->remote_request();
      do_action( 'themeco_update_api_response', $response );
      update_site_option( 'themeco_update_cache', apply_filters( 'themeco_update_cache', array(), $response ) );
      $this->updated = true;
    }

    public function refresh( $force = false ) {

      if ( !is_admin() )
        return false;

      $this->update( $force );

      return true;
    }

  }


  class Validator {

    protected $tco;
    protected $code = '';
    protected $is_valid = false;
    protected $is_verified = false;
    protected $has_site = false;
    protected $site_match = false;
    protected $response = array();

    protected $base_url = 'https://theme.co/api-v2/validate/';
    protected $errors = array();

    public function __construct( $tco, $code, $product ) {
      $this->tco = $tco;
      $this->code = $code;
      $this->product = $product;
    }

    private function getBaseURL() {
      return !defined("THEMECO_DOMAIN")
        ? $this->base_url
        : \THEMECO_DOMAIN . "/api-v2/validate/";
    }

    public function run() {

      $this->response = $this->request();

      if ( $this->has_connection_error() ) {
        return;
      }

      switch ( (int) $this->response['code'] ) {
        case 2:
          $this->is_valid = false;
          break;
        case 3:
          $this->is_valid = true;
          break;
        case 4:
          $this->is_valid = true;
          $this->is_verified = true;
          break;
        case 5:
          $this->is_valid = true;
          $this->is_verified = true;
          $this->has_site = true;
          break;
        case 6:
          $this->is_valid = true;
          $this->is_verified = true;
          $this->has_site = true;
          $this->site_match = true;
          break;
      }

      return true;

    }

    public function request() {

      $args = array(
        'product'  => $this->product,
        'siteurl'  => urlencode( $this->tco->get_site_url() ),
      );

      $request_url = $this->getBaseURL() . trailingslashit( ( $this->code ) ? $this->code : 'unverified' );

      $uri = add_query_arg( $args, $request_url );

      $request = wp_remote_get( $uri, array( 'timeout' => 15 ) );

      if ( is_wp_error( $request ) ) {
        return $request;
      }

      if ( ! isset( $request['response'] ) || ! isset( $request['response']['code'] ) || 200 != $request['response']['code'] ) {
        ob_start();
        echo '<pre>';
        print_r( $request );
        echo '</pre>';
        $error = ob_get_clean();
        return new WP_Error( 'tco_connection_error', $error );
      }

      $data = json_decode( wp_remote_retrieve_body( $request ), true );

      if ( isset( $data['error'] ) ) {
        return new WP_Error( 'tco_connection_error', $data['error'] );
      }

      if ( ! isset( $data['code'] ) ) {
        return new WP_Error( 'tco_connection_error', json_encode( $data ) );
      }

      return $data;

    }

    public function has_connection_error() {
      return is_wp_error( $this->response );
    }

    public function is_valid() {
      return $this->is_valid;
    }

    public function is_verified() {
      return $this->is_verified;
    }

    public function has_site() {
      return $this->has_site;
    }

    public function site_match() {
      return $this->site_match;
    }

    public function connection_error_details() {
      return ( $this->has_connection_error() ) ? $this->response->get_error_message() : '';
    }

    public function get_response() {
      return $this->response;
    }

  }

  endif;
}

namespace {

  if ( ! function_exists('tco_common') ) :

    function tco_common() {
    if (! did_action('after_setup_theme')) {
      throw new Error('tco_common called before after_setup_theme'); // This can't happen otherwise X breaks
    }
    return \Themeco\Common\Shared::instance();
  }

  endif;
}
