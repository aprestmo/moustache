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
