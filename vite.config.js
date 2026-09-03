import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue(),
    ],
    server: {
        host: '0.0.0.0', // يخلي السيرفر يسمع لأي جهاز على الشبكة
        port: 5173,
        strictPort: true,
        hmr: {
            host: '192.168.100.3', // ضعي هنا IP جهازك في الـ Wi-Fi
        },
    },
});


