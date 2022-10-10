<?php

// This is now
$now = date('Y-m-d H:i:s');

// Setup a loop to get some info about next match

$args = array(
  'post_type' => 'fixture',
  'post_status' => 'publish', // Make sure it's only published posts
  'posts_per_page' => 1, // Get only one
  'meta_key' => 'date_time', // Name of custom field to ask for
  'orderby' => 'meta_value',
  'order' => 'ASC', // Gets the one closest in time
  'meta_query' => array(
    array(
      'key' => 'date_time',
      'compare' => '>',
      'value' => $now,
    )
  )
);

$next_match = get_posts($args);

if ($next_match) :

  foreach ($next_match as $post) : setup_postdata($post);

    // Get the match facts
    $home_team = get_field('home_team');
    $away_team = get_field('away_team');
    $when = get_field('date_time');
    $pitch = get_field('pitch');

    foreach ($pitch as $ground) {
      $ground = $ground->post_title;
    }

    foreach ($home_team as $hosts) {
      $hosts = $hosts->post_title;
    }

    foreach ($away_team as $guests) {
      $guests = $guests->post_title;
    }

    // Get date and time for match
    $datetime = new DateTime($when);

    // Do some splitting
    $timestamp = strtotime($when);
    $weekday = date_i18n('l j.', $timestamp);
    $month = date_i18n('F', $timestamp);
    $time = date_i18n('H.i', $timestamp);
?>

    <div class="c-upcoming">
      <div class="mou-site-wrap mou-site-wrap--padding">
        <div class="o-grid c-upcoming__item">
          <p class="o-grid__item u-flush-bottom u-text-center"><img class="c-upcoming__icon" src="<?php echo get_template_directory_uri(); ?>/dist/svg/icons/alert.svg" alt=""><b><?php esc_html_e('Next match:', 'moustache'); ?></b> <?php esc_html_e($hosts); ?>&ndash;<?php esc_html_e($guests); ?>. <?php esc_html_e(ucfirst($weekday)); ?> <?php esc_html_e($month); ?> <?php esc_html_e('at', 'moustache'); ?> <?php esc_html_e($time); ?>, <?php esc_html_e($ground); ?>.</p>
        </div>
      </div>
    </div>

<?php
  endforeach;
  wp_reset_postdata();
endif;
?>
