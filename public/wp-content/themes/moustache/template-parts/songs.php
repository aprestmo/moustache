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

wp_enqueue_script(
	'moustache-audio-playlist',
	get_template_directory_uri() . '/js/audio-playlist.js',
	array(),
	'30012026-v2',
	true
);

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

if (have_rows('music')) :
	$playlist_array = array();
	while (have_rows('music')) :
		the_row();
		$title = get_sub_field('title');
		$audio = get_sub_field('audio_file');
		if ($title && $audio) {
			$playlist_array[] = array('title' => $title, 'url' => is_array($audio) ? ($audio['url'] ?? '') : $audio);
		}
	endwhile;
	?>
	<audio-playlist
		playlist='<?php echo esc_attr( json_encode( $playlist_array ) ); ?>'
	></audio-playlist>
	<div class="songs-container">
		<?php
		while (have_rows('music')) :
			the_row();

			$title = get_sub_field('title');
			$audio = get_sub_field('audio_file');
			$lyrics = get_sub_field('lyrics_file');

			if ($title && $audio) {
				display_song($title, is_array($audio) ? ($audio['url'] ?? '') : $audio, $lyrics);
			}
		endwhile;
		?>
	</div>
<?php endif; ?>