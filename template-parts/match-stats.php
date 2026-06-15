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
$match_reports = get_field('match_report');

if ($match_reports) :
	foreach ($match_reports as $post) :
		setup_postdata($post);
?>

		<header class="u-soft-bottom-md">
			<h4 class="u-flush-bottom"><?php opponents(); ?></h4>
			<?php date_time(); ?>
		</header>

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

<?php
	endforeach;
	wp_reset_postdata();
endif;
?>