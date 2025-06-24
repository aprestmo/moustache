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
echo '<h3>Standings Table</h3>';

// Check if functions exist before calling them
if (!function_exists('fetch_standings_data') || !function_exists('get_standings_last_update')) {
    echo '<p>Standings functions not available.</p>';
    echo '</div>';
    return;
}

// Fetch standings data using the function from functions.php
try {
    $standings = fetch_standings_data();
    $last_update = get_standings_last_update();
    echo '<p>Functions called successfully.</p>';
} catch (Exception $e) {
    error_log('Standings table error: ' . $e->getMessage());
    $standings = false;
    $last_update = false;
    echo '<p>Error occurred: ' . esc_html($e->getMessage()) . '</p>';
}

if ($standings && is_array($standings)) {
    echo '<p>Data loaded: ' . count($standings) . ' teams</p>';
    ?>
    <div class="table-scroll" role="region" aria-labelledby="standings-table" tabindex="0">
        <table>
            <caption id="standings-table"><?php esc_html_e('Tabell', 'moustache'); ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e('Pos', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('Lag', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('S', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('V', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('U', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('T', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('P', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('M+', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('M-', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('M±', 'moustache'); ?></th>
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
                        <td><?php echo esc_html($team['points'] ?? 0); ?></td>
                        <td><?php echo esc_html($team['goalsScored'] ?? 0); ?></td>
                        <td><?php echo esc_html($team['goalsConceded'] ?? 0); ?></td>
                        <td><?php echo esc_html(($team['goalsScored'] ?? 0) - ($team['goalsConceded'] ?? 0)); ?></td>
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
                    esc_html__('Sist oppdatert: %s', 'moustache'),
                    esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_update)))
                );
            } else {
                esc_html_e('Sist oppdatert: Ukjent', 'moustache');
            }
            ?>
        </small>
    </div>
    <?php
} else {
    echo '<p>No standings data available.</p>';
}

echo '</div>';
?>
