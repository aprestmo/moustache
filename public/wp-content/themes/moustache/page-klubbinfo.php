<?php
/* Template Name: Klubbinfo */

get_header(); ?>

<?php
while (have_posts()) :
	the_post();
?>

	<div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
		<div class="o-grid u-text-center">
			<div class="o-grid__item u-1/1 u-2/3@sm">
				<section class="o-section-md u-flow">
					<h1><?php the_title(); ?></h1>
					<dl>
						<dt>Stitet:</dt>
						<dd>
							<?php the_field('founded'); ?>
						</dd>
						<dt>Postadresse:</dt>
						<dd></dd>
						<dt>Kontonummer:</dt>
						<dd>
							<?php the_field('bank_account'); ?>
						</dd>
						<dt>Drakter:</dt>
						<dd>
							<?php the_field('kit'); ?>
						</dd>
					</dl>
					<?php the_content(); ?>
					<?php if (the_field('gullbart')) : ?>
						<dl>
						<?php while( have_rows('gullbart') ) : the_row(); ?>
							<dt><?php the_field('gullbart_year'); ?></dt>
							<dd><?php the_field('gullbart_winner'); ?></dd>
						<?php endwhile; ?>
						</dl>
					<?php endif; ?>
				</section>
			</div>
		</div>
	</div>

<?php endwhile; ?>

<?php get_footer(); ?>
