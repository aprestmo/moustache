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

// Debug mode - show what's happening
$debug_mode = true; // Set to false in production

// Add debug styles
if ($debug_mode) {
    echo '<style>
    .debug-info { background: #e7f3ff; border: 1px solid #b0d4f1; padding: 10px; margin: 10px 0; }
    .debug-error { background: #ffebee; border: 1px solid #ffcdd2; padding: 10px; margin: 10px 0; }
    .error-message { background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px 0; }
    .no-data-message { background: #fff3e0; border: 1px solid #ffcc02; padding: 10px; margin: 10px 0; }
    </style>';
}

// Check if functions exist before calling them
if (!function_exists('fetch_standings_data') || !function_exists('get_standings_last_update')) {
    if ($debug_mode) {
        echo '<div class="debug-error"><p><strong>Debug:</strong> Standings functions not available in functions.php</p></div>';
    }
    return;
}

if ($debug_mode) {
    echo '<div class="debug-info"><p><strong>Debug:</strong> Functions found, attempting to fetch data...</p></div>';
}

// Fetch standings data using the function from functions.php
try {
    $standings = fetch_standings_data();
    $last_update = get_standings_last_update();
    
    if ($debug_mode) {
        echo '<div class="debug-info"><p><strong>Debug:</strong> ' . (is_array($standings) ? 'Got ' . count($standings) . ' teams' : 'No data returned') . '</p></div>';
    }
    
    // Debug: Log what we got
    error_log('Standings debug: ' . (is_array($standings) ? 'Got ' . count($standings) . ' teams' : 'No data returned'));
    
} catch (Exception $e) {
    error_log('Standings table error: ' . $e->getMessage());
    $standings = false;
    $last_update = false;
    if ($debug_mode) {
        echo '<div class="error-message"><p><strong>Error:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
    }
}

if ($standings && is_array($standings)) {
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
    if ($debug_mode) {
        echo '<div class="debug-info"><p><strong>Debug:</strong> No standings data returned from fetch_standings_data()</p></div>';
    }
    echo '<div class="no-data-message"><p>No standings data available at this time.</p></div>';
}
?>
