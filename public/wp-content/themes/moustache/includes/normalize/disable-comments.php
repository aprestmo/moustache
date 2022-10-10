<?php /*
add_action( 'admin_init', function () {

	// Redirect any user trying to access comments page
	global $pagenow;
	if ( 'edit-comments.php' == $pagenow ) {
		wp_redirect( admin_url() );
		exit;
	}


	// Disable support for comments and trackbacks in post types
	$post_types = get_post_types();
	foreach ( $post_types as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}


	// Remove comments metabox from dashboard
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );

} );


// Remove comments page in menu
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
} );


// Close comments on the front-end
add_filter( 'comments_open', '__return_false', 20, 2);
add_filter( 'pings_open', '__return_false', 20, 2);


// Hide existing comments
add_filter( 'comments_array', '__return_empty_array', 10, 2 );


// Remove comments links from admin bar
add_action( 'init', function () {
	if ( is_admin_bar_showing() ) {
		remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
	}
});

// Remove comments from wpadminbar
add_action('wp_before_admin_bar_render', function() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
});
*/
