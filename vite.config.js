import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Keep the generated `public/hot` URL stable when the active Wi-Fi network
// changes. Set VITE_DEV_SERVER_HOST to the current LAN IP only when the site
// must be opened from a different device.
const devServerHost = process.env.VITE_DEV_SERVER_HOST || '127.0.0.1';

export default defineConfig({
    optimizeDeps: {
        include: [
            'echarts/core',
            'echarts/charts',
            'echarts/components',
            'echarts/renderers',
        ],
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/admin.css', 'resources/js/admin.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Accept local and LAN connections; HMR still advertises the stable
        // address configured above.
        host: '0.0.0.0',
        hmr: {
            host: devServerHost,
        },
        // The Laravel page may be opened from the LAN IP rather than APP_URL
        // (which is commonly still set to localhost during development).
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
