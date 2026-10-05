<?php
/**
 * Custom admin menu: Statistikk
 *
 * Registers a top-level "Statistikk" menu with a tabbed dashboard page.
 * Submenu pages are registered but hidden (no menu item) for direct URL access.
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

	// Hidden submenu pages (accessible via direct URL but not shown in menu)
	add_submenu_page(
		'moustache-stats-dashboard',
		__('Spillere', 'moustache'),
		'', // Empty title = hidden from submenu
		'manage_options',
		'player-stats',
		'moustache_player_stats_admin_page'
	);

	add_submenu_page(
		'moustache-stats-dashboard',
		__('Motstandere', 'moustache'),
		'',
		'manage_options',
		'opponent-stats',
		'moustache_opponent_stats_admin_page'
	);

	add_submenu_page(
		'moustache-stats-dashboard',
		__('Lagstatistikk', 'moustache'),
		'',
		'manage_options',
		'team-stats',
		'moustache_team_stats_admin_page'
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