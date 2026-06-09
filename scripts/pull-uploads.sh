#!/usr/bin/env bash
set -euo pipefail

# ──────────────────────────────────────────────
# pull-uploads.sh — Sync production wp-content/uploads (media library / product
# images) down to the local environment.
#
# Usage:
#   ./scripts/pull-uploads.sh              # sync everything
#   ./scripts/pull-uploads.sh --dry-run    # preview what would transfer
#   ./scripts/pull-uploads.sh --delete     # also remove local files not on prod
#   (any extra args are passed straight through to rsync)
#
# The database (pull-db.sh) stores only references to these files, so without
# them the local site shows broken images. rsync is incremental — re-running
# only transfers what changed.
#
# Requires scripts/config.sh (copy from config.example.sh).
# ──────────────────────────────────────────────

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
CONFIG="$SCRIPT_DIR/config.sh"

if [ ! -f "$CONFIG" ]; then
    echo "Error: scripts/config.sh not found."
    echo "Copy scripts/config.example.sh to scripts/config.sh and fill in your values."
    exit 1
fi

# shellcheck source=config.example.sh
source "$CONFIG"

REMOTE_UPLOADS="${SSH_PATH}/wp-content/uploads/"
LOCAL_UPLOADS="${LOCAL_PATH}/wp-content/uploads/"

# Build the ssh transport for rsync: always set the port; add the identity file
# only if SSH_KEY is set (otherwise fall back to the ssh-agent / default key).
SSH_CMD="ssh -p ${SSH_PORT:-22}"
[ -n "${SSH_KEY:-}" ] && SSH_CMD="$SSH_CMD -i $SSH_KEY"

mkdir -p "$LOCAL_UPLOADS"

# Pick a progress flag the installed rsync supports. macOS ships rsync 2.6.9,
# which lacks --info=progress2 (a single overall bar, rsync 3+). Fall back to
# --progress (per-file) elsewhere. Tip: `brew install rsync` gets the modern one.
if rsync --help 2>&1 | grep -q 'info=FLAGS'; then
    PROGRESS=(--info=progress2)
else
    PROGRESS=(--progress)
fi

echo "==> Syncing uploads from $DOMAIN to local..."
echo "    Remote: ${SSH_USER}@${SSH_HOST}:${REMOTE_UPLOADS}"
echo "    Local:  $LOCAL_UPLOADS"

# -a archive, -z compress, -h human-readable sizes.
# Common heavy/irrelevant dirs are excluded; remove these if you need them.
rsync -azh "${PROGRESS[@]}" \
    -e "$SSH_CMD" \
    --exclude 'cache/' \
    --exclude 'backup/' \
    --exclude '*.log' \
    "$@" \
    "${SSH_USER}@${SSH_HOST}:${REMOTE_UPLOADS}" \
    "$LOCAL_UPLOADS"

echo ""
echo "==> Done! Uploads synced to $LOCAL_UPLOADS"
