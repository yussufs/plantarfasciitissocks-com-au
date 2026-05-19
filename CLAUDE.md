# CLAUDE.md — Brand Theme

## Project Context

This is a custom WordPress/WooCommerce theme for an Australian ecommerce brand hosted on Hostinger. It's one of several independent brand sites, each with its own Git repo containing just the theme. A future SvelteKit orchestrator will connect to each brand via the WooCommerce REST API for centralised order management.

## Architecture

- **Standard WordPress** — no Bedrock, no Composer for plugins. Standard `wp-content/themes/` directory structure.
- **Classic theme** — not a block/FSE theme. PHP templates for page structure.
- **Vite + Tailwind CSS + Svelte 5** — modern frontend tooling compiled to static assets.
- **Git tracks the theme only** — plugins are managed via wp-admin, WordPress core is not version-controlled.

## Svelte Component Pattern

Svelte components are used for interactive UI elements only. Page structure stays in PHP.

**How it works:**

1. PHP template renders a mount point with data attributes:
   ```php
   <div id="my-component" data-config='<?php echo esc_attr( wp_json_encode( $data ) ); ?>'></div>
   ```

2. `src/js/app.ts` imports the component and mounts it:
   ```ts
   import MyComponent from '../components/MyComponent.svelte';
   import { mount } from 'svelte';

   const el = document.getElementById('my-component');
   if (el) {
     mount(MyComponent, {
       target: el,
       props: JSON.parse(el.dataset.config || '{}'),
     });
   }
   ```

3. Components use Svelte 5 runes syntax (`$state`, `$derived`, `$effect`).
4. Components are client-side only — no SSR.

## How Vite Works

### Development

Add `define('VITE_DEV', true);` to `wp-config.php`. Assets load from `http://localhost:5173` with HMR.

Run `npm run dev` to start the Vite dev server.

### Production

Vite builds to `dist/` with a manifest at `dist/.vite/manifest.json`. The PHP Vite loader in `functions.php` reads the manifest and enqueues hashed filenames via `wp_enqueue_style`/`wp_enqueue_script`.

**Never hardcode asset paths** — always go through the Vite manifest.

## Local Development

### Prerequisites

- Laravel Valet (provides `.test` domains and nginx)
- Homebrew MySQL
- WP-CLI
- Node.js 20+

### Setup

```bash
./scripts/setup-local.sh brand-name
npm install
npm run dev
```

### Pull production data

```bash
cp scripts/config.example.sh scripts/config.sh
# Fill in SSH details
./scripts/pull-db.sh
```

## Deployment

Push to `main` triggers GitHub Actions:

1. Checks out code
2. Runs `npm ci && npm run build`
3. Rsyncs to Hostinger via SSH

**Deployed** (goes to Hostinger):
- All PHP files (templates, functions.php, style.css)
- `dist/` (built assets)
- `template-parts/`
- `woocommerce/`
- `screenshot.png`

**Excluded** (dev-only):
- `node_modules/`, `src/`, `.git/`, `.github/`
- `scripts/`
- Config files: `vite.config.js`, `svelte.config.js`, `tailwind.config.js`, `postcss.config.js`
- `package.json`, `package-lock.json`
- `CLAUDE.md`, `README.md`

### Required GitHub Secrets

| Secret | Description |
|---|---|
| `SSH_HOST` | Hostinger server hostname |
| `SSH_USER` | SSH username |
| `SSH_KEY` | Private SSH key |
| `REMOTE_PATH` | Remote theme path, e.g. `~/public_html/wp-content/themes/brand-name` |

## Product Page Components

The single product page (`woocommerce/single-product.php`) uses three Svelte components mounted via the standard data-attributes pattern. All components are **generic** — the same component works for every product, with product-specific data passed as props from PHP.

### Architecture

```
PHP: brand_theme_get_product_svelte_data($product)
  → JSON-encodes images, variations, attributes, bundles, pricing
  → Passed via data-config to mount points

Mount points in single-product.php:
  <div id="product-gallery">   → ProductGallery.svelte
  <div id="product-options">   → ProductOptions.svelte
  <div id="product-reviews">   → ProductReviews.svelte (via template part)
```

### Component communication

`ProductOptions` dispatches a `product:variation-changed` custom DOM event when the user selects a variation. `ProductGallery` listens for this event and updates the displayed image. No shared Svelte state — components communicate through the DOM.

### Sub-components

- `ColorSwatches.svelte` — colour attribute picker with hex swatches
- `AttributeSelector.svelte` — dropdown for non-colour attributes (size, etc.)
- `BundleSelector.svelte` — tiered bulk purchase options

### Per-product customisation (WP post meta)

Product-specific data is stored as WooCommerce post meta, editable in wp-admin:

| Meta key | Type | Description |
|---|---|---|
| `_brand_bundle_tiers` | JSON | Bulk pricing tiers (qty + discount) |
| `_brand_testimonial` | JSON | Quote + author displayed on product page |
| `_brand_delivery_days` | string | Estimated delivery days |
| `_brand_shipping_info` | string | Shipping details text |
| `_brand_faqs` | JSON array | Product-specific FAQ items |

### Per-product template overrides

To customise a section for a specific product without touching post meta, use `get_template_part()` with the product slug:

```php
// In woocommerce/single-product.php
get_template_part( 'template-parts/content/single-product/hero', $product->get_slug() );
```

WordPress automatically looks for `hero-{slug}.php` first, then falls back to `hero.php`. Create the slug-specific file only for products that need it:

```
template-parts/content/single-product/
├── hero.php                              ← default (all products)
├── hero-cloud-knit-relief-gloves.php     ← override for this product only
```

No post meta, no conditionals — just a file naming convention.

### Adding a new product component

1. Create `src/components/MyComponent.svelte` (Svelte 5 runes syntax).
2. Add a mount point in the PHP template:
   ```php
   <div id="my-component" data-config='<?php echo esc_attr( wp_json_encode( $data ) ); ?>'></div>
   ```
3. Mount it in `src/js/app.ts`:
   ```ts
   import MyComponent from '../components/MyComponent.svelte';
   const el = document.getElementById('my-component');
   if (el) {
     mount(MyComponent, { target: el, props: JSON.parse(el.dataset.config || '{}') });
   }
   ```
4. To communicate with other product components, use custom DOM events (`window.dispatchEvent` / `window.addEventListener`).

## Reviews

Reviews are **not** WooCommerce comments. They live in a static JSON file checked into the repo.

### Data file

`data/reviews.json` — array of review objects:

```json
{
  "id": 1,
  "author": "Sarah M.",
  "location": "Sydney, NSW",
  "rating": 5,
  "text": "Great product, highly recommend!",
  "image": "review-sarah.jpg",
  "product_slugs": ["*"],
  "featured": true,
  "date": "2025-11-15T00:00:00+11:00"
}
```

- `product_slugs` — array of WooCommerce product slugs this review appears on. Use `["*"]` for all products.
- `image` — filename in `src/images/reviews/`. Served via Vite in dev, built to `dist/images/reviews/` in production.
- `featured` — highlighted in the review grid.

### How it works

1. **PHP helper** `brand_theme_get_reviews($product_slug)` in `functions.php` reads `data/reviews.json` and filters by product slug (cached per request).
2. **Template part** `template-parts/content/single-product/reviews.php` builds the config (reviews, avg rating, count) and renders the mount point + JSON-LD structured data for SEO.
3. **Svelte component** `ProductReviews.svelte` displays the review grid with lightbox for images.

### Adding a review

1. Add an entry to `data/reviews.json`.
2. If the review has an image, place it in `src/images/reviews/` (and optionally provide 400w/800w variants + WebP).
3. Set `product_slugs` to the relevant product slugs, or `["*"]` for all products.
4. The review will appear automatically on matching product pages.

### Review images

`brand_theme_get_review_image_data($filename)` in `functions.php` builds responsive image data:

- **Dev mode**: served from `http://localhost:5173/src/images/reviews/`
- **Production**: served from `dist/images/reviews/` with srcset (400w/800w) and WebP variants

## WooCommerce Template Overrides

To override a WooCommerce template:

1. Find the original in `wp-content/plugins/woocommerce/templates/`
2. Copy it to `woocommerce/` in this theme, keeping the subdirectory structure
3. Edit your copy

Only override what you need. WooCommerce will flag outdated overrides in wp-admin.

## Plugin Notes

| Plugin | Role | Notes |
|---|---|---|
| **WooCommerce** | Ecommerce engine | Template overrides go in `woocommerce/` |
| **FunnelKit Pro** | Checkout/funnels | Configured in wp-admin, may override checkout templates |
| **Rank Math** | SEO | Use proper semantic HTML, meta handled by plugin |
| **Smush Pro** | Image optimisation/CDN | Use standard WP image functions |
| **FluentSMTP** | Transactional email | Configured via wp-admin or init script |

## Conventions

- **Styles**: Tailwind CSS utility classes. Minimal custom CSS in `src/css/app.css`.
- **Svelte**: Svelte 5 runes syntax (`$state`, `$derived`, `$effect`). Client-side only.
- **JS**: Vanilla JS where Svelte isn't needed. No jQuery for new code.
- **PHP**: Proper WordPress escaping on all output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`). Use `get_template_part()` for reusable pieces.
- **WooCommerce region**: AUD, AU:NSW, kg, cm, tax enabled.

## What NOT to Do

- No Bedrock or Composer for plugins
- No Blade, Twig, or other PHP template engines
- No page builders (Elementor, Divi, etc.)
- No jQuery for new code
- No committing `node_modules/` or `dist/`
- No block theme / FSE
- No editing WordPress core or plugin files
- No SSR for Svelte components
- **Do not run git commands** — the user handles all git operations (staging, commits, pushes, branches). Make the file changes only.

## Scripts Reference

| Script | Usage | Description |
|---|---|---|
| `setup-local.sh` | `./scripts/setup-local.sh brand-name` | Create local WordPress dev environment |
| `pull-db.sh` | `./scripts/pull-db.sh` | Pull production DB to local, run search-replace |
| `seed-products.sh` | `./scripts/seed-products.sh brand-name --count 12` | Seed dummy WooCommerce products/categories for local theme previews |
| `init-production.sh` | `./scripts/init-production.sh brand-name domain.com` | Configure fresh Hostinger WordPress install |
| `config.example.sh` | Copy to `config.sh` | Configuration template (gitignored) |
