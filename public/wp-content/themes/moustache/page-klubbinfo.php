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
						<dt><?php esc_html_e('Founded', 'moustache'); ?>:</dt>
						<dd>
							<?php esc_html_e(get_field('founded')); ?>
						</dd>
						<dt><?php esc_html_e('Address', 'moustache'); ?>:</dt>
						<?php
							if ( have_rows('contact_info') ) :
								while ( have_rows('contact_info') ) : the_row();
							?>
								<dd><?php esc_html_e(get_sub_field('name')); ?><br><?php esc_html_e(get_sub_field('address')); ?> <?php esc_html_e(get_sub_field('zip')); ?><br><?php esc_html_e(get_sub_field('place')); ?></dd>
						<?php
								endwhile;
							endif;
						?>
						<dt><?php esc_html_e('Account number', 'moustache'); ?>:</dt>
						<dd>
							<?php esc_html_e(get_field('bank_account')); ?>
						</dd>
						<dt><?php esc_html_e('Kit', 'moustache'); ?>:</dt>
						<dd>
							<?php esc_html_e(get_field('kit')); ?>
						</dd>
					</dl>
					<?php the_content(); ?>
					<?php
						if ( have_rows('gullbart') ):
							echo '<h3>Vinnere av gullbarten</h3>';
							echo '<dl>';
							while ( have_rows('gullbart') ) : the_row();

									$year = get_sub_field('gullbart_year');
									$winners = get_sub_field('gullbart_winner');

									if ( $winners ) {
										foreach ($winners as $winner) {
											echo '<dt>' . $year . '</dt>';
											echo '<dd><a href="/spiller/' . $winner->post_name . '/">'. $winner->post_title . '</a></dd>';
										}
									}
							endwhile;
							echo '</dl>';
						endif;
					?>
				</section>
			</div>
		</div>
	</div>

<?php endwhile; ?>

<?php get_footer(); ?>
