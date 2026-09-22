<?php

/**
 * The template for displaying all single pitch posts
 *
 * @package Moustache
 *
 * @since 6.0.0
 */

get_header();

// Get the current club ID
$club_id = get_the_ID();

// Query for fixtures related to this club as either home or away team
$args = array(
	'post_type'      => 'fixture',
	'posts_per_page' => -1,
	'meta_query'     => array(
		array(
			'key'     => 'pitch', // ACF field name for home team
			'value'   => $club_id,
			'compare' => 'LIKE',
		),
	),
	'orderby'        => 'meta_value',
	'meta_key'       => 'date_time', // ACF field name for datetime
	'order'          => 'ASC',
);

$fixtures_query = new WP_Query($args);
?>

<article class="mou-site-wrap mou-site-wrap--padding wysiwyg">
	<div class="o-grid o-section-md">
		<div class="o-grid__item">
			<h1><?php printf(esc_html__('Matches at %s', 'moustache'), get_the_title()); ?></h1>
			<?php
			$image = get_field('image');
			$size = 'medium';
			if ($image) : ?>
				<img src="<?php echo $image['sizes'][$size]; ?>" alt="<?php _e('Image from ', 'moustache');
																		the_title(); ?>">
			<?php endif; ?>

			<?php
			$map = get_field('address');
			$address = $map['address'];
			?>
			<div class="acf-map" data-zoom="17">
				<div class="marker" data-lat="<?php echo esc_attr($map['lat']); ?>" data-lng="<?php echo esc_attr($map['lng']); ?>"></div>
			</div>
			<p><?php echo esc_html($address); ?></p>

			<?php
			$field = get_field_object('surface');
			$value = get_field('surface');
			?>
			<dl class="u-hard-bottom">
				<dt><?php esc_html_e('Surface', 'moustache'); ?>:</dt>
				<dd class="u-flush-left"><?php esc_html_e($field['choices'][$value], 'moustache'); ?></dd>
			</dl>
			<div class="table-scroll" role="region" aria-labelledby="caption" tabindex="0">
				<table>
					<?php if ($fixtures_query->have_posts()) : ?>
						<thead>
							<tr>
								<th><?php esc_html_e('Date', 'moustache'); ?></th>
								<th><?php esc_html_e('Match', 'moustache'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							while ($fixtures_query->have_posts()) :
								$fixtures_query->the_post();
								$fixture_id = get_the_ID(); // Get the current fixture ID
								$date_time = get_field('date_time');

								// Check if the fixture date is in the past
								$fixture_ts = moustache_acf_datetime_timestamp($date_time);
								if ($fixture_ts && $fixture_ts < time()) {
							?>
									<tr>
										<td>
											<?php echo esc_html(moustache_format_acf_datetime($date_time, 'j. F Y')); ?>
										</td>
										<td>
											<?php
											// Query for related posts for the current fixture ID
											$related_posts_query = new WP_Query(array(
												'post_type'      => 'post',
												'posts_per_page' => -1,
												'meta_query'     => array(
													array(
														'key'     => 'match_report', // ACF relationship field name
														'value'   => $fixture_id,
														'compare' => 'LIKE',
													),
												),
											));

											if ($related_posts_query->have_posts()) :
												while ($related_posts_query->have_posts()) : $related_posts_query->the_post(); ?>
													<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
											<?php endwhile;
											else :
												echo the_title();
											endif;

											wp_reset_postdata();
											?>
										</td>
									</tr>
							<?php
								}
							endwhile;
							?>
						</tbody>
				</table>
			</div>
		<?php endif; ?>
		</div>
	</div>
</article>

<?php wp_reset_postdata(); ?>

<?php get_footer(); ?>