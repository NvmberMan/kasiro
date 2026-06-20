import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    server: {
        // Mengizinkan semua origin (CORS) selama masa development
        cors: true,

        // Memaksa Vite menggunakan 127.0.0.1 (IPv4) alih-alih [::1] (IPv6) jika diperlukan
        host: "127.0.0.1",

        hmr: {
            // Jika Anda mengakses HTTPS, WebSocket HMR sebaiknya disesuaikan
            host: "127.0.0.1",
        },
    },
});
