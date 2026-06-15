<?php

/**
 * Template part for displaying standings table from JSON
 *
 * @package Moustache
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Simple debug version to prevent page breaking
echo '<div class="standings-debug">';

// Check if functions exist before calling them
if (!function_exists('fetch_standings_data') || !function_exists('get_standings_last_update')) {
    echo '<p>' . esc_html__('Standings functions not available.', 'moustache') . '</p>';
    echo '</div>';
    return;
}

// Fetch standings data using the function from functions.php
try {
    $standings = fetch_standings_data();
    $last_update = get_standings_last_update();
} catch (Exception $e) {
    error_log('Standings table error: ' . $e->getMessage());
    $standings = false;
    $last_update = false;
    echo '<p>' . esc_html(sprintf(__('Error occurred: %s', 'moustache'), $e->getMessage())) . '</p>';
}

if ($standings && is_array($standings)) {
?>
    <div class="table-scroll" role="region" aria-labelledby="standings-table" tabindex="0">
        <table>
            <caption id="standings-table"><?php esc_html_e('Standings', 'moustache'); ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e('Pos', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('Team', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('P', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('W', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('D', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('L', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('GF', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('GA', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('GD', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('Pts', 'moustache'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($standings as $team) : ?>
                    <?php
                    // Validate team data structure
                    if (!isset($team['team']) || !isset($team['position'])) {
                        continue;
                    }
                    ?>
                    <tr>
                        <td><?php echo esc_html($team['position']); ?></td>
                        <td><?php echo esc_html($team['team']); ?></td>
                        <td><?php echo esc_html($team['matches'] ?? 0); ?></td>
                        <td><?php echo esc_html($team['wins'] ?? 0); ?></td>
                        <td><?php echo esc_html($team['draws'] ?? 0); ?></td>
                        <td><?php echo esc_html($team['losses'] ?? 0); ?></td>
                        <td><?php echo esc_html($team['goalsScored'] ?? 0); ?></td>
                        <td><?php echo esc_html($team['goalsConceded'] ?? 0); ?></td>
                        <td><?php echo esc_html(($team['goalsScored'] ?? 0) - ($team['goalsConceded'] ?? 0)); ?></td>
                        <td><?php echo esc_html($team['points'] ?? 0); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="u-soft-top-sm">
        <small>
            <?php
            if ($last_update) {
                printf(
                    esc_html__('Last updated: %s', 'moustache'),
                    esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_update)))
                );
            } else {
                esc_html_e('Last updated: Unknown', 'moustache');
            }
            ?>
        </small>
    </div>
<?php
} else {
    echo '<p>' . esc_html__('No standings data available.', 'moustache') . '</p>';
}

echo '</div>';
?>
