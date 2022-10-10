<?php get_header(); ?>

<h1><?php the_title(); ?></h1>

<?php
  $image = get_field('image');
  if (!empty( $image )): ?>
    <img src="<?php echo esc_url($image); ?>" alt="<?php esc_attr_e('Photo of', 'moustache'); ?> <?php esc_attr_e(get_the_title()); ?>">
<?php endif; ?>

<dl>
  <dt>Dekke:</dt>
  <dd><?php the_field('surface', 'moustache'); ?></dd>
</dl>

<p><?php the_field('address'); ?></p>

<a href="/baner/">Baner</a>

<?php get_footer(); ?>
