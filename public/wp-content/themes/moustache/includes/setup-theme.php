<?php
defined('ABSPATH') || die('Shame on you');

/*
 * Sets up theme defaults and registers support for various WordPress features.
 *
 */
add_action('after_setup_theme', function () {

  /*
	 * Make theme available for translation.
	 *
	 */
  load_theme_textdomain(TRANSLATION_DOMAIN, get_template_directory() . '/languages');

  // Set up nav menu
  register_nav_menus(array(
    'primary' => esc_html__('Primary Menu', TRANSLATION_DOMAIN),
  ));

  /*
	 * This feature enables plugins and themes to manage the document title tag.
	 * This should be used in place of wp_title() function.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Title_Tag
	 *
	 */
  add_theme_support('title-tag');

  /*
	 * This feature allows the use of HTML5 markup for the search forms, comment forms, comment lists, gallery, and caption.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#HTML5
	 *
	 */
  add_theme_support('html5', array(
    'search-form',
    'comment-form',
    'comment-list',
    'gallery',
    'caption'
  ));

  /*
	 * This feature enables Automatic Feed Links for post and comment in the head.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Feed_Links
	 */
  //add_theme_support( 'automatic-feed-links' );

  /*
	 * This feature enables Post Thumbnails support for a Theme.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 *
	 */
  add_theme_support('post-thumbnails');
  add_image_size('reports-front', 600, 400, array('center', 'top'));
});
