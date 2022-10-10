<?php
if (!is_admin()) {
  return;
}

add_action('_admin_menu', function () {
  global $menu;

  $remove_widgets_dashboard = array(
    'dashboard_right_now', // Oversikt
    // 'dashboard_recent_comments',
    // 'dashboard_plugins',
    // 'dashboard_recent_drafts',
    // 'dashboard_incoming_links',
    'dashboard_quick_press', // Hurtigkladd
    'dashboard_primary', // WordPress-nyheter
    // 'dashboard_secondary',
    'dashboard_activity', // Aktivitet
  );

  foreach ($remove_widgets_dashboard as $remove_widget_dashboard) {
    remove_meta_box($remove_widget_dashboard, 'dashboard', 'core');
  }

  // remove_menu_page( 'edit.php' );
  // remove_menu_page( 'edit-comments.php' );
  // remove_menu_page( 'link-manager.php' );

  if (!current_user_can('manage_options')) {
    remove_menu_page('themes.php');
    remove_menu_page('tools.php');
  }
});

/**
 * Show a Menu button on top level
 *
 * @since 1.0
 */
// add_action( 'admin_menu', function ( ) {
// 	if ( get_option( 'page_on_front' ) ) {
// 		$page_id = absint( get_option( 'page_on_front' ) );
// 		$menuname = esc_html__( 'Front page', 'starter' );
// 		add_object_page($menuname, $menuname, 'edit_pages', 'post.php?post=' . $page_id . '&action=edit', '', 'dashicons-admin-home' );
// 	}

// 	$menuname = esc_html__( 'Menu', 'starter' );
// 	add_object_page( $menuname, $menuname, 'edit_pages', 'nav-menus.php', '', 'dashicons-menu' );
// } );

/**
 * Remove Welcome panel
 *
 * @since 1.0
 */
remove_action('welcome_panel', 'wp_welcome_panel');

/**
 * Remove footer text (backend)
 *
 * @access public
 */
add_filter('admin_footer_text', function ($footer_text) {
  return '';
});
