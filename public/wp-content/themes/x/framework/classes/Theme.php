<?php

namespace Themeco\Theme;
use Themeco\Theme\Util\IocContainer;

class Theme {

  protected static $instance;
  protected $path;
  protected $url;

  protected $container;
  protected $singletons = [];

  protected $includes = [];
  protected $includesLegacy = [];
  protected $theme_option_defaults = [];

  /**
   * Create the plugin and assign basic configuration properties
   */
  public function __construct( $path, $url ) {
    $this->path = untrailingslashit( $path );
    $this->url = untrailingslashit( $url );
    $this->setupContainer();
  }

  public function setupContainer() {
    $this->container = new IocContainer;
    $this->container->register(__CLASS__, $this);
    $this->container->setRegistrationHandler(function($class, $instance, $interfaces, $hasSetup) {
      if (in_array( $class, $this->singletons ) ) {
        $this->container->register($class, $instance);
      }
      if ( $hasSetup ) {
        $instance->setup();
      }
    });
  }

  public function resolve($class, $singleton = false ) {
    if ($singleton) {
      $this->singletons[] = $class;
    }
    return $this->container->resolve($class);
  }

  /**
   * Singleton Instantiation
   */
  public static function instance() {
    return self::$instance;
  }

  public static function instantiate( $path, $url ) {
    self::$instance = new self( $path, $url );
    spl_autoload_register([__CLASS__, 'autoloader']);
  }

  // Functions previously from X_Bootstrap

  public function boot( $classes, $main_includes ) {
    // Jquery add in
    // @TODO Remove when the plugins actually have removed jquery
    add_action("wp_enqueue_scripts", function() {
      if (apply_filters("cs_use_jquery_everywhere", true)) {
        wp_enqueue_script( 'jquery' );
      }
    });


    $this->classes = $classes;
    $this->includes = $main_includes;

    // Define Path / URL Constants
    // ---------------------------

    define( 'X_TEMPLATE_PATH', $this->path );
    define( 'X_TEMPLATE_URL', $this->url );


    // Preboot
    // -------

    $theme_setup = apply_filters( '_x_theme_setup', X_TEMPLATE_PATH . '/framework/setup.php' );

    if ( file_exists( $theme_setup ) ) {
      require_once( $theme_setup );
    }

    $this->includesLegacy = apply_filters('x_boot_legacy_includes', []);


    // Set Asset Revision Constant (For Cache Busting)
    // -----------------------------------------------

    if ( ! defined('X_ASSET_REV') ) {
      define( 'X_ASSET_REV', X_VERSION );
    }

    // Localization
    // ------------

    load_theme_textdomain( '__x__', X_TEMPLATE_PATH . '/framework/lang' );

    // Preinit
    // -------

    $this->boot_context('preinit');

    if ( is_admin() ) {
      $this->boot_context('admin');
    }

    add_action( 'after_setup_theme',                  array( $this, 'after_setup_theme' ), 0 );
    add_action( 'init',                               array( $this, 'init' )                 );
    add_action( 'admin_init',                         array( $this, 'ajax_init' )            );
    add_action( 'cornerstone_before_boot_app',        array( $this, 'app_init' )             );
    add_action( 'cornerstone_before_custom_endpoint', array( $this, 'app_init' )             );
    add_action( 'cornerstone_before_admin_ajax',      array( $this, 'app_init' )             );
    add_action( 'cornerstone_before_admin_ajax',      array( $this, 'ajax_init' )            );
    add_action( 'cornerstone_before_custom_endpoint', array( $this, 'ajax_init' )            );

  }

  public function after_setup_theme() {
    if ( ! is_admin() ) {
      $this->boot_context('front_end');
      $this->boot_context('theme_front_end');
    }
  }

  public function init() {
    $this->boot_context('init');
    if ( is_user_logged_in() ) {
      $this->boot_context('logged_in');
    }
  }

  public function admin_init() {
    $this->boot_context('admin_init');
  }

  public function app_init() {
    $this->boot_context('app_init');
  }

  public function ajax_init() {
    if ( defined( 'DOING_AJAX' ) ) {
      $this->boot_context('ajax');
    }
  }

  public function load_boot_entry($file) {
    list($enabled, $path) = is_array( $file ) ? $file : [ true, $file];
    if ($enabled) {
      include_once( X_TEMPLATE_PATH . "/framework/$path.php" );
    }
  }

  public function is_classic() {
    if (!function_exists('CS')) {
      return true;
    }
    return ! apply_filters( 'tco_feature_flag_theme_options_reboot', false );
  }

  public function boot_context( $context ) {

    if ( isset( $this->includes[$context] ) ) {
      foreach ( $this->includes[$context] as $file ) {
        $this->load_boot_entry($file);
      }
    }

    if ( $this->is_classic() ) { // If Cornerstone isn't active
      if ( isset( $this->includesLegacy[$context] ) ) {
        foreach ( $this->includesLegacy[$context] as $file ) {
          $this->load_boot_entry($file);
        }
      }
    }

    if ( isset( $this->classes[$context] ) ) {
      foreach ( $this->classes[$context] as $class ) {
        $this->resolve($class, true);
      }
    }

    do_action( 'x_boot_' . $context );

  }

  public function __get($name) {
    switch ($name) {
      case 'path':
      case 'url':
        return $this->{$name};
      default:
        return null;
    }
  }

  public static function autoloader( $class) {
    $prefix = 'Themeco\\Theme\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $filename = self::$instance->path. '/framework/classes/' . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($filename)) require_once $filename;
  }

}
