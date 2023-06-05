<?php
/**
 * Single pitch template
 *
 * @since 6.0.0
 */
get_header(); ?>

<?php while (have_posts()) : the_post(); ?>

<div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
	<div class="o-grid u-text-center">
			<div class="o-grid__item u-1/1">
				<section class="o-section-md">
					<h1><?php the_title(); ?></h1>
					<?php
						$image = get_field('image');
						$size = 'medium';
					if ($image) : ?>
					<img src="<?php echo $image['sizes'][$size]; ?>" alt="<?php _e('Image from ', 'moustache'); the_title(); ?>">
					<?php endif; ?>

					<?php
						$map = get_field('address');
						$address = $map['address'];
					?>
					<div class="acf-map" data-zoom="17">
						<div class="marker" data-lat="<?php echo esc_attr($map['lat']); ?>" data-lng="<?php echo esc_attr($map['lng']); ?>"></div>
					</div>
					<p><?php esc_html_e($address); ?></p>

					<div>
					<?php
						$field = get_field_object('surface');
						$value = get_field('surface');
					?>
						<dl>
							<dt><?php esc_html_e('Surface', 'moustache'); ?>:</dt>
							<dd><?php esc_html_e($field['choices'][$value]); ?></dd>
						</dl>
					</div>
				</section>
			</div>
		</div>
	</div>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>
