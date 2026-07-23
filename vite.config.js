import os from 'node:os';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Laravel's Vite plugin writes this address to `public/hot`.  If it is left
// as localhost/::1, a browser on another device will try to connect to its
// own loopback interface and all Vite assets will fail with ECONNREFUSED.
const lanHost = Object.values(os.networkInterfaces())
    .flat()
    .find(({ family, internal }) => (family === 'IPv4' || family === 4) && !internal)
    ?.address;
const devServerHost = process.env.VITE_DEV_SERVER_HOST || lanHost || 'localhost';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/admin.css', 'resources/js/admin.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Listen on the LAN so the PHP/Vite pair can be opened from another
        // laptop. Set VITE_DEV_SERVER_HOST to override automatic detection.
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
