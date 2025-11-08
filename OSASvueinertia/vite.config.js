import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import viteCompression from 'vite-plugin-compression';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        // Gzip compression
        viteCompression({
            algorithm: 'gzip',
            ext: '.gz',
            threshold: 1024, // Only compress files larger than 1KB
            deleteOriginFile: false,
        }),
        // Brotli compression for even better compression
        viteCompression({
            algorithm: 'brotliCompress',
            ext: '.br',
            threshold: 1024,
            deleteOriginFile: false,
        }),
    ],
    build: {
        // Enable minification
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.log in production
                drop_debugger: true,
            },
        },
        // Optimize chunk size
        rollupOptions: {
            output: {
                entryFileNames: 'assets/[name].[hash].js',
                chunkFileNames: 'assets/[name].[hash].js',
                assetFileNames: 'assets/[name].[hash].[ext]',
                // Code splitting for better caching
                manualChunks: {
                    vendor: ['vue', '@inertiajs/vue3'],
                    charts: ['chart.js', 'vue-chartjs'],
                    calendar: ['@fullcalendar/core', '@fullcalendar/daygrid', '@fullcalendar/vue3'],
                },
            },
        },
        // Increase chunk size warning limit
        chunkSizeWarningLimit: 1000,
        // Enable CSS code splitting
        cssCodeSplit: true,
    },
    // Optimize dependencies
    optimizeDeps: {
        include: ['vue', '@inertiajs/vue3', 'axios'],
    },
});
