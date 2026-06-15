<?php

/**
 * Custom functions for the Moustache theme
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Check if pagination should be shown
 *
 * @return bool
 */
function show_posts_nav(): bool
{
	global $wp_query;
	return ($wp_query->max_num_pages > 1);
}

/**
 * BEM-style Walker for WordPress menus
 */
class Walker_Texas_Ranger extends Walker_Nav_Menu
{
	private string $css_class_prefix;
	private array $item_css_class_suffixes;

	/**
	 * Constructor
	 *
	 * @param string $css_class_prefix Prefix for BEM classes
	 */
	public function __construct(string $css_class_prefix)
	{
		$this->css_class_prefix = $css_class_prefix;
		$this->item_css_class_suffixes = [
			'item'                    => '__item',
			'parent_item'             => '__item--parent',
			'active_item'             => '__item--active',
			'parent_of_active_item'   => '__item--parent--active',
			'ancestor_of_active_item' => '__item--ancestor--active',
			'sub_menu'               => '__sub-menu',
			'sub_menu_item'          => '__sub-menu__item',
			'link'                   => '__link',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output)
	{
		$id_field = $this->db_fields['id'] ?? 'id';

		if (is_object($args[0])) {
			$args[0]->has_children = !empty($children_elements[$element->$id_field]);
		}

		return parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
	}

	/**
	 * @inheritDoc
	 */
	public function start_lvl(&$output, $depth = 1, $args = [])
	{
		$real_depth = $depth + 1;
		$indent = str_repeat("\t", $real_depth);

		$classes = [
			$this->css_class_prefix . $this->item_css_class_suffixes['sub_menu'],
			$this->css_class_prefix . $this->item_css_class_suffixes['sub_menu'] . '--' . $real_depth
		];

		$output .= "\n" . $indent . '<ul class="' . implode(' ', $classes) . '">' . "\n";
	}

	/**
	 * @inheritDoc
	 */
	public function start_el(&$output, $item, $depth = 0, $args = [], $id = 0)
	{
		$indent = $depth > 0 ? str_repeat("    ", $depth) : '';

		// Build item classes
		$item_classes = $this->get_item_classes($item, $depth, $args);
		$class_string = implode(' ', array_filter($item_classes));

		// Build link classes
		$link_classes = $this->get_link_classes($depth);
		$link_class_string = implode(' ', array_filter($link_classes));

		// Build attributes
		$attributes = $this->get_item_attributes($item);

		// Build output
		$output .= $this->build_item_output($indent, $class_string, $link_class_string, $attributes, $item, $args);
	}

	/**
	 * Get item classes
	 */
	private function get_item_classes($item, $depth, $args): array
	{
		return [
			'item_class'            => $depth == 0 ? $this->css_class_prefix . $this->item_css_class_suffixes['item'] : '',
			'parent_class'          => $args->has_children ? $this->css_class_prefix . $this->item_css_class_suffixes['parent_item'] : '',
			'active_page_class'     => in_array("current-menu-item", $item->classes) ? $this->css_class_prefix . $this->item_css_class_suffixes['active_item'] : '',
			'active_parent_class'   => in_array("current-menu-parent", $item->classes) ? $this->css_class_prefix . $this->item_css_class_suffixes['parent_of_active_item'] : '',
			'active_ancestor_class' => in_array("current-menu-ancestor", $item->classes) ? $this->css_class_prefix . $this->item_css_class_suffixes['ancestor_of_active_item'] : '',
			'depth_class'           => $depth >= 1 ? $this->css_class_prefix . $this->item_css_class_suffixes['sub_menu_item'] . ' ' . $this->css_class_prefix . $this->item_css_class_suffixes['sub_menu'] . '--' . $depth . '__item' : '',
			'item_id_class'         => $this->css_class_prefix . '__item--' . $item->object_id,
			'user_class'            => $item->classes[0] !== '' ? $this->css_class_prefix . '__item--' . $item->classes[0] : ''
		];
	}

	/**
	 * Get link classes
	 */
	private function get_link_classes($depth): array
	{
		return [
			'item_link'   => $depth == 0 ? $this->css_class_prefix . $this->item_css_class_suffixes['link'] : '',
			'depth_class' => $depth >= 1 ? $this->css_class_prefix . $this->item_css_class_suffixes['sub_menu'] . $this->item_css_class_suffixes['link'] . '  ' . $this->css_class_prefix . $this->item_css_class_suffixes['sub_menu'] . '--' . $depth . $this->item_css_class_suffixes['link'] : '',
		];
	}

	/**
	 * Get item attributes
	 */
	private function get_item_attributes($item): string
	{
		$attrs = [
			'title'  => $item->attr_title ? ' title="'  . esc_attr($item->attr_title) . '"' : '',
			'target' => $item->target ? ' target="' . esc_attr($item->target) . '"' : '',
			'rel'    => $item->xfn ? ' rel="'    . esc_attr($item->xfn) . '"' : '',
			'href'   => $item->url ? ' href="'   . esc_attr($item->url) . '"' : ''
		];

		return implode('', array_filter($attrs));
	}

	/**
	 * Build item output
	 */
	private function build_item_output($indent, $class_string, $link_class_string, $attributes, $item, $args): string
	{
		$output = $indent . '<li class="' . $class_string . '">';
		$item_output = $args->before ?? '';
		$item_output .= '<a' . $attributes . ' class="' . $link_class_string . '">';
		$item_output .= ($args->link_before ?? '') . apply_filters('the_title', $item->title, $item->ID) . ($args->link_after ?? '');
		$item_output .= '</a>';
		$item_output .= $args->after ?? '';

		return $output . apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth ?? 0, $args);
	}
}

/**
 * Create BEM-style menu
 */
function bem_menu(string $location = "main_menu", string $css_class_prefix = 'main-menu', $css_class_modifiers = null): void
{
	$modifiers = '';

	if ($css_class_modifiers) {
		$modifiers = is_array($css_class_modifiers) ? implode(" ", $css_class_modifiers) : $css_class_modifiers;
	}

	$args = [
		'theme_location' => $location,
		'container'      => false,
		'items_wrap'     => '<ul class="' . esc_attr($css_class_prefix . ' ' . $modifiers) . '">%3$s</ul>',
		'walker'         => new Walker_Texas_Ranger($css_class_prefix)
	];

	if (has_nav_menu($location)) {
		wp_nav_menu($args);
	} else {
		echo '<p>Du må definere en meny i WordPress-admin</p>';
	}
}

/**
 * Initialize ACF Google Maps API key
 */
function initialize_acf_google_maps(): void
{
	acf_update_setting('google_api_key', 'AIzaSyBpX2xs6qaBOTQmn0VB7IiHd0vmRatZz00');
}
add_action('acf/init', 'initialize_acf_google_maps');

/**
 * Get asset base path based on environment
 */
function get_asset_base_path(): string
{
	if (!defined('WP_ENVIRONMENT_TYPE')) {
		define('WP_ENVIRONMENT_TYPE', 'production');
	}
	return WP_ENVIRONMENT_TYPE === 'local' ? '/public/' : '/dist/';
}

/**
 * Format club titles with Norwegian 'og'
 */
function formatClubTitlesWithOg(array $titles): string
{
	$count = count($titles);

	if ($count === 0) return '';
	if ($count === 1) return $titles[0];
	if ($count === 2) return $titles[0] . ' og ' . $titles[1];

	$last_item = array_pop($titles);
	return implode(', ', $titles) . ' og ' . $last_item;
}

// Initialize arrays for withdrawn clubs
$clubs_withdrawn = [];
$club_titles = [];

if (!empty($clubs_withdrawn)) {
	foreach ($clubs_withdrawn as $club) {
		$club_titles[] = get_the_title($club);
	}
}
