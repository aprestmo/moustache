import { defineConfig } from 'vite';
// import legacy from '@vitejs/plugin-legacy';
import liveReload from 'vite-plugin-live-reload';

const isProduction = process.env.NODE_ENV === 'production';

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
	base: isProduction ? 'https://kampbart.com/wp-content/themes/moustache/dist/' : '',
	publicDir: 'public',
  build: {
    outDir: 'dist',
    rollupOptions: {
      input: 'src/main.js',
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
        assetFileNames: '[name][extname]'
      }
    },
    manifest: true,
  },
});
