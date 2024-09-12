<?php get_header(); ?>


<?php if (strlen(get_field('404', 'option'))) : ?>

	<p><?php echo get_field('404', 'option'); ?></p>
<?php else : ?>

	<h1><?php esc_html_e('Oops! That page can&rsquo;t be found.', 'moustache'); ?></h1>
	<p><?php esc_html_e('It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'moustache'); ?></p>
<?php endif; ?>


<?php get_footer(); ?>
