<?php

function date_time()
{
	$matchday = moustache_get_fixture_datetime();

	if ($matchday === '') {
		return;
	}

	$timestamp = strtotime($matchday);
	$machineDay = date('Y-m-d', $timestamp);
	$machineTime = date('H:i', $timestamp);
	$day = date_i18n('d. F Y', $timestamp);
	$time = date_i18n('H.i', $timestamp);

	echo '<p>';
	echo '<time datetime="' . esc_attr($machineDay . 'T' . $machineTime) . '">';
	echo esc_html($day . ' ' . __('at', 'moustache') . ' ' . $time);
	echo '</time>';
	echo '</p>';
}
