<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
<div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
	<div class="o-grid u-text-center">
		<div class="o-grid__item u-1/1 u-2/3@sm">
			<section class="o-section-md u-flow">
				<h1><?php the_title(); ?></h1>
				<?php the_content(); ?>
				<?php get_template_part( 'template-parts/songs', 'page' ); ?>
			</section>
		</div>
	</div>
</div>
<?php endwhile; ?>

<?php get_footer(); ?>
