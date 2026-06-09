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

# Build SSH options: always set the port; include the identity file only if
# SSH_KEY is set (otherwise fall back to the ssh-agent / default key).
SSH_OPTS=(-p "${SSH_PORT:-22}")
[ -n "${SSH_KEY:-}" ] && SSH_OPTS+=(-i "$SSH_KEY")

# Pre-flight: confirm SSH_PATH actually points at the site we expect. Hostinger
# resolves bare paths (e.g. ~/public_html) to the account's PRIMARY domain, so a
# wrong SSH_PATH silently exports a *different* site. Abort if the remote home
# URL doesn't contain the configured DOMAIN.
echo "==> Verifying the remote site at: $SSH_PATH"
REMOTE_HOME="$(ssh "${SSH_OPTS[@]}" "${SSH_USER}@${SSH_HOST}" "cd ${SSH_PATH} && wp option get home" \
    | tr -d '\r' | grep -Eo 'https?://[^[:space:]]+' | head -1)"
echo "    Remote home URL: ${REMOTE_HOME:-<none>}"
if [ -z "$REMOTE_HOME" ] || [[ "$REMOTE_HOME" != *"$DOMAIN"* ]]; then
    echo "Error: remote home URL ('${REMOTE_HOME:-<none>}') does not match DOMAIN ('$DOMAIN')."
    echo "       SSH_PATH ('$SSH_PATH') is likely pointing at the wrong site —"
    echo "       Hostinger defaults bare paths to the primary domain. Set SSH_PATH"
    echo "       to the target site, e.g. ~/domains/$DOMAIN/public_html, and retry."
    exit 1
fi

# FunnelKit (<prefix>fk_*) tables use MariaDB-flavoured foreign keys that strict
# local MySQL 8/9 refuses to recreate on import (ERROR 6125 / 1822 "Missing
# unique key … in the referenced table"). They hold funnel/cart runtime data
# that's irrelevant to local theme dev, so detect and exclude them from the dump.
echo "==> Detecting FunnelKit tables to exclude (MariaDB FK incompatibility)..."
REMOTE_PREFIX="$(ssh "${SSH_OPTS[@]}" "${SSH_USER}@${SSH_HOST}" "cd ${SSH_PATH} && wp db prefix" | tr -d '\r')"
# --all-tables-with-prefix is required: by default `wp db tables` only lists
# tables WordPress registers, which excludes FunnelKit's custom fk_ tables.
EXCLUDE="$(ssh "${SSH_OPTS[@]}" "${SSH_USER}@${SSH_HOST}" "cd ${SSH_PATH} && wp db tables '${REMOTE_PREFIX}fk_*' --all-tables-with-prefix --format=csv" \
    | tr -d '\r' | grep -E "^${REMOTE_PREFIX}fk_" | paste -sd, - || true)"

EXPORT_TAIL=""
if [ -n "$EXCLUDE" ]; then
    echo "    Excluding: $EXCLUDE"
    EXPORT_TAIL="--exclude_tables=$EXCLUDE"
else
    echo "    None found — exporting all tables."
fi

# The local DB may hold data from a previous (possibly different) pull. Offer to
# reset it so the imported dump doesn't mix with stale tables. Defaults to No.
read -r -p "==> Reset local DB before import? This DROPS all local tables. [y/N] " RESET_REPLY
if [[ "${RESET_REPLY:-}" =~ ^[Yy]$ ]]; then
    wp db reset --yes --path="$LOCAL_PATH"
    echo "    Local database reset."
else
    echo "    Keeping existing tables (import will overwrite matching ones)."
fi

# Export production DB via SSH and import locally.
ssh "${SSH_OPTS[@]}" "${SSH_USER}@${SSH_HOST}" \
    "cd ${SSH_PATH} && wp db export - $EXPORT_TAIL" \
    | wp db import - --path="$LOCAL_PATH"

echo "    Database imported."

# Align the local wp-config table prefix with the imported dump. Without this,
# WordPress keeps reading its original (often default wp_) tables and shows an
# empty site even though the data imported fine under the remote's prefix.
if [ -n "$REMOTE_PREFIX" ]; then
    echo "==> Setting local table_prefix to '$REMOTE_PREFIX' to match the import..."
    wp config set table_prefix "$REMOTE_PREFIX" --path="$LOCAL_PATH"
fi

# Ensure a known local admin account so you can log in without production
# credentials. Defaults to admin/admin; override LOCAL_ADMIN_USER / _PASS /
# _EMAIL in config.sh. If the user already exists, just reset its password.
LOCAL_ADMIN_USER="${LOCAL_ADMIN_USER:-admin}"
LOCAL_ADMIN_PASS="${LOCAL_ADMIN_PASS:-admin}"
LOCAL_ADMIN_EMAIL="${LOCAL_ADMIN_EMAIL:-${LOCAL_ADMIN_USER}@${BRAND:-local}.test}"
echo "==> Ensuring local admin '$LOCAL_ADMIN_USER' (password: $LOCAL_ADMIN_PASS)..."
if wp user get "$LOCAL_ADMIN_USER" --field=ID --path="$LOCAL_PATH" >/dev/null 2>&1; then
    wp user update "$LOCAL_ADMIN_USER" --user_pass="$LOCAL_ADMIN_PASS" --role=administrator --path="$LOCAL_PATH"
else
    wp user create "$LOCAL_ADMIN_USER" "$LOCAL_ADMIN_EMAIL" --role=administrator --user_pass="$LOCAL_ADMIN_PASS" --path="$LOCAL_PATH"
fi

# Re-link and re-activate this fork's theme. The imported production DB carries
# its own active theme (template/stylesheet options), so after every pull
# WordPress points at the production/default theme and the local theme looks
# "missing". This script runs from inside the brand fork, so the repo root IS the
# theme — symlink it (idempotent) and activate it so you can edit immediately.
THEME_DIR="$(dirname "$SCRIPT_DIR")"
THEME_SLUG="${THEME_SLUG:-$BRAND}"
THEME_LINK="$LOCAL_PATH/wp-content/themes/$THEME_SLUG"
if [ -L "$THEME_LINK" ] || [ -d "$THEME_LINK" ]; then
    : # already linked or present
else
    ln -s "$THEME_DIR" "$THEME_LINK"
    echo "==> Linked theme: $THEME_DIR -> $THEME_LINK"
fi
echo "==> Activating theme '$THEME_SLUG'..."
wp theme activate "$THEME_SLUG" --path="$LOCAL_PATH"

# Search-replace production URLs with the local URL, via a unique placeholder.
#
# A direct FROM->LOCAL_URL replace is unsafe when a search term is a substring of
# LOCAL_URL — which happens whenever the Valet host embeds the production domain
# (e.g. DOMAIN=site.com.au, LOCAL_URL=http://site.com.au.test). The "http://DOMAIN"
# pass would then re-match the "http://DOMAIN" prefix it had just written and
# append ".test" again, producing "...test.test". Collapsing every production URL
# variant to a placeholder first, then expanding the placeholder once, makes the
# replacement immune to that cascade regardless of how the URLs overlap.
echo "==> Running search-replace: production URLs -> $LOCAL_URL"
PLACEHOLDER="__PULLDB_LOCAL_URL_$$__"
for FROM in "$REMOTE_HOME" "https://${DOMAIN}" "http://${DOMAIN}"; do
    [ -n "$FROM" ] || continue
    wp search-replace \
        "$FROM" \
        "$PLACEHOLDER" \
        --path="$LOCAL_PATH" \
        --all-tables \
        --skip-columns=guid \
        --report-changed-only
done
wp search-replace \
    "$PLACEHOLDER" \
    "$LOCAL_URL" \
    --path="$LOCAL_PATH" \
    --all-tables \
    --skip-columns=guid \
    --report-changed-only

# Flush rewrite rules.
wp rewrite flush --path="$LOCAL_PATH"

echo ""
echo "==> Done! Production database pulled to local."
echo "    Visit: $LOCAL_URL"
echo "    Admin: ${LOCAL_URL}/wp-admin/  (user: $LOCAL_ADMIN_USER  pass: $LOCAL_ADMIN_PASS)"
