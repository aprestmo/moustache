<?php

/**
 * Add some extra capabilities to editors
 *
 * SECURITY: a former `?starter_map_roles=1` GET trigger on `init` has been
 * removed. It granted the editor role dangerous capabilities
 * (edit_theme_options, create_users, delete_users, edit_users,
 * remove_users, promote_users, list_users) for ANY request hitting an admin
 * URL — no nonce, no capability check, reachable unauthenticated via e.g.
 * wp-admin/admin-ajax.php.
 *
 * To (re-)grant those capabilities to the editor role intentionally, run it
 * once yourself via WP-CLI:
 *
 *   wp eval '$r = get_role("editor"); if ($r) { foreach (["edit_theme_options","create_users","delete_users","edit_users","remove_users","promote_users","list_users"] as $c) { $r->add_cap($c); } } echo "done\n";'
 *
 * @since 1.0
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
