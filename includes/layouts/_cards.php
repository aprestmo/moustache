<?php

/**
 * Display cards for a match
 *
 * @package Moustache
 */

function cards(): void
{
	$cards = moustache_get_cards();
	if ($cards === []) {
		return;
	}

	$by_half = [
		'first' => [],
		'second' => [],
		'unknown' => [],
	];

	foreach ($cards as $card) {
		if (!$card['player']) {
			continue;
		}
		$by_half[$card['half']][] = $card;
	}

	$labels = [
		'first' => sprintf(esc_html__('Card in %s half', 'moustache'), esc_html__('1st', 'moustache')),
		'second' => sprintf(esc_html__('Card in %s half', 'moustache'), esc_html__('2nd', 'moustache')),
		'unknown' => esc_html__('Cards', 'moustache'),
	];

	foreach ($by_half as $half => $half_cards) {
		if ($half_cards === []) {
			continue;
		}
		?>
		<strong><?php echo $labels[$half]; ?></strong>
		<ul class="c-report u-soft-bottom-md">
			<?php foreach ($half_cards as $card) : ?>
				<li data-card="<?php echo esc_attr($card['colour']); ?>">
					<a href="<?php echo esc_url(get_permalink($card['player'])); ?>">
						<?php echo esc_html($card['player']->post_title); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}
}
