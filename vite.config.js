import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";
import path from 'path'; // Import the path module

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: [`resources/views/**/*`],
            }),
            tailwindcss(),
        ],
        server: {
            cors: true,
        },
        define: {
            'import.meta.env.PUSHER_APP_KEY': JSON.stringify(env.PUSHER_APP_KEY),
            'import.meta.env.PUSHER_APP_CLUSTER': JSON.stringify(env.PUSHER_APP_CLUSTER),
            __VUE_OPTIONS_API__: true,
            __VUE_PROD_DEVTOOLS__: false,
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
        },
        resolve: {
            alias: {
                'vue': path.resolve(__dirname, 'node_modules/vue/dist/vue.esm-bundler.js')
            }
        }
    };
});