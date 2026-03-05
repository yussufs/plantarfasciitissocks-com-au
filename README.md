# Brand Theme

Custom WooCommerce theme template for Australian ecommerce brands. Built with Vite, Tailwind CSS, and Svelte 5 on standard WordPress hosting (Hostinger). Fork this repo for each new brand.

## Prerequisites

- [Laravel Valet](https://laravel.com/docs/valet) with Homebrew MySQL
- [WP-CLI](https://wp-cli.org/)
- Node.js 20+

## Quick Start

```bash
# Set up local WordPress site
./scripts/setup-local.sh my-brand

# Install frontend dependencies
npm install

# Start Vite dev server (HMR)
npm run dev

# Seed local WooCommerce products for theme preview
./scripts/seed-products.sh my-brand --count 12

# Visit http://my-brand.test
```

## Deployment

Push to `main` triggers GitHub Actions, which builds assets and rsyncs to Hostinger.

Add these GitHub Secrets to your repo:

- `SSH_HOST` — Hostinger server hostname
- `SSH_USER` — SSH username
- `SSH_KEY` — Private SSH key
- `REMOTE_PATH` — e.g. `~/public_html/wp-content/themes/my-brand`

## Creating a New Brand

1. Fork/clone this repo
2. Update `style.css` — theme name, author, description
3. Update `tailwind.config.js` — brand colours
4. Run `./scripts/setup-local.sh new-brand` for local dev
5. Run `./scripts/init-production.sh new-brand domain.com` on the server
6. Set up GitHub Secrets and push to `main`

## Product Page Components

Product pages use three Svelte components mounted from PHP via `data-config` attributes:

- **ProductGallery** — image carousel with variation switching
- **ProductOptions** — attribute selectors, bundle tiers, add-to-cart
- **ProductReviews** — review grid with lightbox

Components are generic — product-specific data (images, variations, bundles, FAQs) comes from WooCommerce post meta. See [CLAUDE.md](./CLAUDE.md#product-page-components) for the full architecture.

## Reviews

Reviews are stored in `data/reviews.json` (not WooCommerce comments). Each review specifies which products it appears on via `product_slugs`. To add a review:

1. Add an entry to `data/reviews.json`
2. Place any review image in `src/images/reviews/`
3. Set `product_slugs` to matching product slugs, or `["*"]` for all products

See [CLAUDE.md](./CLAUDE.md#reviews) for the full data format and image handling.

## Full Documentation

See [CLAUDE.md](./CLAUDE.md) for architecture, conventions, and detailed reference.
