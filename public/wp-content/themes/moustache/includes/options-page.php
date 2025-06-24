<?php
if ( function_exists('acf_add_options_page') ) {
  acf_add_options_page(array(
    'page_title'    => 'Theme General Settings',
    'menu_title'    => 'Theme Settings',
    'menu_slug'     => 'theme-general-settings',
    'capability'    => 'edit_posts',
    'redirect'      => false
  ));

  acf_add_options_sub_page(array(
    'page_title'    => 'Club Information',
    'menu_title'    => 'Club Information',
    'parent_slug'   => 'theme-general-settings',
  ));

  acf_add_options_sub_page(array(
    'page_title'    => 'Theme Header Settings',
    'menu_title'    => 'Header',
    'parent_slug'   => 'theme-general-settings',
  ));

  acf_add_options_sub_page(array(
    'page_title'    => 'Theme Footer Settings',
    'menu_title'    => 'Footer',
    'parent_slug'   => 'theme-general-settings',
  ));

  acf_add_options_sub_page(array(
    'page_title'    => 'Standings Management',
    'menu_title'    => 'Standings',
    'parent_slug'   => 'theme-general-settings',
  ));
}

// Add custom admin page for standings management
add_action('admin_menu', function() {
    add_submenu_page(
        'theme-general-settings',
        'Standings Management',
        'Standings',
        'manage_options',
        'standings-management',
        'render_standings_management_page'
    );
});

function render_standings_management_page() {
    if (isset($_POST['clear_standings_cache']) && current_user_can('manage_options')) {
        clear_standings_cache();
        echo '<div class="notice notice-success"><p>Standings cache cleared successfully!</p></div>';
    }

    $standings = fetch_standings_data();
    $last_update = get_standings_last_update();
    ?>
    <div class="wrap">
        <h1>Standings Management</h1>

        <div class="card">
            <h2>Cache Status</h2>
            <p><strong>Last Update:</strong> <?php echo $last_update ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_update)) : 'Never'; ?></p>
            <p><strong>Data Available:</strong> <?php echo $standings ? 'Yes (' . count($standings) . ' teams)' : 'No'; ?></p>

            <form method="post" style="margin-top: 15px;">
                <?php wp_nonce_field('clear_standings_cache', 'standings_nonce'); ?>
                <input type="submit" name="clear_standings_cache" class="button button-secondary" value="Clear Cache" onclick="return confirm('Are you sure you want to clear the standings cache?');">
            </form>
        </div>

        <?php if ($standings) : ?>
        <div class="card" style="margin-top: 20px;">
            <h2>Current Standings Preview</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Pos</th>
                        <th>Team</th>
                        <th>P</th>
                        <th>Pts</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($standings, 0, 5) as $team) : ?>
                    <tr>
                        <td><?php echo esc_html($team['position']); ?></td>
                        <td><?php echo esc_html($team['team']); ?></td>
                        <td><?php echo esc_html($team['matches'] ?? 0); ?></td>
                        <td><strong><?php echo esc_html($team['points'] ?? 0); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><small>Showing top 5 teams. Full table available on the frontend.</small></p>
        </div>
        <?php endif; ?>
    </div>
    <?php
}
