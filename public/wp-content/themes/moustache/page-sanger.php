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
							echo '<div>';
							while ( have_rows('music') ) : the_row();
								echo '<figure>';
									$title = get_sub_field('title');
									$audio = get_sub_field('audio_file');
									$lyrics = get_sub_field('lyrics_file');

									echo '<figcaption>' . $title . '</figcaption>';
									echo '<audio controls src="' . $audio .'">';
										echo '<a href="' . $audio . '">Last ned' . $title . '</a>';
									echo '</audio>';
									echo '<p><a href="' . $lyrics . '">Last ned teksten til «' . $title . '»</a></p>';
								echo '</figure>';
							endwhile;
							echo '</div>';
						endif;
					?>
				</section>
			</div>
		</div>
	</div>

<?php endwhile; ?>

<?php get_footer(); ?>
