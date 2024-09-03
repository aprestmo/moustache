<div id="search" class="c-search" popover>
	<div class="mou-site-wrap c-search__items">
		<button class=" c-search__close" popovertarget="search">Lukk</button>
		<form class="c-search__form" method="get" action="<?php echo home_url('/'); ?>">
			<label class="u-visually-hidden" for="search-field"><?php esc_html_e('Search', 'moustache'); ?></label>
			<input id="search-field" class="c-search__input" name="s" type="search" placeholder="<?php esc_attr_e('E.g. wins, moustaches etc.', 'moustache'); ?>">
			<button class="c-search__button" type="submit"><?php esc_html_e('Search', 'moustache'); ?></button>
		</form>
	</div>
</div>