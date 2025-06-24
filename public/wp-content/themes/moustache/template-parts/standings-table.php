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

/**
 * Fetch standings data from JSON URL with caching
 *
 * @return array|false Standings data or false on error
 */
function fetch_standings_data(): array|false
{
    // Check for cached data (cache for 1 hour)
    $cache_key = 'standings_data';
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        return $cached_data;
    }

    $json_url = 'https://raw.githubusercontent.com/aprestmo/bedriftsidretten-standings-scraper/refs/heads/main/public/standings.json?token=GHSAT0AAAAAAC5EUUJLY5Q6O32EOEILHBJY2C2FISA';

    // Use WordPress HTTP API
    $response = wp_remote_get($json_url, [
        'timeout' => 10,
        'user-agent' => 'WordPress/' . get_bloginfo('version')
    ]);

    if (is_wp_error($response)) {
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }

    // Cache the data for 1 hour
    set_transient($cache_key, $data, HOUR_IN_SECONDS);

    return $data;
}

// Fetch standings data
$standings = fetch_standings_data();

if ($standings && is_array($standings)) :
?>
    <div class="table-scroll" role="region" aria-labelledby="standings-table" tabindex="0">
        <table>
            <caption id="standings-table"><?php esc_html_e('Tabell', 'moustache'); ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e('Pos', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('Lag', 'moustache'); ?></th>
                    <th scope="col"><?php esc_html_e('K', 'moustache'); ?></th>
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
                    <tr<?php echo ($team['team'] === 'FK Kampbart') ? ' class="highlight-team"' : ''; ?>>
                        <td><?php echo esc_html($team['position']); ?></td>
                        <td><?php echo esc_html($team['team']); ?></td>
                        <td><?php echo esc_html($team['matches']); ?></td>
                        <td><?php echo esc_html($team['wins']); ?></td>
                        <td><?php echo esc_html($team['draws']); ?></td>
                        <td><?php echo esc_html($team['losses']); ?></td>
                        <td><strong><?php echo esc_html($team['points']); ?></strong></td>
                        <td><?php echo esc_html($team['goalsScored']); ?></td>
                        <td><?php echo esc_html($team['goalsConceded']); ?></td>
                        <td><?php echo esc_html($team['goalsScored'] - $team['goalsConceded']); ?></td>
                        </tr>
                    <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="u-soft-top-sm">
        <small>
            <?php
            printf(
                esc_html__('Sist oppdatert: %s', 'moustache'),
                esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format')))
            );
            ?>
        </small>
    </div>
<?php else : ?>
    <div class="u-text-center">
        <p><?php esc_html_e('Kunne ikke laste tabell data for øyeblikket.', 'moustache'); ?></p>
        <p><small><?php esc_html_e('Prøv å laste siden på nytt senere.', 'moustache'); ?></small></p>
    </div>
<?php endif; ?>