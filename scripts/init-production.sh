#!/usr/bin/env bash
set -euo pipefail

# ──────────────────────────────────────────────
# init-production.sh — Configure a fresh Hostinger WordPress install.
#
# Usage: ./scripts/init-production.sh brand-name domain.com
#
# Run this over SSH on the Hostinger server, or locally if you
# have WP-CLI configured to connect to the remote site.
# ──────────────────────────────────────────────

BRAND="${1:?Usage: ./scripts/init-production.sh <brand-name> <domain.com>}"
DOMAIN="${2:?Usage: ./scripts/init-production.sh <brand-name> <domain.com>}"

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
CONFIG="$SCRIPT_DIR/config.sh"

# Source config if available for orchestrator/SMTP settings.
ORCHESTRATOR_URL=""
WEBHOOK_SECRET=""
BRAND_COLOR="#0ea5e9"
SES_ACCESS_KEY=""
SES_SECRET_KEY=""
SES_REGION="ap-southeast-2"

if [ -f "$CONFIG" ]; then
    # shellcheck source=config.example.sh
    source "$CONFIG"
fi

WP_PATH="${SSH_PATH:-~/public_html}"

echo "==> Initialising production WordPress for: $BRAND ($DOMAIN)"

# Install and activate plugins.
echo "==> Installing plugins..."
wp plugin install woocommerce --activate --path="$WP_PATH" 2>/dev/null || echo "    WooCommerce already active."
wp plugin install seo-by-rank-math --activate --path="$WP_PATH" 2>/dev/null || echo "    Rank Math already active."
wp plugin install fluent-smtp --activate --path="$WP_PATH" 2>/dev/null || echo "    FluentSMTP already active."
wp plugin install ast-tracking --activate --path="$WP_PATH" 2>/dev/null || echo "    Advanced Shipment Tracking already active."

echo ""
echo "    REMINDER: Manually install these premium plugins via wp-admin:"
echo "    - FunnelKit Pro"
echo "    - Smush Pro"
echo ""

# Remove default plugins.
wp plugin delete hello --path="$WP_PATH" 2>/dev/null || true
wp plugin delete akismet --path="$WP_PATH" 2>/dev/null || true

# Enable Australia Post in Advanced Shipment Tracking.
echo "==> Enabling Australia Post shipping provider..."
DB_PREFIX=$(wp db prefix --path="$WP_PATH" 2>/dev/null || echo "wp_")
wp db query "UPDATE ${DB_PREFIX}woocommerce_shipping_providers SET provider_status = 1 WHERE provider_name = 'Australia Post';" --path="$WP_PATH" 2>/dev/null || echo "    Australia Post provider — enable manually in wp-admin > Shipment Tracking if this failed."

# Configure WooCommerce.
echo "==> Configuring WooCommerce..."
wp option update woocommerce_currency "AUD" --path="$WP_PATH"
wp option update woocommerce_default_country "AU:NSW" --path="$WP_PATH"
wp option update woocommerce_weight_unit "kg" --path="$WP_PATH"
wp option update woocommerce_dimension_unit "cm" --path="$WP_PATH"
wp option update woocommerce_calc_taxes "yes" --path="$WP_PATH"
wp option update woocommerce_email_base_color "$BRAND_COLOR" --path="$WP_PATH"

# Create WooCommerce pages.
wp wc tool run install_pages --user=1 --path="$WP_PATH" 2>/dev/null || echo "    WooCommerce pages may already exist."

# Set permalink structure.
wp rewrite structure '/%postname%/' --path="$WP_PATH"
wp rewrite flush --path="$WP_PATH"

# Disable comments.
wp option update default_comment_status "closed" --path="$WP_PATH"

# Activate theme.
wp theme activate "$BRAND" --path="$WP_PATH" 2>/dev/null || echo "    Theme activation — verify theme is deployed."

# Configure FluentSMTP with Amazon SES.
if [ -n "$SES_ACCESS_KEY" ]; then
    echo "==> Configuring FluentSMTP (Amazon SES)..."
    wp option update fluentmail-settings --format=json --path="$WP_PATH" "$(cat <<SES_JSON
{
    "connections": {
        "primary": {
            "provider_settings": {
                "sender_name": "$BRAND",
                "sender_email": "noreply@$DOMAIN",
                "force_from_name": "yes",
                "force_from_email": "yes",
                "return_path": "yes",
                "access_key": "$SES_ACCESS_KEY",
                "secret_key": "$SES_SECRET_KEY",
                "region": "$SES_REGION"
            },
            "provider": "ses"
        }
    },
    "misc": {
        "log_emails": "yes",
        "log_saved_interval_days": "14",
        "disable_recommendation": "yes"
    }
}
SES_JSON
)" 2>/dev/null || echo "    FluentSMTP config — set up manually in wp-admin if this failed."
fi

# Create WooCommerce REST API keys.
echo "==> Creating WooCommerce REST API keys..."
API_KEYS=$(wp wc customer_key create \
    --user=1 \
    --description="Orchestrator API Key" \
    --permissions="read_write" \
    --path="$WP_PATH" \
    --porcelain 2>/dev/null) || true

if [ -n "$API_KEYS" ]; then
    echo ""
    echo "    API Keys created. Save these — they won't be shown again:"
    echo "    $API_KEYS"
    echo ""
fi

# Create webhooks for orchestrator.
if [ -n "$ORCHESTRATOR_URL" ]; then
    echo "==> Creating webhooks..."
    wp wc webhook create \
        --name="Order Created" \
        --topic="order.created" \
        --delivery_url="${ORCHESTRATOR_URL}/webhooks/${BRAND}/order-created" \
        --secret="$WEBHOOK_SECRET" \
        --status="active" \
        --user=1 \
        --path="$WP_PATH" 2>/dev/null || echo "    order.created webhook — create manually if this failed."

    wp wc webhook create \
        --name="Order Updated" \
        --topic="order.updated" \
        --delivery_url="${ORCHESTRATOR_URL}/webhooks/${BRAND}/order-updated" \
        --secret="$WEBHOOK_SECRET" \
        --status="active" \
        --user=1 \
        --path="$WP_PATH" 2>/dev/null || echo "    order.updated webhook — create manually if this failed."
fi

echo ""
echo "============================================"
echo "  Production setup complete for $BRAND"
echo "============================================"
echo ""
echo "  Remaining manual steps:"
echo ""
echo "  1. Add to wp-config.php:"
echo "     define('DISALLOW_FILE_EDIT', true);"
echo ""
echo "  2. Install premium plugins via wp-admin:"
echo "     - FunnelKit Pro"
echo "     - Smush Pro"
echo ""
echo "  3. Configure Rank Math via its setup wizard."
echo ""
echo "  4. Verify FluentSMTP settings in wp-admin > FluentSMTP."
echo ""
echo "  5. Set up SSL certificate if not already configured."
echo ""
echo "  6. Save the API keys printed above for the orchestrator."
echo ""
