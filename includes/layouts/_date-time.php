<?php

function date_time()
{
	$matchday = moustache_get_fixture_datetime();

	if ($matchday === '') {
		return;
	}

	$machineDay = moustache_format_acf_datetime($matchday, 'Y-m-d');
	$machineTime = moustache_format_acf_datetime($matchday, 'H:i');
	$day = moustache_format_acf_datetime($matchday, 'd. F Y');
	$time = moustache_format_acf_datetime($matchday, 'H.i');

	echo '<p>';
	echo '<time datetime="' . esc_attr($machineDay . 'T' . $machineTime) . '">';
	echo esc_html($day . ' ' . __('at', 'moustache') . ' ' . $time);
	echo '</time>';
	echo '</p>';
}
