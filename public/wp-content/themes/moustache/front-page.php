<?php

/**
 * The front page template file
 *
 * @package Moustache
 * @since 1.0
 * @version 1.0
 */

get_header(); ?>

<?php
$args = array(
  'posts_per_page' => 12,
);

$query = new WP_Query($args);

if ($query->have_posts()) :
?>

  <section class="c-cards o-section-md">
    <?php
    while ($query->have_posts()) :
      $query->the_post();
    ?>

      <article class="c-listing">
        <a class="c-listing__link" href="<?php the_permalink(); ?>" title="">
          <div class="c-listing__card">

            <?php
            $image = get_the_post_thumbnail(null, 'reports-front', array('class' => 'c-listing__image'));

            if ($image) :
            ?>
              <figure class="u-flush-bottom" role="group">
                <?php echo $image; ?>
              </figure>
            <?php endif; ?>

            <div class="u-soft-md">
              <header class="c-listing__header">
                <?php
                $category = get_the_category();

                if ($category) :
                ?>
                  <span class="c-listing__cat">
                    <?php /* <i class="u-visually-hidden">IKON</i>&nbsp; */ ?>
                    <?php echo esc_html($category[0]->cat_name); ?>
                  </span>
                <?php endif; ?>

                <?php $machine_date = get_the_date('Y-m-d'); ?>
                <time class="c-listing__pubdate" datetime="<?php echo esc_attr($machine_date); ?>"><?php the_date('d.m.Y'); ?></time>
              </header>

              <h2 class="c-listing__title"><?php the_title(); ?></h2>
              <p>
                <?php
                $excerpt = get_the_excerpt();
                $excerpt = substr($excerpt, 0, 500);
                $result = substr($excerpt, 0, strrpos($excerpt, '.'));
                echo $result . '...';
                ?>
              </p>
            </div>
          </div>
        </a>
      </article>

    <?php endwhile; ?>
  </section>
<?php endif; ?>

<div class="u-bg-white">
  <div class="mou-site-wrap mou-site-wrap--padding">
    <?php require get_template_directory() . '/template-parts/featured-players.php'; ?>
  </div>
</div>

<?php get_footer(); ?>
