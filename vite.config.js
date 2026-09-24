import { defineConfig } from 'vite'
import liveReload from 'vite-plugin-live-reload'

export default defineConfig({
  server: {
    cors: true,
    origin: 'http://localhost:5173',
  },

  plugins: [
    liveReload(['./**/*.php']),
  ],

  publicDir: 'public',

  // Keep public assets relative to the built CSS/JS so they resolve from
  // /wp-content/themes/moustache/dist/ in WordPress, not from the site root.
  base: './',

  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: 'src/main.js',
    },
  },
})
