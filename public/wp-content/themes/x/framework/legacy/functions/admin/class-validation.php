<?php

class X_Validation {

  public static $instance;
  public $script_data = array();

  public function setup() {

    $this->add_script_data( 'x-validation', array( $this, 'script_data' ) );
    $this->add_script_data( 'x-validation-revoke', array( $this, 'script_data_revoke' ) );

    add_action( 'wp_ajax_x_validation', array( $this, 'ajax_validation' ) );
    add_action( 'wp_ajax_x_validation_revoke', array( $this, 'ajax_revoke' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

    do_action( 'x_overview_init' );

  }

  public function get_module_data() {

		$modules = array();

		foreach ($this->script_data as $handle => $callback ) {

      if ( is_callable( $callback ) ) {
				$modules[$handle] = call_user_func( $callback );
			}

		}

		return array(
      '_x_nonce' => wp_create_nonce( 'x-nonce' ),
      'tco' => [
        'strings' => [
          'details' => __( 'Details', '__x__' ),
          'back'    => __( 'Back', '__x__' ),
          'yep'     => __( 'Yep', '__x__' ),
          'nope'    => __( 'Nope', '__x__' )
        ],
        'logo' => tco_common()->get_themeco_svg()
      ],
      'modules'  => $modules,
      'notices'  => $this->get_active_notices()
    );

	}

  public function get_active_notices() {

    if ( isset( $_REQUEST['notice'] ) ) {
      $notices = explode( '|', sanitize_text_field( $_REQUEST['notice'] ) );
      return $notices;
    }

    return array();

  }

	public function add_script_data( $handle, $callback ) {
		$this->script_data[$handle] = $callback;
	}

  public function enqueue_scripts( $hook ) {
    if ( strpos( $hook, 'x-addons' ) !== false ) {
      wp_register_script( 'x-validation-js', X_TEMPLATE_URL . "/framework/dist/js/admin/x-validation.js", array( 'jquery', 'wp-util' ), X_ASSET_REV, true );
      wp_localize_script( 'x-validation-js', 'xValidationData', $this->get_module_data() );
      wp_enqueue_script( 'x-validation-js' );
    }
  }

  public function script_data() {
    return array(
      'verifying'   => __( 'Verifying license&hellip;', '__x__' ),
      'error'       => __( '<strong>Uh oh</strong>, we couldn&apos;t check if this license was valid. <a data-tco-error-details href="#">Details.</a>', '__x__' ),
      'notices'     => array(
        'validation-complete' => __( '<strong>Congratulations!</strong> Your site is validated. Addons are now unlocked.', '__x__' )
      ),
      'errorButton' => __( 'Go Back', '__x__' ),
    );
  }

  public function script_data_revoke() {
    return array(
      'confirm'  => x_i18n( 'overview', 'revoke-validation'),
      'accept'   => __( 'Yes, revoke validation', '__x__' ),
      'decline'  => __( 'Stay validated', '__x__' ),
      'revoking' => __( 'Revoking&hellip;', '__x__' ),
      'notices'  => array(
        'validation-revoked' => sprintf( __( '<strong>Validation revoked.</strong> You can re-assign licenses from <a href="%s" target="_blank">Manage Licenses</a>.', '__x__' ), 'https://theme.co/account/dashboard/' )
      )
    );
  }

  public function check_ajax_referer() {
    if ( ! isset( $_REQUEST['_x_nonce'] ) || false === wp_verify_nonce( $_REQUEST['_x_nonce'], 'x-nonce' ) ) {
      wp_send_json_error();
    }
  }

  public function ajax_validation() {

    $this->check_ajax_referer();

    if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['code'] ) || ! $_POST['code'] ) {
      wp_send_json_error( array( 'message' => 'No purchase code specified.' ) );
    }

    $this->code = sanitize_text_field( $_POST['code'] );
    $validator = tco_common()->validator( $this->code, X_SLUG );

    $validator->run();

    if ( $validator->has_connection_error() ) {
      wp_send_json_error( array( 'message' => $validator->connection_error_details() ) );
    }

    $response = $this->get_validation_response( $validator );
    $response['response_body'] = $validator->get_response();

    if ( isset( $response['complete'] ) && $response['complete'] ) {
      $this->update_validation( $this->code );
    } else {
      $this->update_validation( false );
    }

    wp_send_json_success( $response );

  }

  public function get_validation_response( $validator ) {

    // Purchase code is not valid
    if ( ! $validator->is_valid() ) {
      return array(
        'message' => __( 'We&apos;ve checked your code, but it <strong>doesn&apos;t appear to be a valid license.</strong> Please double check the code and try again.', '__x__' ),
        'button'  => __( 'Go Back', '__x__' ),
        'dismiss' => true,
      );
    }

    // Valid, but the purchase code isn't associated with an account.
    if ( ! $validator->is_verified() ) {
      return array(
        'message' => __( 'This looks like a <strong>brand new license that hasn&apos;t been added to a Themeco account yet.</strong> Login to your existing account or register a new one to continue.', '__x__' ),
        'button'  => __( 'Login or Register', '__x__' ),
        'url'     => add_query_arg( $this->out_params(), 'https://theme.co/docs/product-validation/' )
      );
    }

    // Purchase code linked to an account, but doesn't have a site
    if ( ! $validator->has_site() ) {
      return array(
        'message' => __( 'Your code is valid, but <strong>we couldn&apos;t automatically link it to your site.</strong> You can add this site from within your Themeco account.', '__x__' ),
        'button'  => __( 'Manage Licenses', '__x__' ),
        'url'     => 'https://theme.co/account/dashboard/',
        'dismiss' => true,
        'newTab'  => true
      );
    }

    // Purchase code linked, and site exists, but doesn't match this site.
    if ( ! $validator->site_match() ) {
      return array(
        'message' => __( 'Your code is valid but looks like it has <strong>already been used on another site.</strong> You can revoke and re-assign within your Themeco account.', '__x__' ),
        'button'  => __( 'Manage Licenses', '__x__' ),
        'url'     => 'https://theme.co/account/dashboard/',
        'dismiss' => true,
        'newTab'  => true
      );
    }

    return array(
      'complete' => true,
      'message' => __( '<strong>Congratulations,</strong> your site is now validated!', '__x__' )
    );

  }

  public function out_params() {
    return array(
      'code'        => $this->code,
      'product'     => 'x',
      'siteurl'     => tco_common()->get_site_url(),
      'return-url'  => esc_url( x_addons_get_link_home() )
    );
  }

  public function ajax_revoke() {
    $this->check_ajax_referer();
    $this->update_validation( false );
    wp_send_json_success();
  }

  public function update_validation( $code ) {

    if ( $code ) {
      update_option( 'x_product_validation_key', $code );
    } else {
      delete_option( 'x_product_validation_key' );
    }

    tco_common()->updates()->refresh();

  }

  public static function preload_key() {
    $key = '';
    if ( isset( $_REQUEST['tco-key'] ) ) {
      $key = esc_html( $_REQUEST['tco-key'] );
    }
    return $key;
  }

  public function preview_unlock( $box_class, $text = 'Setup Now' ) {

    $text = $text ? $text : __( 'Setup Now', '__x__' );
    ?>
      <a class="tco-btn tco-btn-nope" href="#" data-tco-toggle="<?php echo $box_class; ?> .tco-overlay"><?php echo $text; ?></a>
    <?php

  }


  public function preview_overlay( $box_class ) {

    ?>
      <div class="tco-overlay tco-overlay-box-content">
        <a class="tco-overlay-close" href="#" data-tco-toggle="<?php echo $box_class; ?> .tco-overlay"><?php tco_common()->admin_icon( 'no' ); ?></a>
        <h4 class="tco-box-content-title"><?php _e( 'How do I unlock this feature?', '__x__' ); ?></h4>
        <p><?php printf( x_i18n('overview', 'how-do-i-unlock' ), 'data-tco-focus="validation-input"'); ?></p>
      </div>
    <?php

  }


  public static function instance() {
    if ( ! isset( self::$instance ) ) {
      self::$instance = new self;
      self::$instance->setup();
    }
    return self::$instance;
  }

}
