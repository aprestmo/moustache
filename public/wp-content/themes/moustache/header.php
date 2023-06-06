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
  <?= vite('main.js') ?>
  <?php wp_head(); ?>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBpX2xs6qaBOTQmn0VB7IiHd0vmRatZz00"></script>

</head>

<body <?php body_class(); ?>>

  <header class="c-header" role="banner">

    <div class="mou-site-wrap mou-site-wrap--padding u-1/1">
      <div class="o-grid">
        <div class="o-grid__item c-header__brand">
          <div class="c-brand__logo">
            <?php if (is_front_page()) : ?>
              <?php if ('12' === date('m')) : ?>
                <img class="c-header__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/kampbart-logo-jul.svg'); ?>" alt="">
              <?php else : ?>
                <img class="c-header__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/kampbart-logo-party.png'); ?>" alt="">
              <?php endif; ?>
            <?php else : ?>
              <a href="<?php echo esc_attr(home_url()); ?>">
                <?php if ('12' === date('m')) : ?>
                  <img class="c-header__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/kampbart-logo-jul.svg'); ?>" alt="">
                <?php else : ?>
                  <img class="c-header__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/kampbart-logo-party.png'); ?>" alt="">
                <?php endif; ?>
              </a>
            <?php endif; ?>
          </div>

          <div class="c-brand__text">
            <?php if (is_front_page()) : ?>
              <h1 class="c-brand__title"><?php echo esc_html(get_bloginfo('name')); ?></h1>
            <?php else : ?>
              <p class="c-brand__title"><a class="c-header__link" href="<?php echo esc_attr(home_url()); ?>" title=""><?php echo esc_html(get_bloginfo('name')); ?></a></p>
            <?php endif; ?>
            <span class="u-visually-hidden"><?php echo esc_html(bloginfo('description')); ?></span>
            <img class="c-brand__slogan" src="<?php echo esc_url(get_template_directory_uri() . '/dist/slogan.svg'); ?>" alt="">
          </div>
        </div>
      </div>
    </div>

    <div class="mou-site-wrap mou-site-wrap--padding u-1/1 c-header__actions">
      <button id="js-menu-toggle--open" class="c-header__toggle" type="button">
        <?php esc_html_e('Menu', 'moustache'); ?>
      </button>

      <div class="c-header__search">
        <button class="c-header__toggle js-search-toggle" type="button"><?php esc_html_e('Search', 'moustache'); ?></button>
      </div>
    </div>

    <div class="c-hero" style="background-image: url('<?php echo esc_attr(get_template_directory_uri()); ?>/dist/default-hero.jpg')">
    </div>

    <div id="js-site-navigation" class="c-navigation">
      <nav class="mou-site-wrap mou-site-wrap--padding c-navigation__item" role="navigation">
        <button id="js-menu-toggle--close" class="c-header__toggle" type="button">
          <?php esc_html_e('Close', 'moustache'); ?>
        </button>
        <?php bem_menu('primary', 'c-nav', ''); ?>
      </nav>
    </div>

    <?php if (is_home()) : ?>
      <?php include get_template_directory() . '/template-parts/next-match.php'; ?>
    <?php endif; ?>

  </header>

  <main class="<?php echo esc_attr(is_home() ? 'c-site--bgcolor' : 'c-site'); ?>" role="main">
