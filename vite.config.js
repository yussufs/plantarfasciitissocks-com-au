import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import path from 'path';

export default defineConfig({
  plugins: [svelte()],
  build: {
    outDir: 'dist',
    manifest: true,
    rollupOptions: {
      input: {
        app: path.resolve(__dirname, 'src/js/app.ts'),
      },
    },
  },
  server: {
    port: 5173,
    strictPort: true,
    cors: true,
  },
});
