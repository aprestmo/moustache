<?php
  function cptui_register_my_cpts_pitch() {

  /**
   * Post Type: Pitches.
   */

  $labels = [
    'name' => __('Pitches', 'moustache'),
    'singular_name' => __('Pitch', 'moustache'),
    'menu_name' => __('Pitches', 'moustache'),
  ];

  $args = [
    'label' => __('Pitches', 'moustache'),
    "labels" => $labels,
    "description" => "",
    "public" => true,
    "publicly_queryable" => true,
    "show_ui" => true,
    "show_in_rest" => true,
    "rest_base" => "",
    "rest_controller_class" => "WP_REST_Posts_Controller",
    "rest_namespace" => "wp/v2",
    "has_archive" => "baner",
    "show_in_menu" => true,
    "show_in_nav_menus" => true,
    "delete_with_user" => false,
    "exclude_from_search" => false,
    "capability_type" => "post",
    "map_meta_cap" => true,
    "hierarchical" => false,
    "can_export" => false,
    "rewrite" => [ "slug" => "bane", "with_front" => false ],
    "query_var" => true,
    "menu_position" => 5,
    "menu_icon" => "dashicons-location",
    "supports" => [ "title" ],
    "show_in_graphql" => false,
  ];

  register_post_type( "pitch", $args );
  }

  add_action( 'init', 'cptui_register_my_cpts_pitch' );
