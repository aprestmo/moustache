<?php

function present()
{
	$present = moustache_get_present();

	if ($present === []) {
		return;
	}

	echo '<strong>';
	esc_html_e('Present', 'moustache');
	echo '</strong>';
	echo '<ul>';

	foreach ($present as $player) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url(get_permalink($player)),
			esc_html(get_the_title($player))
		);
	}

	echo '</ul>';
}
