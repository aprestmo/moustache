<?php

namespace Themeco\Theme\Services;

use Themeco\Theme\Theme;
use Themeco\Theme\Util\VersionedUrl;

class Enqueue {

  public function __construct(Theme $theme, VersionedUrl $versionedUrl) {
    $this->theme = $theme;
    $this->versionedUrl = $versionedUrl;
  }

  public function setup() {
    add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
    $this->versionedUrl->configure( $this->theme->path, $this->theme->url );
  }

  public function enqueue() {
    $file = $this->versionedUrl->get('framework/dist/theme','css');
    wp_enqueue_style( 'x-theme', $file['url'], NULL, $file['version'], 'all' );
    if ( is_child_theme() && apply_filters( 'x_enqueue_parent_stylesheet', false ) ) {
      wp_enqueue_style( 'x-child-theme', get_stylesheet_directory_uri() . '/style.css', [ 'x-theme' ] );
    }
  }

}