<?php get_header(); ?>

<article>
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <aside>
      <figure>
        <?php echo get_avatar( get_the_author_meta( 'ID' ), 64 ); ?>
        <figcaption>
          <?php the_author(); ?>
        </figcaption>
      </figure>
      <time><?php the_date(); ?></time>

      <?php if (has_category('kamprapporter')) : ?>
        <?php include get_template_directory() . '/template-parts/match-stats.php'; ?>
        <?php endif; ?>
    </aside>

    <div class="post-content">
      <h1><?php the_title(); ?></h1>
      <?php the_content(); ?>
    </div>
  <?php endwhile;
  endif; ?>
</article>

<?php get_footer(); ?>
