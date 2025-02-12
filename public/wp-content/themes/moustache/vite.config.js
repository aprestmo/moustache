import { defineConfig } from 'vite'
import liveReload from 'vite-plugin-live-reload'
import { basename } from 'path'

const themeName = basename(__dirname)
const basePath = `/wp-content/themes/${themeName}`

export default defineConfig(({ mode }) => ({
  plugins: [liveReload(['./**/*.php'])],
  server: {
    cors: true,
    strictPort: true,
    port: 5173,
    hmr: {
      host: 'localhost',
      protocol: 'ws',
    },
    origin: 'http://localhost:5173',
    headers: {
      'Access-Control-Allow-Origin': '*',
    },
  },
  base: mode === 'production' ? `${basePath}/dist/` : basePath,
  resolve: {
    alias: {
      '@images': '/src/images',
      '@fonts': '/fonts',
    },
  },
  build: {
    manifest: true,
    outDir: 'dist',
    rollupOptions: {
      input: './src/main.js',
      output: {
        entryFileNames: '[name].[hash].js',
        chunkFileNames: '[name].[hash].js',
        assetFileNames: '[name].[hash][extname]',
      },
    },
  },
}))
