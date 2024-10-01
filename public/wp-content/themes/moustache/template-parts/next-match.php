<?php

// This is now
$now = date('Y-m-d H:i:s');

// Setup a loop to get some info about next match

$args = array(
	'post_type' => 'fixture',
	'post_status' => 'publish',
	'posts_per_page' => -1,
	'meta_key' => 'date_time',
	'orderby' => 'meta_value',
	'order' => 'ASC',
	'meta_query' => array(
		array(
			'key' => 'date_time',
			'compare' => '>',
			'value' => $now,
		)
	)
);

$next_match = get_posts($args);

if ($next_match) :
	foreach ($next_match as $post) : setup_postdata($post);

		$canceled = get_field('canceled');

		if ($canceled == 'match_abandoned' || $canceled == 'match_canceled' || $canceled == 'match_abandonded') {
			continue;
		}

		// Get the match facts
		$home_team = get_field('home_team');
		$away_team = get_field('away_team');
		$when = get_field('date_time');
		$pitch = get_field('pitch');

		if (is_array($pitch) && !empty($pitch)) {
			if (is_object($pitch[0])) {
				$ground = $pitch[0]->post_title;
			} elseif (is_array($pitch[0]) && isset($pitch[0]['post_title'])) {
				$ground = $pitch[0]['post_title'];
			} else {
				$ground = ''; // Eller en standardverdi hvis ingen gyldig verdi blir funnet
			}
		} else {
			$ground = ''; // Eller en standardverdi hvis $pitch er tom eller ikke en array
		}

		foreach ($home_team as $hosts) {
			$hosts = $hosts->post_title;
		}

		foreach ($away_team as $guests) {
			$guests = $guests->post_title;
		}

		// Get date and time for match
		$datetime = new DateTime($when);

		// Do some splitting
		$timestamp = strtotime($when);
		$weekday = date_i18n('l j.', $timestamp);
		$month = date_i18n('F', $timestamp);
		$time = date_i18n('H.i', $timestamp);
?>

		<div class="c-upcoming">
			<div class="mou-site-wrap mou-site-wrap--padding">
				<div class="o-grid c-upcoming__item">
					<p class="o-grid__item u-flush-bottom u-text-center"><img class="c-upcoming__icon" src="<?php echo esc_url(get_template_directory_uri()); ?>/dist/icons/ball.svg" alt="" width="18" height="18"><b><?php esc_html_e('Next match:', 'moustache'); ?></b> <?php echo esc_html($hosts); ?>&ndash;<?php echo esc_html($guests); ?>. <?php echo esc_html(ucfirst($weekday)); ?> <?php echo esc_html($month); ?> <?php esc_html_e('at', 'moustache'); ?> <?php echo esc_html($time); ?>, <?php echo esc_html($ground); ?>.</p>
				</div>
			</div>
		</div>

<?php
		break; // Stopp løkken etter å ha funnet det første gyldige innlegget
	endforeach;
	wp_reset_postdata();
endif;
?>