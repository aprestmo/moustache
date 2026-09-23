#!/usr/bin/env bash
#
# auto-deploy.sh — push-to-deploy for the VPS checkout.
#
# Fetches origin/main, fast-forwards, and rebuilds front-end assets only when
# build inputs changed (src/, public/, build config). Idempotent and protected
# by a lock, so it is safe to run from both triggers at once:
#
#   - Gitea push webhook (instant) — includes/deploy-webhook.php spawns this
#   - host cron (fallback, every minute) — see README → Auto-deploy
#
# Usage (on the server, from anywhere):
#   bash auto-deploy.sh
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
if [ "$LOCAL" = "$REMOTE" ]; then
	log "already up to date (${LOCAL:0:7})"
	exit 0
fi

# What changed between where we are and origin/main (read before merging).
CHANGED=$(git diff --name-only "$LOCAL" "$REMOTE")

git merge --ff-only --quiet "$REMOTE" ||
	die "cannot fast-forward — the checkout has local commits or dirty files; fix by hand in $(pwd)"

log "updated ${LOCAL:0:7} -> $(git rev-parse --short HEAD)"

# --- rebuild assets only if build inputs changed ------------------------------
if printf '%s\n' "$CHANGED" | grep -qE '^(src/|public/|package\.json$|pnpm-lock\.yaml$|vite\.config\.js$|postcss\.config\.js$)'; then
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
