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

  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: 'src/main.js',
    },
  },
})
