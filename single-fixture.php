<?php get_header(); ?>

<?php
while (have_posts()) :
	the_post();
	$unplayed_reason = moustache_fixture_unplayed_reason(get_the_ID());
?>
	<article class="mou-site-wrap mou-site-wrap--padding wysiwyg">
		<div class="o-grid o-section-md">
			<div class="o-grid__item">
				<h1><?php opponents(); ?></h1>
				<?php date_time(); ?>

				<?php if ($unplayed_reason) : ?>
					<p><?php echo esc_html(moustache_fixture_unplayed_label($unplayed_reason)); ?></p>
				<?php else : ?>
					<?php
					weather();
					attendance();
					scores();
					cards();
					present();
					?>
				<?php endif; ?>
			</div>
		</div>
	</article>
<?php endwhile; ?>

<?php get_footer();