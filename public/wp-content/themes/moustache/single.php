<?php get_header(); ?>

<article class="mou-site-wrap mou-site-wrap--padding wysiwyg">
  <div class="o-grid o-section-md">

    <?php
    if (have_posts()) :
      while (have_posts()) :
        the_post();
    ?>

        <aside class="meta o-grid__item u-1/6@sm u-soft-right-md">
          <?php echo get_avatar( get_the_author_meta( 'ID' ), 48 ); ?>
          <dl class="byline">
            <dt>
              <?php esc_html_e('Writer', 'moustache'); ?>
            </dt>
            <dd>
              <?php // echo get_the_author_posts_link(); ?>
              <?php the_author(); ?>
            </dd>
          </dl>
          <time class="pubdate" datetime="<?php echo get_the_date('Y-m-d'); ?>"><?php the_date(); ?></time>
        </aside>

        <div class="o-grid__item
					<?php
          if (in_category('kamprapporter')) :
          ?>
			 u-1/2@sm
			<?php
          else :
      ?>
			 u-2/3@sm<?php endif; ?>">
          <h1><?php the_title(); ?></h1>
          <?php the_content(); ?>
        </div>

        <?php if (has_category('kamprapporter')) : ?>
          <aside class="o-grid__item u-1/3@sm u-soft-left-md">
            <div class="c-sidebar">
              <?php include get_template_directory() . '/template-parts/match-stats.php'; ?>
            </div>
          </aside>
        <?php endif; ?>

    <?php
      endwhile;
    endif;
    ?>
  </div>
</article>

<?php get_footer(); ?>
