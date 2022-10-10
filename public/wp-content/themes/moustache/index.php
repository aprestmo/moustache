<?php get_header(); ?>

<main id="content">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
  <article>
    <?php the_category('cat_name'); ?>
    <time datetime="<?php the_date('Y-m-d'); ?>T<?php the_time('G:i:s'); ?>"><?php echo get_the_date('d.m.Y'); ?></time>

    <?php if (!in_category(2)) : ?>
       <h2><?php the_title(); ?></h2>
    <?php endif; ?>

      <?php echo get_the_post_thumbnail(null, 'reports-front'); ?>

    <?php the_content(); ?>
  </article>
<?php endwhile; endif; ?>

<?php next_posts_link( 'Older posts' ); ?>
</main>

<?php get_footer(); ?>
