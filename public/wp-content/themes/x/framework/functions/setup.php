<?php

function x_theme_support() {
  add_theme_support( 'post-thumbnails' );
  add_theme_support( 'menus' );
}

add_action( 'after_setup_theme', 'x_theme_support' );



function x_meta() {
  ?>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
  <?php
}

add_action( 'wp_head', 'x_meta' );
