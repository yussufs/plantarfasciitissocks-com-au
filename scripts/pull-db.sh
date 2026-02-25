#!/usr/bin/env bash
set -euo pipefail

# ──────────────────────────────────────────────
# pull-db.sh — Pull production database to local environment.
#
# Usage: ./scripts/pull-db.sh
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

echo "==> Pulling database from $DOMAIN to local..."

# Export production DB via SSH and import locally.
ssh "${SSH_USER}@${SSH_HOST}" \
    "cd ${SSH_PATH} && wp db export -" \
    | wp db import - --path="$LOCAL_PATH"

echo "    Database imported."

# Search-replace production URL with local URL.
echo "==> Running search-replace: $DOMAIN -> ${LOCAL_URL#http://}"
wp search-replace \
    "https://${DOMAIN}" \
    "$LOCAL_URL" \
    --path="$LOCAL_PATH" \
    --all-tables \
    --skip-columns=guid

wp search-replace \
    "http://${DOMAIN}" \
    "$LOCAL_URL" \
    --path="$LOCAL_PATH" \
    --all-tables \
    --skip-columns=guid

# Flush rewrite rules.
wp rewrite flush --path="$LOCAL_PATH"

echo ""
echo "==> Done! Production database pulled to local."
echo "    Visit: $LOCAL_URL"
