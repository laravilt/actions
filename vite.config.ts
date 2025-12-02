import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve, dirname } from 'path'
import { fileURLToPath } from 'url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

export default defineConfig({
  plugins: [vue()],
  build: {
    lib: {
      entry: resolve(__dirname, 'resources/js/app.ts'),
      name: 'LaraviltActions',
      formats: ['es', 'umd'],
      fileName: (format) => `actions.${format}.js`,
    },
    rollupOptions: {
      external: ['vue', '@inertiajs/vue3', 'radix-vue', 'reka-ui', 'lucide-vue-next', /@laravilt\/.*/],
      output: {
        globals: {
          vue: 'Vue',
          '@inertiajs/vue3': 'InertiaVue3',
          'radix-vue': 'RadixVue',
          'reka-ui': 'RekaUI',
          'lucide-vue-next': 'LucideVueNext',
        },
        assetFileNames: (assetInfo) => {
          if (assetInfo.name === 'style.css') return 'style.css'
          return assetInfo.name as string
        },
      },
    },
    outDir: 'dist',
    emptyOutDir: true,
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
    },
  },
})
