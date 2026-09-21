<?php

/**
 * Template Name: Club info
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

get_header();

/**
 * Display contact information
 *
 * @return void
 */
function display_contact_info(): void
{
	$source = moustache_club_info_id();
	if (have_rows('contact_info', $source)) :
		while (have_rows('contact_info', $source)) :
			the_row();
?>
			<dd>
				<?php
				echo esc_html(get_sub_field('name')) . '<br>' .
					esc_html(get_sub_field('address')) . ' ' .
					esc_html(get_sub_field('zip')) . '<br>' .
					esc_html(get_sub_field('place'));
				?>
			</dd>
		<?php
		endwhile;
	endif;
}

/**
 * Display Gullbart winners
 *
 * @return void
 */
function display_gullbart_winners(): void
{
	$source = moustache_club_info_id();
	if (have_rows('gullbart', $source)):
		?>
		<h3><?php esc_html_e('Winners of the golden moustache', 'moustache'); ?></h3>
		<dl>
			<?php
			while (have_rows('gullbart', $source)) :
				the_row();
				$year = get_sub_field('gullbart_year');
				$winners = moustache_acf_posts(get_sub_field('gullbart_winner'));

				if ($winners) {
					foreach ($winners as $winner) {
			?>
						<dt><?php echo esc_html($year); ?></dt>
						<dd>
							<a href="<?php echo esc_url(get_permalink($winner->ID)); ?>">
								<?php echo esc_html($winner->post_title); ?>
							</a>
						</dd>
			<?php
					}
				}
			endwhile;
			?>
		</dl>
	<?php
	endif;
}

while (have_posts()) :
	the_post();
	?>
	<div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
		<div class="o-grid u-text-center">
			<div class="o-grid__item u-1/1 u-2/3@sm">
				<section class="o-section-md u-flow">
					<h1><?php the_title(); ?></h1>

					<dl>
						<dt><?php esc_html_e('Founded', 'moustache'); ?>:</dt>
						<dd><?php echo esc_html(moustache_format_acf_date(get_field('founded', moustache_club_info_id()))); ?></dd>

						<dt><?php esc_html_e('Address', 'moustache'); ?>:</dt>
						<?php display_contact_info(); ?>

						<dt><?php esc_html_e('Account number', 'moustache'); ?>:</dt>
						<dd><?php echo esc_html((string) get_field('bank_account', moustache_club_info_id())); ?></dd>

						<dt><?php esc_html_e('Kit', 'moustache'); ?>:</dt>
						<dd><?php echo esc_html((string) get_field('kit', moustache_club_info_id())); ?></dd>
					</dl>

					<?php
					the_content();
					display_gullbart_winners();
					?>
				</section>
			</div>
		</div>
	</div>
<?php
endwhile;

get_footer();
