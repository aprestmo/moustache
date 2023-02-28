<?php
/**
 *
 * The archive page template file
 *
 * @package Moustache
 * @since 1.0
 * @version 2.0
 */

/*
 Template Name: Arkiv
 */

get_header(); ?>
  <section class="o-section-md">
    <?php // get_search_form(); ?>

    <h2>Archives by Month:</h2>
    <ul>
      <?php wp_get_archives('type=yearly', 'format=link'); ?>
    </ul>

    <h2>Archives by Subject:</h2>
    <ul>
        <?php wp_list_categories(); ?>
    </ul>
  </section>
<?php get_footer(); ?>
