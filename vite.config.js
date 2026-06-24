import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

// Saat dev (`npm run dev`), set VITE_FULL_RELOAD=1 untuk mengaktifkan kembali
// auto full-reload pada perubahan blade/route. Default-nya MATI karena full
// reload yang menyambar saat user sedang klik link akan membatalkan navigasi
// ("loading di tab lalu diam / harus klik dua kali"). CSS & JS tetap di-hot-
// update lewat HMR, jadi iterasi front-end masih nyaman; cukup tekan F5 setelah
// mengedit blade.
const fullReload = process.env.VITE_FULL_RELOAD === "1";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: fullReload,
        }),
    ],
    server: {
        // Mengizinkan semua origin (CORS) selama masa development
        cors: true,

        // Memaksa Vite menggunakan 127.0.0.1 (IPv4) alih-alih [::1] (IPv6) jika diperlukan
        host: "127.0.0.1",

        hmr: {
            // App disajikan di https://kasiro.com, tetapi dev server Vite di
            // http://127.0.0.1:5173. Pin host + clientPort + protokol ws secara
            // eksplisit agar WebSocket HMR tidak putus-nyambung — reconnect yang
            // berulang memicu location.reload() yang ikut membatalkan navigasi.
            host: "127.0.0.1",
            protocol: "ws",
            clientPort: 5173,
        },
    },
});
