<?php
/**
 * Pitch overview template
 *
 * TODO: Design and map
 */
get_header(); ?>


<?php
$posts = get_posts(
	array(
		'numberposts' => -1,
		'post_type' => 'pitch',
		'orderby' => 'title',
		'order' => 'ASC',
	)
);

if ($posts) { ?>
<section class="mou-site-wrap mou-site-wrap--padding wysiwyg u-soft-top-md u-push-bottom-lg">
	<div>
	<?php foreach ($posts as $post)	: ?>
		<div class="o-grid__item u-1/2 u-1/4@md">
			<h2><?php the_title(); ?></h2>
			<?php if (get_field('surface')) {
				$field = get_field_object('surface');
				$value = get_field('surface');
				$label = $field['choices'][$value]; ?>
				<dl>
					<dt><?php esc_html_e('Surface', 'moustache'); ?></dt>
					<dd><?php esc_html_e($label); ?></dd>
				</dl>
			<?php } ?>

			<?php /*
			<?php if (get_field('image')) :
				$image = get_field('image');
			?>
				<img src="<?php esc_attr_e($image); ?>" alt="">
			<?php endif; ?>
			*/ ?>

			<?php if (get_field('address')) : $map = get_field('address'); ?>
				<p><?php echo $map['address']; ?></p>
					<?php /*
					<div class="acf-map" data-zoom="24">
					<div class="marker" data-lat="<?php echo esc_attr($map['lat']); ?>" data-lng="<?php echo esc_attr($map['lng']); ?>">
				</div>
				*/ ?>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
	</div>
<?php } ?>
</section>

<?php get_footer(); ?>
