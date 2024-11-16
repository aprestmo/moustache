<?php

/**
 * Display cards for a match
 *
 * @package Moustache
 */

/**
 * Display cards for a specific half
 *
 * @param string $half Which half ('first' or 'second')
 * @return void
 */
function display_half_cards(string $half): void
{
	$field_name = "cards_{$half}_half";
	$player_field = "card_player_{$half}_half";
	$colour_field = "card_colour_{$half}_half";

	if (have_rows($field_name)) :
?>
		<strong>
			<?php
			printf(
				esc_html__('Card in %s half', 'moustache'),
				$half === 'first' ? '1st' : '2nd'
			);
			?>
		</strong>
		<ul class="c-report u-soft-bottom-md">
			<?php
			while (have_rows($field_name)) :
				the_row();
				$player = get_sub_field($player_field);
				$colour = get_sub_field($colour_field);

				if ($player) : ?>
					<li data-card="<?php echo esc_attr($colour); ?>">
						<a href="<?php echo esc_url(get_permalink($player->ID)); ?>">
							<?php echo esc_html($player->post_title); ?>
						</a>
					</li>
			<?php
				endif;
			endwhile;
			?>
		</ul>
	<?php
	endif;
}

/**
 * Display cards from the 'cards' field
 *
 * @return void
 */
function display_additional_cards(): void
{
	if (have_rows('cards')) :
	?>
		<strong><?php esc_html_e('Cards', 'moustache'); ?></strong>
		<ul class="c-report u-soft-bottom-md">
			<?php
			while (have_rows('cards')) :
				the_row();
				$player = get_sub_field('card_player');
				$colour = get_sub_field('card_colour');

				if ($player) : ?>
					<li data-card="<?php echo esc_attr($colour); ?>">
						<a href="<?php echo esc_url(get_permalink($player->ID)); ?>">
							<?php echo esc_html($player->post_title); ?>
						</a>
					</li>
			<?php
				endif;
			endwhile;
			?>
		</ul>
<?php
	endif;
}

/**
 * Display all cards for a match
 *
 * @return void
 */
function cards(): void
{
	// Sjekk om det finnes data i 'cards' feltet
	if (have_rows('cards')) {
		display_additional_cards();
	} else {
		// Hvis ikke, vis kort fra første og andre omgang
		display_half_cards('first');
		display_half_cards('second');
	}
}
