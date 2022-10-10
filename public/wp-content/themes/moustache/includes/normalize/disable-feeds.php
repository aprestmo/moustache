<?php

/**
 * Disable Feeds
 *
 * @since 1.0
 */
function starter_disable_feed()
{
  if (is_feed()) {
    wp_redirect(home_url());
    exit;
  }
}

if (!is_admin()) {
  add_action('do_feed', 'starter_disable_feed', 1);
  $rss = array('rdf', 'rss', 'rss2', 'atom');
  foreach ($rss as $r) {
    add_action('do_feed_' . $r, 'starter_disable_feed', 1);
  }
}
