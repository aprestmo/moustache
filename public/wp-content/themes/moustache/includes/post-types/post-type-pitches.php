<?php
//Die if accessed directly
defined( 'ABSPATH' ) || die('Shame on you');

define('POST_TYPE_PITCH', 'pitch');

add_action('init', function() {

	register_post_type( 'pitch' , array(
		'labels' => array(
			'name' => _x('Pitches', 'post type general name', 'moustache'),
			'singular_name' => _x('Pitch', 'post type singular name', 'moustache'),
			'add_new' => _x('Add Pitch', 'post type singular', 'moustache'),
			'add_new_item' => __('Add', 'moustache'),
			'edit_item' => __('Edit', 'moustache'),
			'new_item' => __('New', 'moustache'),
			'view_item' => __('View', 'moustache'),
			'search_items' => __('Search', 'moustache'),
			'not_found' =>  __('Not Found', 'moustache'),
			'not_found_in_trash' => __('Not Found in Trash', 'moustache'),
			'parent_item_colon' => ''
		),
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'show_in_nav_menus' => true,
		'show_ui' => true,
		'query_var' => true,
		'has_archive' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => true,
		'menu_position' => 5,
		'menu_icon' => 'dashicons-location',
		'supports' => array('title')
	));

		// register_post_type(POST_TYPE_PITCH, array(
		// 		'label'               => esc_html__( 'Fixtures', TRANSLATION_DOMAIN ),
		// 		'description'         => esc_html__( 'Fixtures', TRANSLATION_DOMAIN ),
		// 		'labels'              => array(
		// 			'name'                => esc_html__( 'Fixtures', TRANSLATION_DOMAIN ),
		// 			'singular_name'       => esc_html__( 'Fixture', TRANSLATION_DOMAIN ),
		// 			'menu_name'           => esc_html__( 'Fixtures', TRANSLATION_DOMAIN ),
		// 			'name_admin_bar'      => esc_html__( 'Fixtures', TRANSLATION_DOMAIN ),
		// 			'add_new'             => esc_html__( 'Add new', TRANSLATION_DOMAIN ),
		// 			'add_new_item'        => esc_html__( 'Add', TRANSLATION_DOMAIN ),
		// 			'new_item'            => esc_html__( 'Add', TRANSLATION_DOMAIN ),
		// 			'edit_item'           => esc_html__( 'Edit', TRANSLATION_DOMAIN ),
		// 			'view_item'           => esc_html__( 'Show', TRANSLATION_DOMAIN ),
		// 			'all_items'           => esc_html__( 'All articles', TRANSLATION_DOMAIN ),
		// 			'search_items'        => esc_html__( 'Search', TRANSLATION_DOMAIN ),
		// 			'parent_item_colon'   => esc_html__( 'Parent', TRANSLATION_DOMAIN ),
		// 			'not_found'           => esc_html__( 'Nothing found', TRANSLATION_DOMAIN ),
		// 			'not_found_in_trash'  => esc_html__( 'Nothing found in trash bin', TRANSLATION_DOMAIN ),
		// 			'update_item'         => esc_html__( 'Update', TRANSLATION_DOMAIN ),
		// 		),
		// 		'supports'            => ['title', 'revisions'],
		// 		'taxonomies'          => [TAXONOMY_ARTICLE_CATEGORY, 'post_tag'],
		// 		'hierarchical'        => false,
		// 		'public'              => true,
		// 		'show_ui'             => true,
		// 		'show_in_menu'        => true,
		// 		'menu_position'       => 21,
		// 		'menu_icon'           => 'dashicons-id-alt',
		// 		'show_in_admin_bar'   => true,
		// 		'show_in_nav_menus'   => true,
		// 		'rewrite'             => true,
		// 		'can_export'          => true,
		// 		'has_archive'         => false,
		// 		'exclude_from_search' => false,
		// 		'publicly_queryable'  => true,
		// 		'capability_type'     => 'post',
		// 	) );

	});

/**
 *
 * Add ACF lead as excerpt
 *
 */
// add_filter( 'wp_insert_post_data', function( $data, $postarr ) {

// 	if ( $data['post_type'] == POST_TYPE_PITCH && isset( $postarr['acf']['field_5628878814866'] ) ) {
// 		$data['post_excerpt'] = $postarr['acf']['field_5628878814866'];
// 	}

// 	return $data;

// }, 10, 2 );

// /**
//  *
//  * Add ACF image as featured image
//  *
//  */
// add_action( 'save_post_' . POST_TYPE_PITCH, function( $post_ID, $post, $update ) {

// 	// make sure we actually have submitted the form data
// 	if ( ! isset( $_POST['acf']['field_56295fe8668fa'] ) ) {
// 		return;
// 	}

// 	if ( is_numeric( $_POST['acf']['field_56295fe8668fa'] ) ) {
// 		update_post_meta( $post_ID, '_thumbnail_id', $_POST['acf']['field_56295fe8668fa'] );
// 	}

// }, 10, 3 );
