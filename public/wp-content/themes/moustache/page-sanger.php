<?php
/* Template Name: Sanger */

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
					<?php the_content(); ?>
					<?php
						if ( have_rows('music') ):
							echo '<dl>';
							while ( have_rows('music') ) : the_row();

									$title = get_sub_field('title');
									$audio = get_sub_field('audio_file');
									$lyrics = get_sub_field('lyrics_file');

									var_dump($title);
									var_dump($audio);
									var_dump($lyrics);

									// if ( $winners ) {
									// 	foreach ($winners as $winner) {
									// 		echo '<dt>' . $year . '</dt>';
									// 		echo '<dd><a href="/spiller/' . $winner->post_name . '/">'. $winner->post_title . '</a></dd>';
									// 	}
									// }
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
