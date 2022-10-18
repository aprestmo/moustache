<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <main>
 *
 * @package Moustache
 * @since 1.0
 * @version 1.0
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">

<head>
  <meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>

  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBpX2xs6qaBOTQmn0VB7IiHd0vmRatZz00"></script>

</head>

<body <?php body_class(); ?>>

  <header class="c-header">

  <?php if (is_front_page()) : ?>
    <!-- Inline svg og vis g/path basert på måned -->
    <pre><?php var_dump(date('n')); ?></pre>
    <?php if ('12' === date('n')) : ?>
      <img class="c-header__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/svg/kampbart-logo-jul.svg'); ?>" alt="" width="200">
    <?php else : ?>
      <img class="c-header__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/svg/kampbart-logo.svg'); ?>" alt="" width="200">
    <?php endif; ?>


  <?php else : ?>
    <a href="<?php echo esc_attr(home_url()); ?>">
      <?php if ('12' === date('m')) : ?>
        <img class="c-header__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/svg/kampbart-logo-jul.svg'); ?>" alt="">
      <?php else : ?>
        <img class="c-header__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/svg/kampbart-logo.svg'); ?>" alt="">
      <?php endif; ?>
    </a>
  <?php endif; ?>

  <?php if (is_front_page()) : ?>
    <h1 class="c-brand__title"><?php echo esc_html(get_bloginfo('name')); ?></h1>
  <?php else : ?>
    <p class="c-brand__title"><a class="c-header__link" href="<?php echo esc_attr(home_url()); ?>" title=""><?php echo esc_html(get_bloginfo('name')); ?></a></p>
  <?php endif; ?>

  <?php echo esc_html(bloginfo('description')); ?>

  <?php if (is_home()) : ?>
    <?php include get_template_directory() . '/template-parts/next-match.php'; ?>
  <?php endif; ?>

  </header>

  <main>
