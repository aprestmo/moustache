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
			<h1>Kamper på <?php the_title(); ?></h1>
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
			<table>
				<?php if ($fixtures_query->have_posts()) : ?>
					<thead>
						<tr>
							<th>Dato</th>
							<th>Kamp</th>
						</tr>
					</thead>
					<tbody>
						<?php
						while ($fixtures_query->have_posts()) :
							$fixtures_query->the_post();
							$fixture_id = get_the_ID(); // Get the current fixture ID
							$date_time = get_field('date_time');

							// Check if the fixture date is in the past
							if ($date_time && strtotime($date_time) < time()) {
						?>
								<tr>
									<td>
										<?php
										$formatted_datetime = wp_date('j. F Y', strtotime($date_time));
										echo esc_html($formatted_datetime);
										?>
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
		<?php endif; ?>
		</div>
	</div>
</article>

<?php wp_reset_postdata(); ?>

<?php get_footer(); ?>