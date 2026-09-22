<?php

/**
 * Template part for displaying match statistics
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

global $post;
$match_reports = moustache_acf_posts(get_field('match_report'));

if ($match_reports) :
	foreach ($match_reports as $post) :
		setup_postdata($post);
		$unplayed_reason = moustache_fixture_unplayed_reason(get_the_ID());
?>

		<header class="u-soft-bottom-md">
			<h4 class="u-flush-bottom"><?php opponents(); ?></h4>
			<?php date_time(); ?>
		</header>

		<?php if ($unplayed_reason) : ?>
			<p><?php echo esc_html(moustache_fixture_unplayed_label($unplayed_reason)); ?></p>
		<?php else : ?>
		<?php
		weather();
		attendance();
		scores();
		?>

		<hr>

		<div>
			<?php cards(); ?>
		</div>
		<div>
			<?php present(); ?>
		</div>
		<?php endif; ?>

<?php
	endforeach;
	wp_reset_postdata();
endif;
?>