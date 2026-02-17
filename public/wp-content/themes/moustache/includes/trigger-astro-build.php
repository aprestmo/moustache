<?php
defined('ABSPATH') || die('Shame on you');

/**
 * Trigger GitHub Actions build (moustache-v7) when content is updated.
 * Configure via wp-config.php or filter:
 *   define('MOUSTACHE_GITHUB_OWNER', 'aprestmo');
 *   define('MOUSTACHE_GITHUB_REPO', 'moustache-v7');
 *   define('MOUSTACHE_GITHUB_TOKEN', 'ghp_...');
 */
const MOUSTACHE_TRIGGER_DEBOUNCE_SECONDS = 5;

add_action('save_post', 'moustache_trigger_astro_build', 20, 3);

function moustache_trigger_astro_build(int $post_id, \WP_Post $post, bool $update): void
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    $default_post_types = ['post', 'pitch', 'fixture', 'player', 'club', 'league'];
    $post_types = apply_filters('moustache_trigger_post_types', $default_post_types);
    if (!in_array($post->post_type, $post_types, true)) {
        return;
    }

    $owner = apply_filters('moustache_github_owner', defined('MOUSTACHE_GITHUB_OWNER') ? MOUSTACHE_GITHUB_OWNER : 'aprestmo');
    $repo = apply_filters('moustache_github_repo', defined('MOUSTACHE_GITHUB_REPO') ? MOUSTACHE_GITHUB_REPO : 'moustache-v7');
    $token = apply_filters('moustache_github_token', defined('MOUSTACHE_GITHUB_TOKEN') ? MOUSTACHE_GITHUB_TOKEN : '');

    if (empty($token)) {
        return;
    }

    $last = get_option('moustache_last_trigger_timestamp', '');
    if ($last) {
        try {
            $modified = new DateTime($post->post_modified);
            $last_time = new DateTime($last);
            $diff = $modified->getTimestamp() - $last_time->getTimestamp();
            if (abs($diff) < MOUSTACHE_TRIGGER_DEBOUNCE_SECONDS) {
                return;
            }
        } catch (Exception $e) {
            // Fall through and trigger
        }
    }

    $url = sprintf('https://api.github.com/repos/%s/%s/dispatches', $owner, $repo);
    $response = wp_remote_post($url, [
        'method' => 'POST',
        'headers' => [
            'Accept' => 'application/vnd.github.v3+json',
            'Content-Type' => 'application/json',
            'Authorization' => 'token ' . $token,
        ],
        'body' => json_encode(['event_type' => 'wordpress']),
        'timeout' => 10,
    ]);

    if (!is_wp_error($response)) {
        update_option('moustache_last_trigger_timestamp', $post->post_modified);
    }
}

/**
 * Admin: manual test trigger + status
 */
add_action('admin_menu', function () {
    add_options_page(
        'Astro build trigger',
        'Astro build',
        'manage_options',
        'moustache-astro-trigger',
        'moustache_render_astro_trigger_page'
    );
});

function moustache_render_astro_trigger_page(): void
{
    $owner = apply_filters('moustache_github_owner', defined('MOUSTACHE_GITHUB_OWNER') ? MOUSTACHE_GITHUB_OWNER : 'aprestmo');
    $repo = apply_filters('moustache_github_repo', defined('MOUSTACHE_GITHUB_REPO') ? MOUSTACHE_GITHUB_REPO : 'moustache-v7');
    $token = apply_filters('moustache_github_token', defined('MOUSTACHE_GITHUB_TOKEN') ? MOUSTACHE_GITHUB_TOKEN : '');
    $has_token = !empty($token);
    $last = get_option('moustache_last_trigger_timestamp', '');

    if (isset($_POST['moustache_test_trigger']) && check_admin_referer('moustache_test_trigger')) {
        $url = sprintf('https://api.github.com/repos/%s/%s/dispatches', $owner, $repo);
        $response = wp_remote_post($url, [
            'method' => 'POST',
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Content-Type' => 'application/json',
                'Authorization' => 'token ' . $token,
            ],
            'body' => json_encode(['event_type' => 'wordpress']),
            'timeout' => 15,
        ]);
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $err = is_wp_error($response) ? $response->get_error_message() : null;
    }
    ?>
    <div class="wrap">
        <h1>Astro build trigger</h1>
        <p>Sender <code>repository_dispatch</code> til GitHub for å trigge build av moustache-v7.</p>

        <table class="form-table">
            <tr>
                <th>Status</th>
                <td>
                    <?php if ($has_token) : ?>
                        <span style="color: green;">✓ Token konfigurert</span> (owner: <?php echo esc_html($owner); ?>, repo: <?php echo esc_html($repo); ?>)
                    <?php else : ?>
                        <span style="color: red;">✗ MOUSTACHE_GITHUB_TOKEN mangler i wp-config.php</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Sist trigget</th>
                <td><?php echo $last ? esc_html($last) : 'Aldri'; ?></td>
            </tr>
        </table>

        <?php if (isset($response)) : ?>
            <div class="notice notice-<?php echo $code >= 200 && $code < 300 ? 'success' : 'error'; ?>">
                <p><strong>Test-resultat:</strong>
                    <?php if ($err) : ?>
                        Feil: <?php echo esc_html($err); ?>
                    <?php else : ?>
                        HTTP <?php echo (int) $code; ?>
                        <?php if ($code >= 200 && $code < 300) : ?>
                            – Workflow ble trigget. Sjekk <a href="https://github.com/<?php echo esc_attr($owner); ?>/<?php echo esc_attr($repo); ?>/actions" target="_blank">GitHub Actions</a>.
                        <?php elseif ($body) : ?>
                            <pre style="margin-top: 0.5em; font-size: 11px;"><?php echo esc_html($body); ?></pre>
                        <?php endif; ?>
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($has_token) : ?>
            <form method="post">
                <?php wp_nonce_field('moustache_test_trigger'); ?>
                <p><input type="submit" name="moustache_test_trigger" class="button button-primary" value="Test trigger nå"></p>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
