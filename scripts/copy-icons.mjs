#!/usr/bin/env node

/**
 * Copy required Lucide icons from node_modules to dist/icons/.
 *
 * Add icon names to the ICONS array below as needed. Run via `npm run build`.
 * Browse all available icons at https://lucide.dev/icons
 */

import { copyFileSync, mkdirSync, existsSync } from 'fs';
import path from 'path';

const SRC = 'node_modules/lucide-static/icons';
const OUT = 'dist/icons';

// ── Add icons here as you need them ─────────────────────────────
const ICONS = [
  'check',
  'chevron-down',
  'droplets',
  'flame',
  'heart-pulse',
  'help-circle',
  'package',
  'refresh-ccw',
  'search',
  'shield-check',
  'shopping-cart',
  'star',
  'target',
  'truck',
  'x',
];
// ─────────────────────────────────────────────────────────────────

mkdirSync(OUT, { recursive: true });

let copied = 0;

for (const name of ICONS) {
  const src = path.join(SRC, `${name}.svg`);
  const dest = path.join(OUT, `${name}.svg`);

  if (!existsSync(src)) {
    console.error(`  ✗ Icon not found: ${name} (check https://lucide.dev/icons)`);
    process.exit(1);
  }

  copyFileSync(src, dest);
  copied++;
}

console.log(`Copied ${copied} Lucide icon(s) to ${OUT}/`);
