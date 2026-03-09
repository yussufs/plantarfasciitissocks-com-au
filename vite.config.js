import { defineConfig } from "vite";
import { svelte } from "@sveltejs/vite-plugin-svelte";
import { execFileSync } from "child_process";
import path from "path";

function copyIconsPlugin() {
  const script = "scripts/copy-icons.mjs";

  function run() {
    try {
      execFileSync("node", [script], { stdio: "inherit" });
    } catch {
      // Error already printed by the script.
    }
  }

  return {
    name: "copy-icons",
    buildStart() {
      run();
    },
    configureServer(server) {
      server.watcher.add(path.resolve(script));
      server.watcher.on("change", (file) => {
        if (file.endsWith(script)) {
          run();
        }
      });
    },
  };
}

export default defineConfig({
  plugins: [svelte(), copyIconsPlugin()],
  build: {
    outDir: "dist",
    manifest: true,
    rollupOptions: {
      input: {
        app: path.resolve(__dirname, "src/js/app.ts"),
      },
    },
  },
  server: {
    port: 5173,
    strictPort: true,
    cors: true,
  },
});
