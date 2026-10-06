<?php
/**
 * Custom admin menu: Statistikk
 *
 * Registers a top-level "Statistikk" menu with a tabbed dashboard page.
 * No submenu pages — all tabs live in the dashboard.
 *
 * @package Moustache
 */

defined('ABSPATH') || die('Shame on you');

add_action('admin_menu', function (): void {
	// Top-level menu: tabbed dashboard
	$hook = add_menu_page(
		__('Statistikk', 'moustache'),
		__('Statistikk', 'moustache'),
		'manage_options',
		'moustache-stats-dashboard',
		'moustache_stats_dashboard_page',
		'dashicons-chart-bar',
		56 // After Posts (55), before Pages (57)
	);

	// Optional: style the menu icon if dashicons not loading
	add_action("load-$hook", function (): void {
		add_action('admin_head', function (): void {
			?>
			<style>
				#toplevel_page_moustache-stats-dashboard .dashicons-chart-bar:before {
					content: "\f185";
				}
			</style>
			<?php
		});
	});
});