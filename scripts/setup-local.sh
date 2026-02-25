#!/usr/bin/env bash
set -euo pipefail

# ──────────────────────────────────────────────
# setup-local.sh — Set up a local WordPress dev environment.
#
# Usage: ./scripts/setup-local.sh brand-name
#
# Prerequisites: Laravel Valet, Homebrew MySQL, WP-CLI, Node.
# ──────────────────────────────────────────────

BRAND="${1:?Usage: ./scripts/setup-local.sh <brand-name>}"
SITE_DIR="$HOME/Sites/$BRAND"
THEME_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DB_NAME="${BRAND//-/_}"
ADMIN_USER="admin"
ADMIN_PASS="admin"
ADMIN_EMAIL="admin@${BRAND}.test"
SITE_URL="http://${BRAND}.test"

echo "==> Setting up local WordPress for: $BRAND"

# Create site directory.
if [ -d "$SITE_DIR" ]; then
    echo "    Directory $SITE_DIR already exists — skipping creation."
else
    mkdir -p "$SITE_DIR"
    echo "    Created $SITE_DIR"
fi

# Download WordPress core.
if [ -f "$SITE_DIR/wp-load.php" ]; then
    echo "    WordPress already downloaded — skipping."
else
    wp core download --path="$SITE_DIR"
    echo "    WordPress downloaded."
fi

# Create database.
echo "    Creating database: $DB_NAME"
mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`;" 2>/dev/null || {
    echo "    Warning: Could not create database. Create it manually:"
    echo "    mysql -u root -e \"CREATE DATABASE \`$DB_NAME\`;\""
}

# Create wp-config.php.
if [ -f "$SITE_DIR/wp-config.php" ]; then
    echo "    wp-config.php already exists — skipping."
else
    wp config create \
        --path="$SITE_DIR" \
        --dbname="$DB_NAME" \
        --dbuser="root" \
        --dbpass="" \
        --dbhost="127.0.0.1"
    echo "    wp-config.php created."
fi

# Add VITE_DEV constant for development.
wp config set VITE_DEV true --raw --path="$SITE_DIR" 2>/dev/null || true

# Install WordPress.
if wp core is-installed --path="$SITE_DIR" 2>/dev/null; then
    echo "    WordPress already installed — skipping."
else
    wp core install \
        --path="$SITE_DIR" \
        --url="$SITE_URL" \
        --title="$BRAND" \
        --admin_user="$ADMIN_USER" \
        --admin_password="$ADMIN_PASS" \
        --admin_email="$ADMIN_EMAIL" \
        --skip-email
    echo "    WordPress installed."
fi

# Set permalink structure.
wp rewrite structure '/%postname%/' --path="$SITE_DIR"
wp rewrite flush --path="$SITE_DIR"

# Install and activate plugins.
echo "==> Installing plugins..."
wp plugin install woocommerce --activate --path="$SITE_DIR" 2>/dev/null || echo "    WooCommerce already installed."
wp plugin install seo-by-rank-math --activate --path="$SITE_DIR" 2>/dev/null || echo "    Rank Math already installed."
wp plugin install fluent-smtp --activate --path="$SITE_DIR" 2>/dev/null || echo "    FluentSMTP already installed."
wp plugin install ast-tracking --activate --path="$SITE_DIR" 2>/dev/null || echo "    Advanced Shipment Tracking already installed."

# Remove default plugins.
wp plugin delete hello --path="$SITE_DIR" 2>/dev/null || true
wp plugin delete akismet --path="$SITE_DIR" 2>/dev/null || true

# Enable Australia Post in Advanced Shipment Tracking.
echo "==> Enabling Australia Post shipping provider..."
DB_PREFIX=$(wp db prefix --path="$SITE_DIR" 2>/dev/null || echo "wp_")
wp db query "UPDATE ${DB_PREFIX}woocommerce_shipping_providers SET provider_status = 1 WHERE provider_name = 'Australia Post';" --path="$SITE_DIR" 2>/dev/null || echo "    Australia Post provider — enable manually in wp-admin if this failed."

# Configure WooCommerce.
echo "==> Configuring WooCommerce..."
wp option update woocommerce_currency "AUD" --path="$SITE_DIR"
wp option update woocommerce_default_country "AU:NSW" --path="$SITE_DIR"
wp option update woocommerce_weight_unit "kg" --path="$SITE_DIR"
wp option update woocommerce_dimension_unit "cm" --path="$SITE_DIR"
wp option update woocommerce_calc_taxes "yes" --path="$SITE_DIR"

# Create WooCommerce pages.
wp wc tool run install_pages --user=1 --path="$SITE_DIR" 2>/dev/null || echo "    WooCommerce pages may already exist."

# Disable comments.
wp option update default_comment_status "closed" --path="$SITE_DIR"

# Symlink theme.
THEME_LINK="$SITE_DIR/wp-content/themes/$BRAND"
if [ -L "$THEME_LINK" ]; then
    echo "    Theme symlink already exists."
elif [ -d "$THEME_LINK" ]; then
    echo "    Warning: $THEME_LINK exists as a directory — skipping symlink."
else
    ln -s "$THEME_DIR" "$THEME_LINK"
    echo "    Theme symlinked: $THEME_DIR -> $THEME_LINK"
fi

# Activate theme.
wp theme activate "$BRAND" --path="$SITE_DIR"

echo ""
echo "==> Done! Local site ready at: $SITE_URL"
echo ""
echo "    Admin:    ${SITE_URL}/wp-admin/"
echo "    User:     $ADMIN_USER"
echo "    Password: $ADMIN_PASS"
echo ""
echo "    Next steps:"
echo "    1. cd $(pwd) && npm install && npm run dev"
echo "    2. Visit $SITE_URL in your browser"
