import { defineConfig } from 'vite';
import liveReload from 'vite-plugin-live-reload';

export default defineConfig(({ mode }) => ({
  plugins: [liveReload(['./**/*.php'])],
  server: {
    host: 'localhost',
    port: 5173,
    cors: {
      origin: 'http://kampbart.local', // Allow requests from your WordPress site
      credentials: true,
    },
    hmr: {
      host: 'localhost',
    },
  },
  base: process.env.VITE_BASE_PATH || '/',
  publicDir: 'public',
  define: {
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV),
  },
  build: {
    outDir: 'dist',
    rollupOptions: {
      input: 'src/main.js',
      output: {
        entryFileNames: 'assets/[name].js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: 'assets/[name][extname]',
      },
    },
    manifest: true,
  },
}));
