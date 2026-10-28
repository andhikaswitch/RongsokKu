import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // Hampir seluruh halaman tanpa JavaScript; satu-satunya pengecualian adalah
            // peta interaktif + GPS di halaman "Atur titik lokasi".
            input: ['resources/css/app.css', 'resources/js/atur-lokasi.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
