import { defineConfig } from 'vite';
// import legacy from '@vitejs/plugin-legacy';
import liveReload from 'vite-plugin-live-reload';

export default defineConfig({
  plugins: [
    // legacy({
    //   targets: ['defaults', 'not IE 11']
    // }),
    liveReload(['./**/*.php'])
  ],
  server: {
    hmr: {
      host: 'localhost',
    },
  },
	publicDir: 'src/public',
  build: {
    outDir: 'dist',
    rollupOptions: {
      input: 'src/main.js',
      output: {
        entryFileNames: 'assets/[name].js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: 'assets/[name][extname]'
      }
    },
    manifest: true,
  },
});
