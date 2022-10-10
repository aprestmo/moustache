<?php

/**
 * Pitch overview template
 *
 * TODO: Design and map
 */
get_header(); ?>


<?php
$posts = get_posts(
  array(
    'numberposts' => -1,
    'post_type'   => 'pitch',
    'orderby'     => 'title',
    'order'       => 'ASC',
  )
);

if ($posts) {

  foreach ($posts as $post) {
?>


<?php
$location = get_field('address');
if( $location ): ?>
    <div class="acf-map" data-zoom="16">
        <div class="marker" data-lat="<?php echo esc_attr($location['lat']); ?>" data-lng="<?php echo esc_attr($location['lng']); ?>"></div>
    </div>
<?php endif; ?>


    <h1><?php the_title(); ?></h1>

    <?php if (get_field('surface')) : ?>
      <?php
      $field = get_field_object('surface');
      $value = get_field('surface');
      $label = $field['choices'][$value];
      ?>
      <dl>
        <dt><?php esc_html_e('Surface', 'moustache'); ?></dt>
        <dd><?php esc_html_e($label); ?></dd>
      </dl>

    <?php endif; ?>

    <?php if (get_field('image')) : ?>
      <?php
      $image = get_field('image');
      ?>
      <img src="<?php esc_attr_e($image); ?>" alt="">

    <?php endif; ?>

    <?php if (get_field('address')) : ?>
      <?php $map = get_field('address'); ?>
      <?php /*<pre><?php var_dump($map); ?></pre> */ ?>
      <?php echo $map['address']; ?>
    <?php endif; ?>

  <?php } ?>

<?php } ?>

<?php get_footer(); ?>
