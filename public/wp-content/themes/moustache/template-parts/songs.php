<?php
	if ( have_rows('music') ):
		echo '<div>';
		while ( have_rows('music') ) : the_row();
			echo '<figure>';
				$title = get_sub_field('title');
				$audio = get_sub_field('audio_file');
				$lyrics = get_sub_field('lyrics_file');

				echo '<figcaption>' . $title . '</figcaption>';
				echo '<audio controls src="' . $audio .'" style="inline-size: 100%">';
					echo '<a href="' . $audio . '">Last ned' . $title . '</a>';
				echo '</audio>';
				if ($lyrics) {
					echo '<p><a href="' . $lyrics . '">Last ned teksten til «' . $title . '»</a></p>';
				}
			echo '</figure>';
		endwhile;
		echo '</div>';
	endif;
?>
