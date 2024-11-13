<?php

/**
 * Template part for displaying songs
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Display song item
 *
 * @param string $title Song title
 * @param string $audio Audio file URL
 * @param string|null $lyrics Lyrics file URL
 * @return void
 */
function display_song(string $title, string $audio, ?string $lyrics): void
{
?>
	<figure>
		<figcaption><?php echo esc_html($title); ?></figcaption>
		<audio controls style="inline-size: 100%">
			<source src="<?php echo esc_url($audio); ?>" type="audio/mpeg">
			<a href="<?php echo esc_url($audio); ?>">
				<?php
				printf(
					esc_html__('Download "%s"', 'moustache'),
					esc_html($title)
				);
				?>
			</a>
		</audio>
		<?php if ($lyrics) : ?>
			<p>
				<a href="<?php echo esc_url($lyrics); ?>">
					<?php
					printf(
						esc_html__('Download lyrics for "%s"', 'moustache'),
						esc_html($title)
					);
					?>
				</a>
			</p>
		<?php endif; ?>
	</figure>
<?php
}

if (have_rows('music')) : ?>
	<div class="songs-container">
		<?php
		while (have_rows('music')) :
			the_row();

			$title = get_sub_field('title');
			$audio = get_sub_field('audio_file');
			$lyrics = get_sub_field('lyrics_file');

			if ($title && $audio) {
				display_song($title, $audio, $lyrics);
			}
		endwhile;
		?>
	</div>
<?php endif; ?>