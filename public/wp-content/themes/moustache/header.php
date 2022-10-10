<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?= vite('main.js') ?>
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

  <!-- Skip link -->
  <?php get_template_part('partials/skip-link'); ?>

  <header>
    <?php echo is_home() ? '<h1>' . get_bloginfo('title') . '</h1>' : '<p><a href='. get_home_url() .'>' . get_bloginfo('title') . '</a></p>' ?>

    <?php // get_template_part('partials/navigation'); ?>
  </header>
