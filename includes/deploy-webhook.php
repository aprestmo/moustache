<?php
//Die if accessed directly
defined('ABSPATH') || die('Shame on you');

/**
 * Gitea push webhook → auto-deploy.
 *
 * POST /wp-json/moustache/v1/deploy spawns auto-deploy.sh in the background
 * (git fetch + ff merge + conditional pnpm build) and answers immediately.
 * The host cron running the same script every minute is the fallback, so a
 * missed webhook still deploys within ~60s.
 *
 * Setup (see README → Auto-deploy):
 *   1. define('MOUSTACHE_WEBHOOK_SECRET', '<random>') in wp-config.php
 *   2. Gitea → repo → Settings → Webhooks → URL + the same secret
 *
 * The endpoint stays disabled (404) while the constant is undefined.
 *
 * Gitea signs deliveries as X-Gitea-Signature: hex HMAC-SHA256 of the raw
 * body, no prefix — verified below before anything is spawned.
 */
add_action('rest_api_init', function () {
    register_rest_route('moustache/v1', '/deploy', [
        'methods'             => 'POST',
        'permission_callback' => function ($request) {
            if (!defined('MOUSTACHE_WEBHOOK_SECRET') || MOUSTACHE_WEBHOOK_SECRET === '') {
                return new WP_Error('deploy_disabled', 'Auto-deploy webhook is not configured.', ['status' => 404]);
            }

            $signature = (string) $request->get_header('x-gitea-signature');
            $expected  = hash_hmac('sha256', $request->get_body(), MOUSTACHE_WEBHOOK_SECRET);

            if ($signature === '' || !hash_equals($expected, $signature)) {
                return new WP_Error('deploy_bad_signature', 'Invalid signature.', ['status' => 403]);
            }

            return true;
        },
        'callback'            => function ($request) {
            // Only pushes to main deploy; everything else (other branches,
            // tag/ping events with a body we still had to sign) is a no-op.
            $payload = json_decode($request->get_body(), true);
            $ref     = is_array($payload) ? ($payload['ref'] ?? '') : '';
            if ($ref !== 'refs/heads/main') {
                return ['status' => 'ignored', 'reason' => 'not a push to main'];
            }

            $script = get_template_directory() . '/auto-deploy.sh';
            if (!file_exists($script)) {
                return new WP_Error('deploy_missing_script', 'auto-deploy.sh not found in the theme.', ['status' => 500]);
            }

            // Spawn detached and log to stdout/stderr; never block the request.
            // Locking lives in auto-deploy.sh, so a cron run racing this one
            // just waits instead of double-pulling.
            $log    = '/tmp/moustache-deploy.log';
            $cmd    = sprintf(
                'nohup bash %s > %s 2>&1 & echo $!',
                escapeshellarg($script),
                escapeshellarg($log)
            );
            $output      = [];
            $exit_code   = 0;
            exec($cmd, $output, $exit_code);
            $pid = (int) (trim(implode("\n", (array) $output)) ?: 0);
            if ($exit_code !== 0 || $pid <= 0) {
                return new WP_Error('deploy_spawn_failed', 'Could not spawn auto-deploy.sh (check PHP exec permissions).', ['status' => 500]);
            }

            return ['status' => 'queued', 'pid' => $pid, 'log' => $log];
        },
    ]);
});
