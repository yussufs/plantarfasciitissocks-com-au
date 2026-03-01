#!/usr/bin/env bash
set -euo pipefail

# ──────────────────────────────────────────────
# seed-products.sh — Seed local WooCommerce products for theme previews.
#
# Usage:
#   ./scripts/seed-products.sh brand-name
#   ./scripts/seed-products.sh brand-name --count 16
#   ./scripts/seed-products.sh --path "$HOME/Sites/brand-name" --count 8 --reset
#
# Notes:
# - Defaults to 12 products.
# - --reset deletes products previously created by this script first.
# ──────────────────────────────────────────────

usage() {
    cat <<'USAGE'
Usage:
  ./scripts/seed-products.sh <brand-name> [--count N] [--reset]
  ./scripts/seed-products.sh --path <wp-site-path> [--count N] [--reset]

Options:
  --path   Absolute path to the local WordPress install (e.g. $HOME/Sites/arthritis-gloves)
  --count  Number of products to seed (default: 12)
  --reset  Delete previously seeded products before reseeding
USAGE
}

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
SITE_PATH=""
BRAND=""
COUNT="12"
RESET="0"

while [ "$#" -gt 0 ]; do
    case "$1" in
        --path)
            [ "${2:-}" ] || { echo "Error: --path requires a value."; usage; exit 1; }
            SITE_PATH="$2"
            shift 2
            ;;
        --count)
            [ "${2:-}" ] || { echo "Error: --count requires a value."; usage; exit 1; }
            COUNT="$2"
            shift 2
            ;;
        --reset)
            RESET="1"
            shift
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            if [ -z "$BRAND" ]; then
                BRAND="$1"
                shift
            else
                echo "Error: Unknown argument '$1'."
                usage
                exit 1
            fi
            ;;
    esac
done

if [ -z "$SITE_PATH" ]; then
    if [ -z "$BRAND" ]; then
        echo "Error: Provide either <brand-name> or --path."
        usage
        exit 1
    fi
    SITE_PATH="$HOME/Sites/$BRAND"
fi

if [ ! -d "$SITE_PATH" ]; then
    echo "Error: WordPress path not found: $SITE_PATH"
    exit 1
fi

if ! [[ "$COUNT" =~ ^[0-9]+$ ]] || [ "$COUNT" -lt 1 ]; then
    echo "Error: --count must be a positive integer."
    exit 1
fi

if ! wp core is-installed --path="$SITE_PATH" >/dev/null 2>&1; then
    echo "Error: WordPress is not installed or not reachable at $SITE_PATH."
    exit 1
fi

if ! wp plugin is-active woocommerce --path="$SITE_PATH" >/dev/null 2>&1; then
    echo "Error: WooCommerce is not active at $SITE_PATH."
    exit 1
fi

echo "==> Seeding WooCommerce products..."
echo "    Site path: $SITE_PATH"
echo "    Product count: $COUNT"
if [ "$RESET" = "1" ]; then
    echo "    Reset existing seeded products: yes"
fi

SEED_PRODUCT_COUNT="$COUNT" \
SEED_PRODUCT_RESET="$RESET" \
wp eval-file "$SCRIPT_DIR/seed-products.php" --path="$SITE_PATH"

SITE_URL="$(wp option get siteurl --path="$SITE_PATH" 2>/dev/null || true)"

echo ""
echo "==> Done."
if [ -n "$SITE_URL" ]; then
    echo "    Shop: ${SITE_URL%/}/shop/"
    echo "    Categories: ${SITE_URL%/}/product-category/"
fi
