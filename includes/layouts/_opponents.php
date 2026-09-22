<?php

function opponents()
{
	$home_team = moustache_get_home_team();
	$away_team = moustache_get_away_team();

	if (!$home_team && !$away_team) {
		return;
	}

	echo $home_team ? esc_html(get_the_title($home_team)) : '';
	echo '–';
	echo $away_team ? esc_html(get_the_title($away_team)) : '';
}
