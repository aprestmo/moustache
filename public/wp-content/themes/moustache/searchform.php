<div id="js-search-overlay" class="c-search">
  <div class="mou-site-wrap">
    <div>
      <form class="c-search__items" role="search" method="get" action="<?php echo home_url('/'); ?>">
        <button class="c-search__close js-search-toggle" type="button">Lukk</button>
        <label class="u-visually-hidden" for="js-search-field"><?php esc_html_e('Search', 'moustache'); ?></label>
        <input id="js-search-field" class="c-search__input" name="s" type="search" placeholder="<?php esc_attr_e('E.g. wins, moustaches etc.', 'moustache'); ?>">
        <button class="c-search__button" type="submit"><?php esc_html_e('Search', 'moustache'); ?></button>
      </form>
    </div>
  </div>
</div>
