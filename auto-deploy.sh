#!/usr/bin/env bash
#
# auto-deploy.sh — push-to-deploy for the VPS checkout.
#
# Fetches origin/main, fast-forwards, and deploys only approved changes. CI
# deploys committed dist/; a manual server build must opt in explicitly.
# Idempotent and protected
# by a lock, so it is safe to run from both triggers at once:
#
#   - Gitea push webhook (fallback) — includes/deploy-webhook.php spawns this
#   - Gitea Action (after it commits dist) — waits for the pull to finish
#   - host cron (fallback, every minute) — see README → Auto-deploy
#
# Usage (on the server, from anywhere):
#   bash auto-deploy.sh
#   MOUSTACHE_DEPLOY_ALLOW_SERVER_BUILD=1 bash auto-deploy.sh  # explicit manual frontend build
#
# Logs to stdout. The webhook appends to /tmp/moustache-deploy.log inside the
# container; host cron appends to /tmp/moustache-deploy.log on the host.

set -euo pipefail
cd "$(dirname "$0")"

LOCK_DIR="${TMPDIR:-/tmp}/moustache-auto-deploy.lock"
LOCK_MAX_WAIT=180 # seconds — a pnpm build may be running when we arrive
LOCK_STALE=900 # seconds — older than this means the holder died

log() { echo "auto-deploy: $*"; }
die() { echo "auto-deploy: ERROR: $*" >&2; exit 1; }

command -v git >/dev/null 2>&1 || die "git not found"
[ -d .git ] || die "not a git checkout: $(pwd)"

# --- single-instance lock (webhook and cron can race) -----------------------
waited=0
while ! mkdir "$LOCK_DIR" 2>/dev/null; do
	started=$(stat -f %m "$LOCK_DIR" 2>/dev/null || stat -c %Y "$LOCK_DIR" 2>/dev/null || echo 0)
	if [ $(( $(date +%s) - started )) -gt "$LOCK_STALE" ]; then
		log "breaking stale lock $LOCK_DIR"
		rm -rf "$LOCK_DIR"
		continue
	fi
	[ "$waited" -ge "$LOCK_MAX_WAIT" ] && die "lock $LOCK_DIR still held after ${LOCK_MAX_WAIT}s"
	sleep 2
	waited=$((waited + 2))
done
trap 'rm -rf "$LOCK_DIR"' EXIT

# --- fetch + fast-forward ----------------------------------------------------
git fetch --quiet origin main || die "git fetch failed — check credentials for origin (token in remote URL or SSH key for the user running this)"

LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse FETCH_HEAD)
if [ -n "${MOUSTACHE_DEPLOY_EXPECTED_COMMIT:-}" ] && [ "$REMOTE" != "$MOUSTACHE_DEPLOY_EXPECTED_COMMIT" ]; then
	die "remote is $REMOTE, expected $MOUSTACHE_DEPLOY_EXPECTED_COMMIT; a newer or different commit won"
fi

# Never deploy a commit whose built manifest is missing. This is checked in the
# fetched commit, before merging, and also covers the already-up-to-date path.
git cat-file -e "${REMOTE}:dist/.vite/manifest.json" 2>/dev/null ||
	die "remote commit $REMOTE does not contain dist/.vite/manifest.json"

if [ "${MOUSTACHE_DEPLOY_STRICT:-0}" = "1" ] && [ -n "$(git status --porcelain --untracked-files=all)" ]; then
	die "strict deploy requested but the checkout has local changes"
fi
if [ "$LOCAL" = "$REMOTE" ]; then
	log "already up to date (${LOCAL:0:7})"
	exit 0
fi

# What changed between where we are and origin/main (read before merging).
CHANGED=$(git diff --name-only "$LOCAL" "$REMOTE")
BUILD_INPUTS_CHANGED=0
if printf '%s\n' "$CHANGED" | grep -qE '^(src/|public/|package\.json$|pnpm-lock\.yaml$|vite\.config\.js$|postcss\.config\.js$)'; then
	BUILD_INPUTS_CHANGED=1
fi

# Webhook and cron must not deploy unapproved frontend source. The signed CI
# request sets SKIP_BUILD; a human can explicitly opt into a server build.
if [ "$BUILD_INPUTS_CHANGED" -eq 1 ] && [ "${MOUSTACHE_DEPLOY_SKIP_BUILD:-0}" != "1" ] && [ "${MOUSTACHE_DEPLOY_ALLOW_SERVER_BUILD:-0}" != "1" ]; then
	log "frontend build inputs changed — refusing ungated deploy; use Gitea Action or set MOUSTACHE_DEPLOY_ALLOW_SERVER_BUILD=1"
	exit 0
fi

git merge --ff-only --quiet "$REMOTE" ||
	die "cannot fast-forward — the checkout has local commits or dirty files; fix by hand in $(pwd)"

log "updated ${LOCAL:0:7} -> $(git rev-parse --short HEAD)"

# --- rebuild assets only if build inputs changed ------------------------------
if [ "${MOUSTACHE_DEPLOY_SKIP_BUILD:-0}" = "1" ]; then
	[ -f dist/.vite/manifest.json ] || die "CI deploy requested but dist manifest is missing"
	log "build skipped — using committed dist (CI deploy)"
elif [ "$BUILD_INPUTS_CHANGED" -eq 1 ]; then
	if command -v pnpm >/dev/null 2>&1; then
		log "build inputs changed — running deploy.sh"
		bash ./deploy.sh
	else
		log "WARNING: build inputs changed but pnpm is missing — serving the committed dist/ until deploy.sh can run (install Node 20+ / corepack enable)"
	fi
else
	log "no build inputs changed — pull only"
fi

log "done — HEAD is now $(git rev-parse --short HEAD)"
