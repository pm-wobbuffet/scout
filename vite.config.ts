import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from "@tailwindcss/vite";
import { resolve } from 'node:path';
import { defineConfig } from 'vite';
import vueDevTools from 'vite-plugin-vue-devtools'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vueDevTools({
            appendTo: 'resources/js/app.js'
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            '@images': path.resolve(__dirname, './resources/images'),
            '@resoures': path.resolve(__dirname, './resources'),
            'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
});
