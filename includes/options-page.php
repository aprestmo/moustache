<?php

defined('ABSPATH') || exit;

if (function_exists('acf_add_options_page')) {
	acf_add_options_page([
		'page_title' => 'Theme General Settings',
		'menu_title' => 'Theme Settings',
		'menu_slug' => 'theme-general-settings',
		'capability' => 'manage_options',
		'redirect' => false,
	]);

	acf_add_options_sub_page([
		'page_title' => 'Club Information',
		'menu_title' => 'Club Information',
		'menu_slug' => 'theme-club-information',
		'parent_slug' => 'theme-general-settings',
		'capability' => 'manage_options',
	]);
}
