#!/usr/bin/env node

/**
 * Image optimization build script.
 *
 * Reads source images from src/images/, generates multiple widths + WebP
 * variants, and writes optimized output to dist/images/.
 *
 * Run automatically via `npm run build` or standalone: node scripts/optimize-images.mjs
 */

import sharp from 'sharp';
import { readdir, mkdir, stat } from 'fs/promises';
import path from 'path';

const SRC_DIR = 'src/images';
const OUT_DIR = 'dist/images';

const WIDTHS = [400, 800, 1200, 1600];
const QUALITY = { jpg: 80, png: 80, webp: 80 };

// Extensions that get resized + WebP variants.
const RASTER_EXTS = new Set(['.jpg', '.jpeg', '.png']);
// Extensions copied as-is (already optimized by ViteImageOptimizer).
const PASSTHROUGH_EXTS = new Set(['.svg', '.gif', '.ico', '.webp', '.avif']);

async function getFiles(dir, base = dir) {
  const entries = await readdir(dir, { withFileTypes: true });
  const files = [];

  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      files.push(...await getFiles(fullPath, base));
    } else if (!entry.name.startsWith('.')) {
      files.push(path.relative(base, fullPath));
    }
  }

  return files;
}

async function processImage(relPath) {
  const srcPath = path.join(SRC_DIR, relPath);
  const ext = path.extname(relPath).toLowerCase();
  const nameNoExt = relPath.slice(0, -ext.length);
  const outSubDir = path.join(OUT_DIR, path.dirname(relPath));

  await mkdir(outSubDir, { recursive: true });

  // Passthrough files — just copy (ViteImageOptimizer handles SVG etc.).
  if (PASSTHROUGH_EXTS.has(ext)) {
    const { default: fs } = await import('fs');
    fs.copyFileSync(srcPath, path.join(OUT_DIR, relPath));
    return;
  }

  if (!RASTER_EXTS.has(ext)) return;

  const image = sharp(srcPath);
  const metadata = await image.metadata();
  const originalWidth = metadata.width || 1600;

  // Generate each width (skip sizes larger than the original).
  for (const width of WIDTHS) {
    if (width > originalWidth) continue;

    const resized = sharp(srcPath).resize(width);
    const suffix = `-${width}w`;

    // Original format.
    if (ext === '.png') {
      await resized.clone().png({ quality: QUALITY.png }).toFile(
        path.join(OUT_DIR, `${nameNoExt}${suffix}.png`)
      );
    } else {
      await resized.clone().jpeg({ quality: QUALITY.jpg, mozjpeg: true }).toFile(
        path.join(OUT_DIR, `${nameNoExt}${suffix}.jpg`)
      );
    }

    // WebP variant.
    await resized.clone().webp({ quality: QUALITY.webp }).toFile(
      path.join(OUT_DIR, `${nameNoExt}${suffix}.webp`)
    );
  }

  // Also generate a full-size WebP for the original dimensions.
  await sharp(srcPath).webp({ quality: QUALITY.webp }).toFile(
    path.join(OUT_DIR, `${nameNoExt}.webp`)
  );

  // Copy original at full size (optimized).
  if (ext === '.png') {
    await sharp(srcPath).png({ quality: QUALITY.png }).toFile(
      path.join(OUT_DIR, relPath)
    );
  } else {
    await sharp(srcPath).jpeg({ quality: QUALITY.jpg, mozjpeg: true }).toFile(
      path.join(OUT_DIR, relPath)
    );
  }
}

async function main() {
  // Check src dir exists.
  try {
    await stat(SRC_DIR);
  } catch {
    console.log('No src/images directory — skipping image optimization.');
    return;
  }

  const files = await getFiles(SRC_DIR);

  if (files.length === 0) {
    console.log('No images found in src/images/ — skipping.');
    return;
  }

  await mkdir(OUT_DIR, { recursive: true });

  console.log(`Optimizing ${files.length} image(s)...`);

  for (const file of files) {
    await processImage(file);
    console.log(`  ✓ ${file}`);
  }

  console.log('Image optimization complete.');
}

main().catch((err) => {
  console.error('Image optimization failed:', err);
  process.exit(1);
});
