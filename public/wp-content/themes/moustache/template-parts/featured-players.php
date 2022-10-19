<?php

$args = array(
  'post_type' => 'player',
  'post_status' => 'publish',
  'posts_per_page' => 4,
  'meta_key' => 'status',
  'meta_value' => 'active',
  'orderby' => 'rand',
);

$players = get_posts($args);

if ($players) : ?>

  <section class="o-grid o-section-md">
    <header class="o-grid__item u-1/1 u-text-center">
      <h2 class="c-players__title"><?php esc_html_e('Featured players', 'moustache') ?></h2>
    </header>

    <?php foreach ($players as $post) : setup_postdata($post); ?>
      <div class="o-grid__item u-1/2 u-1/4@md u-soft-md u-text-center">
        <a class="c-players--featured" href="<?php the_permalink(); ?>">
          <?php
          $image = get_field('image');
          if ($image) :
          ?>
            <img class="u-img-round" src="<?php esc_attr_e($image['sizes']['thumbnail']); ?>" alt="<?php the_title(); ?>">
          <?php else : ?>
            <svg style="max-height: 220px; max-width: 220px" viewBox="0 0 220 220" xmlns="http://www.w3.org/2000/svg">
              <title><?php esc_attr_e('Missing player profile image', 'moustache') ?></title>
              <path d="M110 0C49.28 0 0 49.28 0 110s49.28 110 110 110 110-49.28 110-110S170.72 0 110 0zm1.392 33.418c18.492 0 33.418 14.926 33.418 33.417 0 18.492-14.926 33.418-33.418 33.418-18.49 0-33.417-14.926-33.417-33.418 0-18.49 14.926-33.417 33.417-33.417zM110 189.368c-27.268 0-51.373-14.146-65.443-35.585.327-21.99 43.63-34.036 65.443-34.036 21.705 0 65.116 12.045 65.443 34.036-14.07 21.44-38.175 35.584-65.443 35.584z" fill="#F5F5F5" fill-rule="nonzero" />
            </svg>
          <?php endif; ?>
        </a>
      </div>
    <?php
      wp_reset_postdata();
    endforeach;
    ?>
  </section>

<?php endif; ?>
