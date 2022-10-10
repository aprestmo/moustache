<?php get_header(); ?>

<div id="content">
  <?php if (!has_category('kamprapporter')) : ?>
    <h1><?php the_title(); ?></h1>
  <?php endif; ?>

  <?php echo get_avatar( get_the_author_meta( 'ID' ), 64 ); ?>
  <?php the_author(); ?>
  <time datetime="<?php the_date('Y-m-d'); ?>"><?php echo get_the_date(); ?></time>

  <?php if (has_category('kamprapporter')) : ?>
  <aside>
    <?php get_template_part('partials/match-stats'); ?>
  </aside>
  <?php endif; ?>

  <?php the_content(); ?>
</div>

<?php get_footer(); ?>
