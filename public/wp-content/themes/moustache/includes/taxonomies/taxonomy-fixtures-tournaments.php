<?php
function fixture_tournament()
{

  $labels = array(
    'name'                       => _x('Tournament', 'Taxonomy General Name', 'moustache'),
    'singular_name'              => _x('Tournament', 'Taxonomy Singular Name', 'moustache'),
    'menu_name'                  => __('Tournament', 'moustache'),
    'all_items'                  => __('All Items', 'moustache'),
    'parent_item'                => __('Parent Item', 'moustache'),
    'parent_item_colon'          => __('Parent Item:', 'moustache'),
    'new_item_name'              => __('New Item Name', 'moustache'),
    'add_new_item'               => __('Add New Item', 'moustache'),
    'edit_item'                  => __('Edit Item', 'moustache'),
    'update_item'                => __('Update Item', 'moustache'),
    'view_item'                  => __('View Item', 'moustache'),
    'separate_items_with_commas' => __('Separate items with commas', 'moustache'),
    'add_or_remove_items'        => __('Add or remove items', 'moustache'),
    'choose_from_most_used'      => __('Choose from the most used', 'moustache'),
    'popular_items'              => __('Popular Items', 'moustache'),
    'search_items'               => __('Search Items', 'moustache'),
    'not_found'                  => __('Not Found', 'moustache'),
  );
  $rewrite = array(
    'slug'                       => __('tournament', 'moustache'),
    'with_front'                 => false,
    'hierarchical'               => true,
  );
  $args = array(
    'labels'                     => $labels,
    'hierarchical'               => true,
    'public'                     => true,
    'show_ui'                    => true,
    'show_admin_column'          => true,
    'show_in_nav_menus'          => false,
    'show_tagcloud'              => false,
    'rewrite'                    => $rewrite,
  );
  register_taxonomy('tournament', array('fixture'), $args);
}
add_action('init', 'fixture_tournament', 0);

// define('TAXONOMY_ARTICLE_CATEGORY', 'article-tax');

// add_action( 'init', function() {

// 		register_taxonomy(
// 			TAXONOMY_ARTICLE_CATEGORY, // The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
// 			POST_TYPE_ARTICLE, // post type name
// 			array(
// 				'hierarchical' => true,
// 				'label' => esc_html__('Categories', TRANSLATION_DOMAIN),  //Translated display name
// 				'query_var' => true,
// 				'rewrite' => array(
// 					//'slug' => dekode_get_custom_page( 'dekode-article-parent-page', 'full-slug' ) . '/kategori', // This controls the base slug that will display before each term
// 					'slug' => get_page_by_title('Sikker til sjøs')->post_name . '/kategori',
// 					'with_front' => false // Don't display the category base before
// 				)
// 			)
// 		);
// 	}
// );

// // Remove description and slug field from list view
// add_action('manage_edit-'.TAXONOMY_ARTICLE_CATEGORY.'_columns', function($header_text_columns) {
//     unset($header_text_columns['description']);
//     unset($header_text_columns['slug']);
// 	return $header_text_columns;
// });
