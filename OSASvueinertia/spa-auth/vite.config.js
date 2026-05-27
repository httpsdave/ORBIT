import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './src'),
        },
    },
    publicDir: path.resolve(__dirname, '../public'),
    build: {
        outDir: 'dist',
        emptyOutDir: true,
    },
});
