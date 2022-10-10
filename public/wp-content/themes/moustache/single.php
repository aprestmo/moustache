<?php get_header(); ?>

<div id="content">
  <h1><?php the_title(); ?></h1>

  <aside>
    <?php echo get_avatar( get_the_author_meta( 'ID' ), 64 ); ?>
    <?php the_author(); ?>
    <time datetime="<?php the_date('Y-m-d'); ?>"><?php echo get_the_date(); ?></time>
  </aside>

  <?php the_content(); ?>
</div>

<?php get_footer(); ?>
