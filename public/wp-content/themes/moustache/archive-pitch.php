<?php get_header(); ?>

<main>
  <h1>Baner</h1>

  <?php
    $args = array(
      'numberposts' => -1,
      'post_type'   => 'pitch',
      'orderby'     => 'title',
      'order'       => 'ASC',
    );

    $pitches = get_posts($args);

    if ($pitches) :
      foreach ($pitches as $pitch) :
  ?>
    <article>
      <h2>
        <a href="<?php echo get_post_permalink($pitch); ?>">
          <?php esc_html_e($pitch->post_title); ?>
        </a>
      </h2>
    </article>

  <?php endforeach; endif; ?>
</main>

<?php get_footer(); ?>
