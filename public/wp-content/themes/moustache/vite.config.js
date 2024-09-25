import { defineConfig } from 'vite';
import liveReload from 'vite-plugin-live-reload';
import { basename } from 'path';

const themeName = basename(__dirname);
const basePath = `/wp-content/themes/${themeName}`;

export default defineConfig(({ mode }) => ({
  plugins: [liveReload(['./**/*.php'])],
  server: {
    hmr: {
      host: 'localhost',
    },
  },
	base: mode === 'production' ? `${basePath}/dist/` : basePath,
	resolve: {
		alias: {
			'@images': '/src/images',
			'@fonts': '/public/fonts',
		}
	},
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
