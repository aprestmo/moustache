// https://vitejs.dev/config/
// http://localhost:3005 is serving Vite on development but accessing it directly will be empty

import { defineConfig } from 'vite'
import liveReload from 'vite-plugin-live-reload'

const { resolve, basename } = require('path')

const basePath = basename(__dirname)
const themePath = `/wp-content/themes/${basePath}`

export default defineConfig({
  plugins: [
    liveReload([`${__dirname}/*.php`, `${__dirname}/(lib|partials)/**/*.php`]),
  ],
  root: 'src',
  base:
    process.env.APP_ENV === 'development'
      ? `${themePath}/src/`
      : `${themePath}/dist/`,
  resolve: {
    alias: {
      '@images': resolve(`${themePath}`, 'public/'),
    },
  },
  build: {
    // output dir for production build
    outDir: resolve(__dirname, './dist'),
    emptyOutDir: true,

    // emit manifest so PHP can find the hashed files
    manifest: true,

    // esbuild target
    target: 'modules',

    // our entry
    rollupOptions: {
      input: '/main.js',
    },
  },
  server: {
    // required to load scripts from custom host
    cors: true,

    // we need a strict port to match on PHP side
    strictPort: true,
    port: 3005,
  },
})
