<?php

/**
 * The archive page template file
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

get_header();

/**
 * Get all posts
 *
 * @return WP_Query
 */
function get_archive_posts(): WP_Query
{
	return new WP_Query([
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	]);
}
?>

<div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
	<div class="o-grid">
		<div class="o-grid__item">
			<section class="o-section-md">
				<header>
					<h1><?php esc_html_e('Arkiv', 'moustache'); ?></h1>
				</header>

				<?php
				$archive_query = get_archive_posts();

				if ($archive_query->have_posts()) :
					$current_year = '';

					while ($archive_query->have_posts()) :
						$archive_query->the_post();

						$year = get_the_date('Y');
						$categories = get_the_category();
						$category_name = $categories ? $categories[0]->name : '';

						// Vis årstall når det endrer seg
						if ($year !== $current_year) {
							if ($current_year !== '') {
								echo '</ol>'; // Lukk forrige års liste
							}
							echo '<h2>' . esc_html($year) . '</h2>';
							echo '<ol style="list-style: none">'; // Start ny liste for nytt år
							$current_year = $year;
						}
				?>
						<li>
							<a href="<?php echo esc_url(get_permalink()); ?>">
								<?php echo esc_html(get_the_title()); ?>
							</a>
							<time datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>" style="opacity: 50%">
								(<?php echo esc_html(get_the_date('d.m.Y')); ?>
								<?php if ($category_name) : ?> &ndash; <?php echo esc_html($category_name); ?><?php endif; ?>)
							</time>
						</li>
					<?php
					endwhile;

					if ($current_year !== '') {
						echo '</ol>'; // Lukk siste liste
					}

					wp_reset_postdata();
				else : ?>
					<p><?php esc_html_e('Ingen innlegg funnet.', 'moustache'); ?></p>
				<?php endif; ?>
			</section>
		</div>
	</div>
</div>

<?php
get_footer();
