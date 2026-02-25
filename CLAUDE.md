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

2. `src/js/app.js` imports the component and mounts it:
   ```js
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

## Scripts Reference

| Script | Usage | Description |
|---|---|---|
| `setup-local.sh` | `./scripts/setup-local.sh brand-name` | Create local WordPress dev environment |
| `pull-db.sh` | `./scripts/pull-db.sh` | Pull production DB to local, run search-replace |
| `init-production.sh` | `./scripts/init-production.sh brand-name domain.com` | Configure fresh Hostinger WordPress install |
| `config.example.sh` | Copy to `config.sh` | Configuration template (gitignored) |
