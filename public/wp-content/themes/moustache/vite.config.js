import { defineConfig } from 'vite';
import liveReload from 'vite-plugin-live-reload';

export default defineConfig(({ mode }) => ({
  plugins: [liveReload(['./**/*.php'])],
  server: {
    hmr: {
      host: 'localhost',
    },
  },
	base: process.env.VITE_BASE_URL,
  // publicDir: 'public',
  build: {
		manifest: true,
    outDir: 'dist',
    rollupOptions: {
      input: './src/main.js',
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
        assetFileNames: '[name][extname]',
      },
    },
  },
}));
