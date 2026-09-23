<?php
//Die if accessed directly
defined('ABSPATH') || die('Shame on you');

/**
 * Gitea push webhook → auto-deploy.sh.
 *
 * Served through admin-ajax.php instead of the REST API on purpose:
 * kampbart.com has the REST API disabled site-wide (an external
 * `rest_authentication_errors` filter returning WP_Error 'rest_disabled' —
 * it does not live in this repo, so it cannot be allowlisted from here).
 * admin-ajax.php is unaffected by that filter and always reachable.
 *
 * Setup (see README → Auto-deploy):
 *   1. define('MOUSTACHE_WEBHOOK_SECRET', '<random>') in wp-config.php
 *   2. Gitea → repo → Settings → Webhooks →
 *      https://kampbart.com/wp-admin/admin-ajax.php?action=moustache_deploy
 *      + the same secret (Content type: application/json)
 *
 * The handler stays disabled (404) while the constant is undefined.
 *
 * Gitea signs deliveries as X-Gitea-Signature: hex HMAC-SHA256 of the raw
 * body, no prefix — verified before anything is spawned.
 */
function moustache_handle_deploy_webhook()
{
    if (!defined('MOUSTACHE_WEBHOOK_SECRET') || MOUSTACHE_WEBHOOK_SECRET === '') {
        wp_send_json(['code' => 'deploy_disabled', 'message' => 'Auto-deploy webhook is not configured.'], 404);
    }

    $body      = (string) file_get_contents('php://input');
    $signature = (string) ($_SERVER['HTTP_X_GITEA_SIGNATURE'] ?? '');
    $expected  = hash_hmac('sha256', $body, MOUSTACHE_WEBHOOK_SECRET);

    if ($signature === '' || !hash_equals($expected, $signature)) {
        wp_send_json(['code' => 'deploy_bad_signature', 'message' => 'Invalid signature.'], 403);
    }

    // Only pushes to main deploy; anything else (other branches, pings with a
    // body we still had to sign) is an explicit no-op.
    $payload = json_decode($body, true);
    $ref     = is_array($payload) ? ($payload['ref'] ?? '') : '';
    if ($ref !== 'refs/heads/main') {
        wp_send_json(['status' => 'ignored', 'reason' => 'not a push to main']);
    }

    $script = get_template_directory() . '/auto-deploy.sh';
    if (!file_exists($script)) {
        wp_send_json(['code' => 'deploy_missing_script', 'message' => 'auto-deploy.sh not found in the theme.'], 500);
    }

    // Spawn detached and log to a file; never block the request. Locking lives
    // in auto-deploy.sh, so a cron run racing this one just waits instead of
    // double-pulling.
    $log       = '/tmp/moustache-deploy.log';
    $cmd       = sprintf(
        'nohup bash %s > %s 2>&1 & echo $!',
        escapeshellarg($script),
        escapeshellarg($log)
    );
    $output    = [];
    $exit_code = 0;
    exec($cmd, $output, $exit_code);
    $pid = (int) (trim(implode("\n", (array) $output)) ?: 0);

    if ($exit_code !== 0 || $pid <= 0) {
        wp_send_json(['code' => 'deploy_spawn_failed', 'message' => 'Could not spawn auto-deploy.sh (check PHP exec permissions).'], 500);
    }

    wp_send_json(['status' => 'queued', 'pid' => $pid, 'log' => $log]);
}
add_action('wp_ajax_nopriv_moustache_deploy', 'moustache_handle_deploy_webhook');
add_action('wp_ajax_moustache_deploy', 'moustache_handle_deploy_webhook');
