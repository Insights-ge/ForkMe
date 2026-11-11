import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/filament/admin/theme.css', 'resources/js/sliding-door-builder.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        rolldownOptions: {
            preserveEntrySignatures: 'exports-only',
        },
    },
});
