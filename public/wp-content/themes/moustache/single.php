<?php get_header(); ?>

<div class="post">
  <?php if (!has_category('kamprapporter')) : ?>
    <h1><?php the_title(); ?></h1>
  <?php endif; ?>

  <div class="byline">
    <?php echo get_avatar( get_the_author_meta( 'ID' ), 48 ); ?>
    <p><?php the_author(); ?></p>
    <time datetime="<?php the_date('Y-m-d'); ?>"><?php echo get_the_date(); ?></time>
  </div>

  <?php if (has_category('kamprapporter')) : ?>
  <details class="match-stats flow" open>
    <summary>
      Kampfakta
    </summary>
    <?php get_template_part('partials/match-stats'); ?>
  </details>
  <?php endif; ?>

  <div class="article flow">
    <?php the_content(); ?>
  </div>
</div>

<?php get_footer(); ?>
