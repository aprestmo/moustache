#!/usr/bin/env bash
#
# Deploy helper for the VPS (Coolify/Docker volume).
#
# The theme is served from a git checkout inside the `wordpress-files` Docker
# volume, so PHP/ACF/template changes go live with a plain `git pull`.
# Front-end assets are another matter: WordPress reads
# dist/.vite/manifest.json at runtime, so anything under src/ or public/
# (and build config) needs a rebuild on the server — that's this script.
#
# Usage (from the theme directory on the server):
#   git pull origin main && ./deploy.sh
#
# Can also be wired up as Coolify's post-deployment command. Note: the
# optional Gitea Actions workflow (.gitea/workflows/build.yml) rebuilds and
# commits dist/ for you, but only if a Gitea Act runner is registered —
# this script makes deploys work regardless.

set -euo pipefail
cd "$(dirname "$0")"

if ! command -v pnpm >/dev/null 2>&1; then
	echo "deploy.sh: pnpm not found. Install Node 20+ and enable pnpm (corepack enable)." >&2
	exit 1
fi

pnpm install --frozen-lockfile
pnpm build

echo "deploy.sh: done — dist/ refreshed in $(pwd)/dist"
