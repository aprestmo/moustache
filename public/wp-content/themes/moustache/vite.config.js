import { defineConfig } from 'vite';
import liveReload from 'vite-plugin-live-reload';

const THEME_NAME = 'moustache';

export default defineConfig(({ mode }) => ({
  plugins: [liveReload(['./**/*.php'])],
  server: {
    hmr: {
      host: 'localhost',
    },
  },
  base: mode === 'production' ? `/wp-content/themes/${THEME_NAME}/dist/` : '/',
  build: {
		manifest: true,
    outDir: 'dist',
    rollupOptions: {
      input: './src/main.js',
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
        assetFileNames: '[name].[hash][extname]',
      },
    },
  },
}));
