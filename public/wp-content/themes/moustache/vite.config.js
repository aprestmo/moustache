import { defineConfig } from 'vite';
import liveReload from 'vite-plugin-live-reload';
import { basename } from 'path';

const THEME_NAME = basename(__dirname);
const BASE_PATH = `/wp-content/themes/${THEME_NAME}`;

export default defineConfig(({ mode }) => ({
  plugins: [liveReload(['./**/*.php'])],
  server: {
    hmr: {
      host: 'localhost',
    },
  },
	// server: {
  //   host: '0.0.0.0', // Allows access from any network interface, including kampbart.local
  //   port: 5173, // Default Vite port, adjust if necessary
  //   cors: {
  //     origin: 'http://kampbart.local', // Allow requests from kampbart.local
  //     credentials: true, // Allows cookies to be sent
  //   },
  //   hmr: {
  //     host: 'kampbart.local', // Set the HMR (Hot Module Replacement) host to match kampbart.local
  //     port: 5173, // Ensure the port matches your Vite server port
  //   },
  // },
	base: mode === 'production' ? `${BASE_PATH}/dist/` : `${BASE_PATH}`,
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
