<?php get_header(); ?>

<?php if (have_posts()) : ?>

  <div class="mou-site-wrap mou-site-wrap--padding wysiwyg">
    <div class="o-grid u-text-center">
      <div class="o-grid__item u-1/1 u-2/3@sm">
        <?php
        while (have_posts()) :
          the_post();
        ?>
          <section class="o-section-md">
            <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
            <?php the_content(); ?>
          </section>
        <?php endwhile; ?>
        <footer class="u-soft-bottom-md">
          <?php posts_nav_link(); ?>
        </footer>
      </div>
    </div>
  </div>

<?php endif; ?>

<?php get_footer(); ?>
