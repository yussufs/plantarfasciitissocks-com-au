#!/usr/bin/env bash
set -euo pipefail

# ──────────────────────────────────────────────
# deploy.sh — Local mirror of the GitHub Actions deploy.
#
# Builds the theme and rsyncs it to the remote theme path defined in
# scripts/config.sh. Use this when CI is blocked (e.g. BitNinja IP ban on
# the GitHub runner) or you want a faster ad-hoc deploy from your Mac.
#
# Usage:
#   ./scripts/deploy.sh             # build + deploy
#   ./scripts/deploy.sh --dry-run   # build + show what would change, transfer nothing
#   ./scripts/deploy.sh --skip-build # rsync existing dist/ only
#
# Requires scripts/config.sh with SSH_HOST, SSH_USER, SSH_PORT, SSH_KEY, REMOTE_PATH.
# ──────────────────────────────────────────────

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
CONFIG="$SCRIPT_DIR/config.sh"

if [ ! -f "$CONFIG" ]; then
    echo "✖ scripts/config.sh not found."
    echo "  Copy scripts/config.example.sh → scripts/config.sh and fill in SSH details."
    exit 1
fi

# shellcheck source=config.example.sh
source "$CONFIG"

# Validate required vars.
: "${SSH_HOST:?Missing SSH_HOST in config.sh}"
: "${SSH_USER:?Missing SSH_USER in config.sh}"
: "${SSH_PORT:?Missing SSH_PORT in config.sh}"
: "${SSH_KEY:?Missing SSH_KEY in config.sh}"
: "${REMOTE_PATH:?Missing REMOTE_PATH in config.sh}"

# Expand a leading ~ in SSH_KEY — shell vars don't auto-expand tildes.
SSH_KEY="${SSH_KEY/#\~/$HOME}"

if [ ! -f "$SSH_KEY" ]; then
    echo "✖ SSH key not found at $SSH_KEY"
    echo "  Tip: use \$HOME instead of ~ in config.sh, e.g. SSH_KEY=\"\$HOME/.ssh/keyname.pem\""
    exit 1
fi

# Parse flags.
DRY_RUN=""
SKIP_BUILD=""
for arg in "$@"; do
    case "$arg" in
        --dry-run)    DRY_RUN="--dry-run --itemize-changes" ;;
        --skip-build) SKIP_BUILD=1 ;;
        *) echo "Unknown flag: $arg"; exit 1 ;;
    esac
done

cd "$PROJECT_ROOT"

# ── Build ───────────────────────────────────────────────────────────────────
if [ -z "$SKIP_BUILD" ]; then
    echo "==> Building theme..."
    npm run build
else
    echo "==> Skipping build (--skip-build)."
fi

# ── Deploy ──────────────────────────────────────────────────────────────────
echo "==> Deploying to $SSH_USER@$SSH_HOST:$REMOTE_PATH"
[ -n "$DRY_RUN" ] && echo "    DRY RUN — nothing will actually transfer."

# Same exclusions as .github/workflows/*.yml — keep these in sync.
rsync -avz --delete $DRY_RUN \
    --exclude='node_modules/' \
    --exclude='src/' \
    --exclude='.git/' \
    --exclude='.github/' \
    --exclude='scripts/' \
    --exclude='vite.config.js' \
    --exclude='svelte.config.js' \
    --exclude='tailwind.config.js' \
    --exclude='postcss.config.js' \
    --exclude='package.json' \
    --exclude='package-lock.json' \
    --exclude='CLAUDE.md' \
    --exclude='README.md' \
    --exclude='.env' \
    --exclude='.env.*' \
    -e "ssh -i '$SSH_KEY' -p $SSH_PORT -o StrictHostKeyChecking=accept-new" \
    ./ "$SSH_USER@$SSH_HOST:$REMOTE_PATH/"

echo ""
echo "==> Done."
[ -z "$DRY_RUN" ] && echo "    Live: https://${DOMAIN:-yussufs.com}/"
