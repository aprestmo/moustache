<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <main>
 *
 * @package Moustache
 * @since 1.0
 * @version 1.0
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBpX2xs6qaBOTQmn0VB7IiHd0vmRatZz00"></script>

</head>

<body <?php body_class(); ?>>

	<header class="c-header" role="banner">

		<div class="mou-site-wrap mou-site-wrap--padding u-1/1">
			<div class="o-grid">
				<div class="o-grid__item c-header__brand">
					<div class="c-brand__logo">
						<?php if (is_front_page()) : ?>
							<?php if ('12' === date('m')) : ?>
								<img class="c-header__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/kampbart-logo-jul.svg'); ?>" alt="">
							<?php else : ?>
								<img class="c-header__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/kampbart-logo.svg'); ?>" alt="" width="100">
							<?php endif; ?>
						<?php else : ?>
							<a href="<?php echo esc_attr(home_url()); ?>">
								<?php if ('12' === date('m')) : ?>
									<img class="c-header__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/kampbart-logo-jul.svg'); ?>" alt="">
								<?php else : ?>
									<img class="c-header__logo" src="<?php echo esc_url(get_template_directory_uri() . '/dist/kampbart-logo.svg'); ?>" alt="" width="100">
								<?php endif; ?>
							</a>
						<?php endif; ?>
					</div>

					<div class="c-brand__text">
						<?php if (is_front_page()) : ?>
							<h1 class="c-brand__title"><?php echo esc_html(get_bloginfo('name')); ?></h1>
						<?php else : ?>
							<p class="c-brand__title"><a class="c-header__link" href="<?php echo esc_attr(home_url()); ?>" title=""><?php echo esc_html(get_bloginfo('name')); ?></a></p>
						<?php endif; ?>
						<span class="u-visually-hidden"><?php echo esc_html(bloginfo('description')); ?></span>
						<img class="c-brand__slogan" src="<?php echo esc_url(get_template_directory_uri() . '/dist/slogan.svg'); ?>" alt="">
					</div>
				</div>
			</div>
		</div>

		<div class="mou-site-wrap mou-site-wrap--padding u-1/1 c-header__actions">
			<button class="c-header__toggle" popovertarget="navigation" popovertargetaction="show">
				<?php esc_html_e('Menu', 'moustache'); ?>
			</button>

			<div class="c-header__search">
				<button class="c-header__toggle" popovertarget="search" popovertargetaction="show"><?php esc_html_e('Search', 'moustache'); ?></button>
			</div>
		</div>

		<div class="c-hero" style="background-image: url('https://res.cloudinary.com/kampbart/image/upload/cs_srgb,f_auto,q_auto/v1688457152/kampbart/default-hero.jpg')">
		</div>

		<div id="navigation" class="c-navigation" popover>
			<nav class="mou-site-wrap mou-site-wrap--padding c-navigation__item">
				<button class="c-header__toggle" popovertarget="navigation" popovertargetaction="hide">
					<?php esc_html_e('Close', 'moustache'); ?>
				</button>
				<?php bem_menu('primary', 'c-nav', ''); ?>
			</nav>
		</div>

		<?php if (is_home()) : ?>
			<?php include get_template_directory() . '/template-parts/next-match.php'; ?>
		<?php endif; ?>

	</header>

	<main class="<?php echo esc_attr(is_home() ? 'c-site--bgcolor' : 'c-site'); ?>" role="main">
