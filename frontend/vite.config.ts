import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueJsx(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  optimizeDeps: {
    include: ['bootstrap-vue-3']
  },
  server: {
    host: '0.0.0.0',  // Permite conexões de qualquer interface
    port: 3000,        // A mesma porta que o Vite está a usar
    strictPort: true   // Garante que a porta 5173 está disponível
  }
})
