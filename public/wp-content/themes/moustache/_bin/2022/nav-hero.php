
  <button class="c-header__toggle" type="button">
    <?php esc_html_e('Menu', 'moustache'); ?>
  </button>

  <button class="c-header__toggle" type="button">
    <?php esc_html_e('Search', 'moustache'); ?>
  </button>

  <div class="c-hero" style="background-image: url('<?php echo esc_attr(get_template_directory_uri()); ?>/dist/img/default-hero.jpg')"></div>

  <nav>
    <?php bem_menu('primary', 'c-nav', ''); ?>
  </nav>
