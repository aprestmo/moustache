import { defineConfig } from 'vite'
import { fileURLToPath, URL } from 'url'
import liveReload from 'vite-plugin-live-reload'

export default defineConfig({
  server: {
    cors: true,
    origin: 'http://localhost:5173',
  },

  plugins: [
    liveReload(['./**/*.php']),
  ],

  resolve: {
    alias: {
      '@fonts': fileURLToPath(new URL('./public/fonts', import.meta.url)),
    },
  },

  publicDir: 'public',

  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: 'src/main.js',
    },
  },
})
