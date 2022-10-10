<?php

/**
 * Add some extra capabilities to editors
 *
 * @since 1.0
 */
function starter_map_editor_role()
{
  if (is_admin() && isset($_GET['starter_map_roles'])) {
    $role = get_role('editor');

    if (is_object($role)) {
      $role->add_cap('edit_theme_options');
      $role->add_cap('create_users');
      $role->add_cap('delete_users');
      $role->add_cap('edit_users');
      $role->add_cap('remove_users');
      $role->add_cap('promote_users');
      $role->add_cap('list_users');
    }

    wp_die('Done mapping users');
  }
}
add_action('init', 'starter_map_editor_role', 1);

/**
 * Editors should not edit admin-users
 *
 * @access public
 */
function starter_editable_roles($roles)
{
  if (isset($roles['administrator']) && !current_user_can('administrator')) {
    unset($roles['administrator']);
  }
  return $roles;
}
add_filter('editable_roles', 'starter_editable_roles');

/**
 * Map usermeta
 * If someone is trying to edit or delete and admin and that user isn't an admin, don't allow it
 *
 * @access public
 */
function starter_map_meta_cap($caps, $cap, $user_id, $args)
{
  switch ($cap) {
    case 'edit_user':
    case 'remove_user':
    case 'promote_user':
      if (isset($args[0]) && $args[0] == $user_id) {
        break;
      } else if (!isset($args[0]) || !$args[0]) {
        $caps[] = 'do_not_allow';
      }

      $other = new WP_User(absint($args[0]));
      if ($other->has_cap('administrator')) {
        if (!current_user_can('administrator')) {
          $caps[] = 'do_not_allow';
        }
      }

      break;

    case 'delete_user':
    case 'delete_users':
      if (!isset($args[0])) {
        break;
      }

      $other = new WP_User(absint($args[0]));
      if ($other->has_cap('administrator')) {
        if (!current_user_can('administrator')) {
          $caps[] = 'do_not_allow';
        }
      }

      break;
  }
  return $caps;
}
add_filter('map_meta_cap', 'starter_map_meta_cap', 10, 4);
