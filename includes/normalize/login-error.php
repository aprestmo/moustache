<?php
function no_wordpress_errors()
{
  return __('<b>Error:</b> Something went wrong. Please try again.', 'moustache');
}
add_filter('login_errors', 'no_wordpress_errors');
