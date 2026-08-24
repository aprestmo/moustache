<search class="c-search">
	<form class="c-search__form" method="get" role="search" action="<?php echo esc_url(home_url('/')); ?>">
		<label class="u-visually-hidden" for="search-field"><?php esc_html_e('Search', 'moustache'); ?></label>
		<input id="search-field" class="c-search__input" name="s" type="search" placeholder="<?php esc_attr_e('E.g. wins, moustaches etc.', 'moustache'); ?>">
		<button class="c-search__button" type="submit"><?php esc_html_e('Search', 'moustache'); ?></button>
	</form>
</search>
