<?php

/**
 * Player overview template
 *
 */
get_header(); ?>

<div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
  <div class="o-grid u-text-center">
    <div class="o-grid__item u-1/1 u-2/3@sm">
      <section class="o-section-md">

        <?php
        $posts = get_posts(
          array(
            'numberposts' => -1,
            'post_type'   => 'player',
            'orderby'     => 'title',
            'order'       => 'ASC',
            'meta_key'    => 'active_member',
            // Må nok endres på
          )
        );

        if ($posts) :
        ?>

          <header>
            <h1><?php esc_html_e('Aktive spillere', 'moustache'); ?></h1>
          </header>

          <ul>
            <?php foreach ($posts as $post) : ?>
              <li class="o-grid__item u-1/2 u-1/4@md u-soft-md">
                <a href="<?php echo get_permalink($post->ID); ?>">
									<?php /*
									<?php
									$image = get_field('image');
									if ($image) :
									?>
										<img src="<?php echo $image['sizes']['thumbnail']; ?>">
									<?php endif; ?>
									*/ ?>
									<?php $shirt_number = get_field('shirt_number'); ?>
									<?php echo get_the_title($post->ID); ?>
									<?php $shirt_number ? '(' . $shirt_number . ')' : ''; ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>

        <?php endif; ?>

        <?php
        $posts = get_posts(
          array(
            'numberposts' => -1,
            'post_type'   => 'player',
            'orderby'     => 'title',
            'order'       => 'ASC',
            'meta_key'    => 'retired_member',
          )
        );

        if ($posts) {
          echo '<h1>Spillere med barten på hylla</h1>';
          echo '<ul>';

          foreach ($posts as $post) {
            echo '<li><a href="' . get_permalink($post->ID) . '">' . get_the_title($post->ID) . '</a></li>';
          }

          echo '</ul>';
        }
        ?>

      </section>
    </div>
  </div>
</div>

<?php get_footer(); ?>
