<?php get_header(); ?>

<h1><?php the_title(); ?></h1>

<?php
  $image = get_field('image');
  if (!empty( $image )): ?>
    <img src="<?php echo esc_url($image); ?>" alt="<?php esc_attr_e('Photo of', 'moustache'); ?> <?php esc_attr_e(get_the_title()); ?>">
<?php endif; ?>

<?php
  $field = get_field_object('surface');
  $value = get_field('surface');
  $label = $field['choices'][$value];
?>
<dl>
  <dt><?php _e('Surface', 'moustache'); ?>:</dt>
  <dd><?php echo $label; ?></dd>
</dl>

<p><?php the_field('address'); ?></p>
<?php
  $map     = get_field('address');
  $address = $map['address'];
  echo $address;
?>

<a href="/baner/">Se alle baner</a>

<?php get_footer(); ?>
