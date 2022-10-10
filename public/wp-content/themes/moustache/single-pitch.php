<?php get_header(); ?>

<?php
while (have_posts()) :
  the_post();
?>

  <h1><?php the_title(); ?></h1>

  <?php
  $image = get_field('image');
  if (!empty($image)) :
  ?>
    <img src="<?php echo $image['sizes']['large']; ?>" alt="
						<?php
            _e('Image from ', 'moustache');
            the_title();
            ?>
">
  <?php endif; ?>

  <?php
  $field = get_field_object('surface');
  $value = get_field('surface');
  $label = $field['choices'][$value];

  _e('Surface', 'moustache');
  echo $label;
  ?>

  <?php
  // Sjekk OPPguiden
  $map     = get_field('address');
  $address = $map['address'];

  echo $address;
  ?>

<?php endwhile; ?>

<?php get_footer(); ?>
