import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],

  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },

  server: {
    port: 5173,
    open: true, // Öffnet Browser automatisch

    proxy: {
      '/api': {
        // Für Windows XAMPP mit VirtualHost:
        target: 'http://modernquiz.local',

        // ODER für Windows XAMPP ohne VirtualHost:
        // target: 'http://localhost/ModernQuiz/public',

        // Für Debian Production:
        // target: 'http://your-domain.com',

        changeOrigin: true,
        secure: false
      }
    }
  },

  build: {
    outDir: '../public/dist',
    emptyOutDir: true,
    rollupOptions: {
      output: {
        manualChunks: {
          'vendor': ['vue', 'vue-router', 'pinia'],
        }
      }
    }
  }
})
