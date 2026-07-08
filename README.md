# Kasiro

Kasiro adalah platform SaaS multi-tenant untuk membuat sistem kasir (POS) bermerek untuk UMKM. Setiap toko yang dibuat lewat "studio" mendapat subdomain sendiri (`{namatoko}.kasiro.my.id`), lengkap dengan template tampilan, tema warna, dan kini juga bahasa yang bisa disesuaikan per toko.

## Tech Stack

- **Backend:** Laravel 12, PHP 8.3
- **Frontend:** Blade + Alpine.js + Tailwind CSS (Vite)
- **Database:** MySQL
- **Screenshot generator:** Spatie Browsershot (Puppeteer/headless Chrome)
- **Auth:** Laravel Breeze + Google OAuth (Socialite) + 2FA (Google2FA)

## Fitur Utama

- **Multi-tenancy berbasis subdomain** — domain pusat (`kasiro.my.id`) untuk studio, subdomain per tenant untuk toko masing-masing.
- **Sistem template & tema** — 6 template kasir siap pakai (2 per tema: classic, modern, retro), preview screenshot otomatis via headless Chrome.
- **Localization (id/en)** — Indonesia sebagai bahasa default, English sebagai bahasa kedua. Bahasa studio dan bahasa tiap tenant bisa berbeda; tenant bisa memilih ikut bahasa studio (dinamis) atau bahasa sendiri.
- **Kasir (POS)**, manajemen produk & kategori, laporan, manajemen karyawan & undangan, arsip toko.
- **Login Google + 2FA** untuk keamanan akun studio.

## Setup Lokal

1. Clone repo, lalu install dependency:
   ```bash
   composer install
   npm install
   ```
2. Salin `.env.example` menjadi `.env`, lalu isi `APP_KEY`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Sesuaikan koneksi database (`DB_*`) di `.env`, lalu buat databasenya.
4. Migrate + seed (data awal: 6 template, akun demo `owner@kasiro.my.id` / `password` dengan tenant `kopisenja` & `berkahmart`):
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```
5. Jalankan dev server:
   ```bash
   npm run dev
   php artisan serve
   ```
6. (Opsional) Generate ulang screenshot template/tenant kalau butuh preview gambar:
   ```bash
   php artisan templates:screenshots --queue
   php artisan tenants:screenshots
   php artisan queue:work   # perlu jalan supaya job screenshot diproses
   ```

> Domain lokal (Laragon) memakai `kasiro.test`; domain produksi memakai `kasiro.my.id`. Pastikan `TENANCY_CENTRAL_DOMAIN` di `.env` sesuai environment yang dipakai, dan subdomain wildcard (`*.kasiro.test` / `*.kasiro.my.id`) sudah diarahkan ke server lokal/VPS.

## Testing

```bash
php artisan test
```

## Deployment (VPS)

Aplikasi production berjalan di VPS (`/var/www/kasiro`, branch `main`, nginx + php8.3-fpm + MySQL).

Alur deploy standar untuk perubahan kode (migration aditif, tidak menghapus data):

```bash
ssh -i <path-ke-key.pem> ubuntu@<ip-vps>
cd /var/www/kasiro

git pull origin main
php artisan migrate --force        # aman, hanya menjalankan migration baru
npm run build                      # rebuild CSS/JS kalau ada perubahan tampilan
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear
```

Kalau ada template/tenant baru yang belum punya screenshot:

```bash
php artisan templates:screenshots --slug=<slug> --queue
php artisan tenants:screenshots --subdomain=<subdomain>
```

### Reset Total (Database + Seeder + Migrate + Screenshot)

Gunakan ini hanya kalau memang ingin mengembalikan aplikasi ke kondisi awal (misalnya lingkungan demo/staging). **Ini menghapus SEMUA data secara permanen dan tidak bisa di-undo** — semua akun, toko, transaksi yang sudah ada akan hilang dan digantikan hanya dengan data seed (template + akun demo).

```bash
# 1. Reset total: drop semua tabel, migrate ulang dari nol, lalu seed ulang
php artisan migrate:fresh --seed --force

# 2. Re-link storage (symlink public/storage -> storage/app/public)
php artisan storage:link

# 3. Generate ulang screenshot semua template
php artisan templates:screenshots --queue

# 4. Generate ulang screenshot semua tenant aktif (dari seeder demo)
php artisan tenants:screenshots

# 5. Bersihkan cache Laravel
php artisan config:clear && php artisan view:clear && php artisan route:clear && php artisan cache:clear
```

Catatan:
- `--force` wajib untuk migration/seeder di environment production.
- Langkah screenshot memakai job queue (Browsershot/headless Chrome) — pastikan queue worker aktif (`sudo systemctl status kasiro-queue` di VPS) dan tunggu beberapa detik sebelum mengecek hasilnya.
- Setelah reset, data yang tersedia hanya: user `test@example.com`, akun demo `owner@kasiro.my.id` / `password` dengan tenant `kopisenja` & `berkahmart`, dan 6 template kasir.

### Catatan RAM saat build di VPS

`npm run build` aman dijalankan langsung di VPS selama RAM mencukupi (cek dulu dengan `free -h`). Kalau instance sedang RAM kecil / tidak ada swap, build bisa membuat proses lain (nginx, php-fpm, sshd) kehabisan memori. Alternatif paling aman: build di lokal, lalu upload hasilnya:

```bash
# Di lokal
npm run build

# Upload ke VPS
scp -i <path-ke-key.pem> -r public/build ubuntu@<ip-vps>:/tmp/kasiro-build-new

# Di VPS
cd /var/www/kasiro
mv public/build public/build.bak
mv /tmp/kasiro-build-new public/build
```

## License

Proprietary — internal project.
