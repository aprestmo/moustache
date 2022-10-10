<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @package Moustache
 * @since 1.0
 * @version 1.0
 */

?></main>

<footer class="c-footer">
  <img class="c-footer__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/svg/kampbart-logo.svg'); ?>" alt="">
  <?php echo esc_html(bloginfo('description')); ?>
  <?php echo esc_html(get_bloginfo('name')); ?>
  <?php esc_html_e('Founded 2003', 'moustache'); ?>
  <?php esc_html_e('Copyright', 'moustache'); ?> &copy; <?php echo esc_html(get_bloginfo('name')); ?> &ndash; <?php echo esc_html(date('Y')); ?>
</footer>

<?php // get_search_form(); ?>

<?php wp_footer(); ?>
</body>
</html>
