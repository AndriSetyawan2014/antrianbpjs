import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/variables.css',
                'resources/css/app.css',
                'resources/css/index.css',
                'resources/css/vclaim.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
