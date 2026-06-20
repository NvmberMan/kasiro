# Kasiro — Platform SaaS Pembuat Aplikasi Kasir (Multi-Tenant)

> Dokumen ini adalah **PRD (requirements-phase)**: menjelaskan *apa* yang harus benar agar produk berhasil dan *mengapa*, lalu berhenti sebelum *bagaimana* mengimplementasikannya. Dekomposisi implementasi (file, kelas, urutan kerja) didelegasikan ke `/plan`.
>
> Bahasa: Indonesia, istilah teknis tetap Inggris sesuai standar industri (tenant, middleware, migration, global scope, dll).

---

## 1. Executive Summary

Kasiro adalah platform **SaaS multi-tenant berbasis Laravel** yang memungkinkan pemilik usaha kecil (warung, toko kelontong, kafe, dll) membuat **aplikasi kasir (POS) milik mereka sendiri tanpa coding** — analog dengan WordPress/Wix, tapi terspesialisasi untuk POS.

Setiap aplikasi kasir yang dibuat menjadi sebuah **tenant** yang diakses lewat **subdomain** dari domain utama (mis. `warungbudi.kasiro.com`). Seluruh tenant dilayani oleh **satu codebase Laravel yang sama**; perbedaan antar-tenant murni berasal dari **data konfigurasi di database** (layout, theme, color palette, nama brand, subdomain, logo) — **bukan** dari generate file/kode terpisah per tenant.

**Value proposition:** UMKM mendapat aplikasi kasir bermerek sendiri dalam hitungan menit, tanpa biaya developer dan tanpa infrastruktur, sambil tetap memegang kendali atas branding dan data mereka.

**Batasan inti (deliberate constraint):** kustomisasi tenant **terbatas pada presentasi** (layout, theme, palette, brand, subdomain, logo). Tenant **tidak bisa** mengubah business logic atau struktur halaman. Batasan ini yang membuat model "satu codebase melayani semua" tetap dapat dipelihara.

---

## 2. Problem Statement & Goals

### Problem
Pemilik UMKM yang ingin mendigitalisasi transaksi kasir menghadapi pilihan yang buruk: aplikasi POS jadi (off-the-shelf) terasa generik, tidak bermerek, dan sering berlangganan mahal; sementara membuat aplikasi kasir sendiri membutuhkan developer dan biaya yang di luar jangkauan. Akibatnya banyak yang tetap memakai pencatatan manual/spreadsheet, kehilangan kontrol stok dan rekap penjualan.

### Target User
UMKM Indonesia skala kecil — warung, toko, kafe — yang **non-teknis**, sensitif harga, dan menginginkan identitas brand sendiri pada alat operasional mereka.

### Goals (Business)
- **G1** — Memberi UMKM cara membuat POS bermerek sendiri **tanpa coding** dan tanpa biaya developer.
- **G2** — Menjaga **single codebase, single database** agar biaya operasional platform rendah dan skalabel di tahap awal.
- **G3** — Membuktikan bahwa model "config-driven tenant" cukup untuk kebutuhan POS UMKM (validasi hipotesis sebelum investasi monetisasi).

### Non-Goals (tahap ini)
- Bukan platform POS enterprise (multi-cabang kompleks, akuntansi penuh, integrasi pajak).
- Bukan page-builder bebas (tidak ada custom logic / custom layout per tenant).
- Monetisasi **ditunda** (lihat §5 Scope).

---

## 3. Evidence

> **Assumption — needs validation.** Pemilik produk menyatakan saat ini belum ada riset formal atau data kuantitatif; keputusan dibuat atas dasar asumsi pasar.

Asumsi yang perlu divalidasi sebelum/awal development:
- **A1** — UMKM kecil cukup termotivasi untuk membuat POS bermerek sendiri (vs memakai POS generik gratis yang sudah ada di pasar). *Validasi via: wawancara 8–12 pemilik UMKM + prototype activation test.*
- **A2** — Kustomisasi terbatas (theme/palette/logo/brand) sudah cukup sebagai daya tarik "punya aplikasi sendiri". *Validasi via: user research / fake-door pada galeri showcase.*
- **A3** — Pengguna non-teknis mampu menyelesaikan flow create-tenant tanpa bantuan. *Validasi via: usability test pada MVP.*

---

## 4. Users (Personas)

| Persona | Konteks | Pemicu kebutuhan | Yang mereka lakukan di Kasiro |
|---|---|---|---|
| **Owner (Pemilik UMKM)** | Akun platform; non-teknis; punya 1+ usaha | Ingin POS bermerek sendiri, kelola usaha | Daftar, buat tenant (custom/template), atur theme/branding, undang & kelola karyawan, lihat semua laporan, kelola produk |
| **Manager/Admin** | Karyawan tepercaya per tenant | Owner butuh delegasi operasional | Kelola inventory & kategori, lihat laporan; **tidak** bisa ubah setting tenant atau billing |
| **Cashier/Kasir** | Pegawai garis depan | Melayani transaksi harian | Hanya akses interface POS; tidak lihat laporan sensitif, tidak ubah produk |
| **Visitor / calon pengguna** | Belum punya akun | Mengevaluasi platform | Lihat landing page, galeri showcase; klik contoh → diarahkan login/register lalu quick-create |

**Not for:** rantai retail besar/enterprise, bisnis yang butuh custom business logic, developer yang ingin white-label penuh dengan kode kustom.

> Catatan relasi penting: **role melekat pada pasangan (user, tenant)**, bukan pada user secara global. Satu User bisa menjadi Owner di tenant A, Cashier di tenant B.

---

## 5. Hypothesis

Kami percaya **kemampuan membuat aplikasi POS bermerek sendiri lewat subdomain, tanpa coding, dari satu codebase config-driven** akan **menyelesaikan masalah POS generik & mahal** untuk **pemilik UMKM kecil non-teknis**.

Kami tahu kami benar ketika **mayoritas user yang mendaftar berhasil membuat tenant aktif dan menyelesaikan transaksi POS pertamanya** (activation funnel sehat).

---

## 6. Success Metrics

Metrik utama yang dipilih: **Aktivasi Tenant** (activation funnel).

| Metric | Target (awal, perlu kalibrasi) | Cara diukur |
|---|---|---|
| **Activation rate** (primary) | ≥ 40% user terdaftar membuat ≥1 tenant aktif **dan** mencatat ≥1 transaksi | Event funnel: `registered → tenant_created → first_transaction` |
| Time-to-first-tenant | ≤ 10 menit median dari register | Timestamp `registered` → `tenant_created` |
| Create-flow completion | ≥ 70% yang memulai create-flow menyelesaikannya | Funnel step create-tenant |
| Data isolation incidents | **0** kebocoran data antar-tenant | Audit query + test otomatis cross-tenant |

> Metrik retensi & volume diakui penting namun **bukan** fokus pembuktian hipotesis MVP; dilacak sebagai metrik sekunder.

---

## 7. Scope

### MVP (Must Have) — minimum untuk menguji hipotesis aktivasi

**Domain utama (`kasiro.com`)**
- Landing page: hero, fitur, **galeri showcase** template/contoh, halaman Tentang & Bantuan.
- Auth terpusat: register, login, logout, reset password.
- Dashboard platform: **Beranda** (ringkasan tenant + entry point create), **Kasir Saya** (list tenant aktif), **Arsip** (tenant non-aktif).
- **Create-tenant flow** dua jalur:
  1. **Custom** — pilih layout + theme + color palette → isi detail (nama, subdomain, logo).
  2. **Pakai Template** — pilih preset → isi detail.
- **Quick-create dari showcase** — template fixed, lewati langkah kustomisasi, langsung isi detail.
- Arsip/restore tenant (soft state, bukan hard delete).

**Aplikasi tenant (`*.kasiro.com`)** — di-resolve dari DB via middleware
- **Interface POS** (transaksi: pilih produk, keranjang, total, bayar, simpan transaksi).
- **Manajemen Produk** (CRUD produk, stok).
- **Manajemen Kategori Produk** *(dipilih masuk MVP)*.
- **Laporan Transaksi** versi ringkas: penjualan harian, produk terlaris *(dipilih masuk MVP)*.
- **Manajemen Karyawan** *(dipilih masuk MVP)*: invite (token/link berexpiry), assign role, revoke.
- **Pengaturan Tenant** *(dipilih masuk MVP)*: edit nama, logo, theme, palette, layout, subdomain.
- **RBAC** 3 role (Owner/Manager/Cashier) ditegakkan via Policy/Gate.
- **Rendering config-driven** (theme/layout/palette dari DB) tanpa generate file per tenant.

**Cross-cutting (Must Have)**
- Resolusi tenant via subdomain (middleware) + reserved-subdomain guard + unik subdomain.
- Sesi auth **cross-subdomain** (cookie domain `.kasiro.com`).
- **Data isolation** otomatis via global scope `tenant_id` (defense-in-depth).

### Out of Scope (eksplisit ditunda + alasan)
- **Billing / subscription / payment gateway** — *ditunda; MVP gratis untuk validasi. Placeholder menu "Billing" boleh muncul bagi Owner namun non-fungsional.*
- **Custom domain milik tenant** (mis. `kasir.warungbudi.com`) — *kompleksitas TLS/DNS; subdomain dulu.*
- **Multi-cabang / multi-outlet per tenant** — *belum dibutuhkan UMKM target.*
- **Laporan lanjutan** (profit margin, pajak, ekspor akuntansi) — *laporan ringkas dulu.*
- **Marketplace template buatan komunitas** — *kurasi internal dulu.*
- **Aplikasi mobile native / offline mode** — *web dulu; offline POS adalah Future.*
- **Editor visual theme bebas** — *hanya pilihan dari whitelist preset.*

---

## 8. Functional Requirements

> Penomoran FR untuk dirujuk oleh `/plan`. Tiap FR menyatakan *apa* yang harus dilakukan sistem.

### 8.1 Domain Utama & Onboarding
- **FR-1** Visitor dapat melihat landing page, halaman Tentang, Bantuan, dan galeri showcase tanpa login.
- **FR-2** Visitor dapat register & login dengan satu akun platform (email + password).
- **FR-3** Saat visitor mengklik item showcase: jika belum login → diarahkan ke login/register lalu lanjut ke quick-create; jika sudah login → langsung ke quick-create dengan template **terkunci** sesuai item yang diklik.
- **FR-4** User login dapat membuat tenant via **Custom flow** (pilih layout, theme, palette, lalu detail).
- **FR-5** User login dapat membuat tenant via **Template flow** (pilih preset, lalu detail).
- **FR-6** Sistem memvalidasi **subdomain**: format valid (lowercase, alfanumerik + hyphen), unik global, bukan reserved (`www`, `app`, `api`, `admin`, `mail`, dll).
- **FR-7** Dashboard menampilkan **Beranda** (ringkasan tenant terbaru + entry create), **Kasir Saya** (tenant aktif milik/terkait user), dan **Arsip** (tenant diarsipkan).
- **FR-8** User (Owner) dapat **mengarsipkan** dan **memulihkan** tenant; arsip tidak menghapus data.

### 8.2 Aplikasi Tenant (POS)
- **FR-9** Request ke subdomain di-resolve ke tenant yang benar; jika tenant tidak ada/arsip → tampilkan halaman status yang sesuai (404 / "tenant tidak aktif").
- **FR-10** Kasir dapat melakukan transaksi: memilih produk, menyusun keranjang, menghitung total, mencatat pembayaran, menyimpan transaksi (mengurangi stok).
- **FR-11** Manager/Owner dapat CRUD produk dan kategori, mengatur stok.
- **FR-12** Manager/Owner dapat melihat laporan transaksi ringkas (penjualan harian, produk terlaris) terbatas pada tenant aktif.
- **FR-13** Owner dapat mengubah pengaturan tenant (nama, logo, theme, layout, palette, subdomain) dari dalam dashboard tenant.

### 8.3 Auth, Role & Manajemen Karyawan
- **FR-14** Satu sistem auth terpusat melayani domain utama dan semua subdomain; sesi dibagikan cross-subdomain.
- **FR-15** Setiap akses ke resource tenant dicek terhadap role user **pada tenant tersebut** (Owner/Manager/Cashier) via Policy/Gate.
- **FR-16** Owner dapat **mengundang** karyawan via email/link berisi token undangan ber-expiry, memilih role saat mengundang.
- **FR-17** Penerima undangan: jika sudah punya akun platform → menerima undangan menautkan akun ke tenant; jika belum → register dulu lalu otomatis tertaut.
- **FR-18** Owner dapat **mengubah role** dan **mencabut (revoke) akses** karyawan; pencabutan berlaku segera (sesi karyawan kehilangan akses tenant tsb).
- **FR-19** Token undangan: sekali pakai, kedaluwarsa setelah jangka waktu tertentu, dan dapat dibatalkan oleh Owner.

### 8.4 Data Isolation
- **FR-20** Semua tabel transaksional (`products`, `categories`, `transactions`, `transaction_items`, dll) memiliki `tenant_id` dan **otomatis** ter-scope ke tenant aktif pada konteks request.
- **FR-21** Operasi tulis otomatis mengisi `tenant_id` dari konteks tenant aktif (user tidak dapat menyetelnya manual).
- **FR-22** Akses lintas tenant ditolak by default (tidak ada query yang mengembalikan data tenant lain tanpa konteks eksplisit administratif).

---

## 9. Non-Functional Requirements

### Performa
- **NFR-1** Resolusi tenant (subdomain → tenant config) harus murah: cache lookup tenant by subdomain (mis. cache in-memory/Redis), invalidasi saat setting tenant berubah. Target overhead resolusi < ~10ms pada cache hit.
- **NFR-2** Halaman POS responsif untuk operasi kasir bolak-balik (target interaksi sub-detik pada data produk skala UMKM, ratusan–ribuan SKU).

### Skalabilitas
- **NFR-3** Arsitektur **single database, single codebase**. Target awal realistis: **ratusan hingga rendah-ribuan tenant** dalam satu database MySQL dengan indeks `tenant_id` yang benar di setiap tabel transaksional. Di atas itu, jalur evolusi: read-replica, partisi per `tenant_id`, lalu sharding/DB-per-tenant — **di luar scope MVP**, tapi skema tidak boleh menghalangi migrasi tsb.
- **NFR-4** Setiap query transaksional **wajib** memanfaatkan indeks komposit yang diawali `tenant_id`.

### Keamanan
- **NFR-5** **Data isolation** adalah requirement keamanan kelas-satu: global scope + auto-fill `tenant_id`, ditambah test otomatis yang memverifikasi tidak ada kebocoran lintas tenant. Target: **0 insiden**.
- **NFR-6** RBAC ditegakkan server-side (Policy/Gate), bukan hanya UI hiding.
- **NFR-7** Cookie sesi: `Domain=.kasiro.com`, `HttpOnly`, `Secure` (production), `SameSite=Lax`. CSRF aktif. Rate limiting pada endpoint auth & invite.
- **NFR-8** Token undangan: acak kriptografis, hashed at rest, sekali pakai, ber-expiry.
- **NFR-9** Tidak ada secret hardcoded; konfigurasi via `.env`.

### Availability & Operasional
- **NFR-10** Karena single codebase, satu deploy melayani semua tenant — keuntungan operasional, namun **blast radius** juga global; perubahan wajib lewat review + test sebelum deploy.
- **NFR-11** Backup database reguler; karena single DB, kehilangan DB = kehilangan semua tenant → backup & restore harus teruji.

---

## 10. Database Schema Outline

> Gaya migration Laravel/MySQL. Hanya tabel inti; detail kolom final ditetapkan di `/plan`.

**`users`** — akun platform (global)
- `id`, `name`, `email` (unique), `password`, `email_verified_at`, timestamps.

**`tenants`** — satu baris per aplikasi kasir
- `id`
- `owner_id` → `users.id` (pembuat/pemilik utama)
- `name` (brand)
- `subdomain` (unique, indexed)
- `logo_path` (nullable)
- `status` (`active` | `archived`)
- `template_id` → `templates.id` (nullable; asal preset)
- `theme_config` (JSON: `layout`, `theme`, `color_palette`, dll — lihat §11)
- timestamps, `archived_at` (nullable)

**`templates`** — preset siap pakai untuk showcase & Template flow
- `id`, `name`, `slug`, `description`, `preview_image`
- `default_config` (JSON: layout+theme+palette preset)
- `is_published` (untuk galeri showcase), timestamps.

**`tenant_user`** — pivot relasi user↔tenant + role
- `id`, `tenant_id` → `tenants.id`, `user_id` → `users.id`
- `role` (`owner` | `manager` | `cashier`)
- `status` (`active` | `revoked`), timestamps
- unique(`tenant_id`,`user_id`)

**`tenant_invitations`** — undangan karyawan
- `id`, `tenant_id`, `email`, `role`, `token` (hashed, unique)
- `invited_by` → `users.id`, `expires_at`, `accepted_at` (nullable), timestamps.

**`categories`** — kategori produk (tenant-scoped)
- `id`, `tenant_id` (indexed), `name`, timestamps.

**`products`** — produk (tenant-scoped)
- `id`, `tenant_id` (indexed), `category_id` (nullable), `name`, `sku` (nullable), `price`, `stock`, `is_active`, timestamps.
- index komposit (`tenant_id`, `category_id`).

**`transactions`** — header transaksi (tenant-scoped)
- `id`, `tenant_id` (indexed), `cashier_id` → `users.id`, `total`, `paid`, `change`, `payment_method`, `transacted_at`, timestamps.

**`transaction_items`** — baris transaksi
- `id`, `tenant_id` (indexed, denormalized untuk scope cepat), `transaction_id`, `product_id`, `qty`, `unit_price`, `subtotal`.

**Relasi ringkas**
```
users 1───* tenants (owner)
users *───* tenants  (via tenant_user, dgn role)
tenants 1───* categories / products / transactions
templates 1───* tenants
transactions 1───* transaction_items
```

> Konvensi: semua tabel tenant-scoped memakai trait `BelongsToTenant` (global scope + auto-fill), lihat §10/§11 strategi.

---

## 11. Subdomain & Routing Strategy

**Prinsip:** `*.kasiro.com` menunjuk ke aplikasi Laravel yang sama; tenant di-resolve **runtime** dari host header.

**Resolusi (production):**
1. Wildcard DNS: A/AAAA record `*.kasiro.com` → IP server.
2. Web server (Nginx/Apache) catch-all `server_name *.kasiro.com kasiro.com` → root Laravel sama.
3. Wildcard TLS (Let's Encrypt DNS-01 untuk `*.kasiro.com`).
4. **Middleware `ResolveTenant`**: ambil host → ekstrak subdomain → jika host = apex/`www` → mode platform; jika subdomain → lookup `tenants` by `subdomain` (cache). Jika tidak ada → 404; jika `archived` → halaman "tenant tidak aktif". Jika ada → set **TenantContext** (singleton) + binding container untuk request berjalan.
5. Route group: route domain utama (landing, auth, dashboard) terpisah dari route tenant (POS, inventory, dll) — Laravel `Route::domain()` / pemisahan via middleware.

**Local development (Laragon):**
- Laragon auto-vhost cocok untuk apex `kasiro.com`, namun **Windows hosts file tidak mendukung wildcard** (`*.kasiro.com`).
- Rekomendasi: jalankan **wildcard DNS lokal** (mis. Acrylic DNS Proxy / dnsmasq) yang me-resolve `*.kasiro.com → 127.0.0.1`, dan konfigurasi Apache/Nginx vhost dengan `ServerAlias *.kasiro.com`. Alternatif kasar: tambah entri hosts manual per subdomain saat testing (cukup untuk beberapa tenant dev).
- Pakai domain asli `kasiro.com` di lokal (bukan `.test`) sesuai rencana.

**Edge:** reserved subdomain guard (`www`, `app`, `api`, `admin`, `mail`, `static`, dll) diberlakukan saat pembuatan tenant **dan** saat resolusi.

---

## 12. Authentication & Authorization Strategy

**Auth terpusat & cross-subdomain**
- Satu guard session untuk seluruh platform. Saat user login di `kasiro.com`, cookie sesi diset dengan `SESSION_DOMAIN=.kasiro.com` sehingga terbaca di semua subdomain tenant.
- Rekomendasi MVP: **Laravel session-based auth** (Breeze/Fortify) — server-rendered (Blade). **Sanctum tidak wajib** untuk MVP; pertimbangkan hanya jika nanti ada SPA/mobile/API publik. *(Open Question OQ-1.)*
- `SESSION_DRIVER` berbasis database/Redis (bukan file) agar revoke & shared-session andal.

**Authorization (RBAC per-tenant)**
- Role bersifat **per-tenant** (di `tenant_user.role`), bukan global. Setelah `ResolveTenant`, sistem menentukan role user untuk tenant aktif, lalu Policy/Gate menegakkan izin.
- Rekomendasi MVP: **native Gate/Policy** dengan resolver kustom membaca role dari `tenant_user` pada TenantContext. Alasan: hanya 3 role tetap & sederhana; menghindari overhead konfigurasi.
- Alternatif jika kebutuhan role/permission berkembang: **Spatie laravel-permission dengan fitur Teams** (team = tenant). Catat sebagai jalur evolusi, bukan MVP. *(Open Question OQ-2.)*

**Matriks izin (ringkas)**

| Aksi | Owner | Manager | Cashier |
|---|---|---|---|
| Transaksi POS | ✓ | ✓ | ✓ |
| CRUD produk & kategori | ✓ | ✓ | ✗ |
| Lihat laporan | ✓ | ✓ | ✗ (atau terbatas) |
| Kelola karyawan (invite/role/revoke) | ✓ | ✗ | ✗ |
| Ubah setting tenant (theme/subdomain/logo) | ✓ | ✗ | ✗ |
| Billing (future) | ✓ | ✗ | ✗ |

**Invite flow (aman & user-friendly)** — *jawaban atas pertanyaan brief:*
- Owner kirim undangan (email + role). Sistem buat token acak (disimpan **hashed**), kirim link berisi token mentah, `expires_at`.
- Klik link: jika belum login & belum punya akun → register; jika sudah punya akun → login. Lalu tampilkan layar konfirmasi "Bergabung ke {tenant} sebagai {role}".
- Terima → buat baris `tenant_user` (`status=active`). Token ditandai `accepted_at`, tidak bisa dipakai ulang.
- Owner bisa membatalkan undangan pending & mencabut akses anggota kapan saja.

---

## 13. Theme / Layout / Color Palette System

**Keputusan penyimpanan: hybrid.**
- **Kolom terdedikasi** untuk field yang di-query/di-index/di-validasi: `subdomain`, `name`, `status`, `logo_path`, `template_id`.
- **JSON `theme_config`** untuk presentasi murni: `layout`, `theme`, `color_palette` (dan token warna terkaitnya). Alasan: presentasi sering berubah & tidak perlu di-query relasional → JSON fleksibel; identitas/relasi tetap di kolom agar cepat & berintegritas.

**Rendering tanpa generate file per tenant**
- **Layout** = pemilihan **Blade layout** dari **whitelist** (mis. `layouts/pos/modern`, `classic`, `retro`). Nilai `theme_config.layout` memetakan ke nama layout yang sudah ada di codebase — bukan file baru.
- **Theme/Palette** = di-inject sebagai **CSS custom properties (variables)** di `<head>` layout berdasarkan `theme_config.color_palette`. Satu set komponen CSS membaca variabel ini; ganti palette = ganti nilai variabel, bukan file CSS baru.
- **Brand & logo** = data biasa (`name`, `logo_path`) yang dirender ke template.
- **Validasi ketat:** nilai `layout`/`theme`/`palette` harus termasuk dalam daftar yang didukung (whitelist) saat disimpan, agar tidak ada referensi ke aset/komponen yang tidak ada.

> Implikasi: menambah theme/layout baru = pekerjaan platform (tambah preset + komponen), bukan pekerjaan tenant. Ini menjaga "satu codebase" tetap utuh.

---

## 14. Template System untuk Quick-Create

**Tujuan:** preset yang dipilih dari **galeri showcase landing page** (atau Template flow di dashboard) menghasilkan tenant tanpa langkah kustomisasi, **namun memakai struktur data yang sama** dengan Custom flow.

**Mekanisme:**
- Tabel `templates` menyimpan `default_config` (JSON: layout+theme+palette) + metadata showcase (`preview_image`, `is_published`).
- **Custom flow:** user menyusun `theme_config` sendiri dari pilihan layout/theme/palette → disimpan ke `tenants.theme_config`; `template_id` = null.
- **Template / Quick-create flow:** `tenants.theme_config` diisi dengan **snapshot** dari `template.default_config` saat pembuatan, dan `tenants.template_id` mereferensikan asalnya.
  - **Snapshot (denormalisasi), bukan referensi hidup:** jika template diperbarui kelak, tenant yang sudah dibuat **tidak ikut berubah** (mencegah perubahan tak terduga pada toko yang sudah berjalan). `template_id` hanya jejak asal/analytics.
- **Perbedaan flow hanyalah pada UI/langkah input**, bukan pada bentuk data akhir: keduanya bermuara pada satu `tenants` row dengan `theme_config` terisi. Quick-create dari showcase = template terkunci + langkah kustomisasi dilewati; user hanya isi nama, subdomain, logo.

---

## 15. Edge Cases & Risk Considerations

| # | Kasus | Penanganan yang diharapkan |
|---|---|---|
| E1 | Subdomain sudah dipakai | Validasi unik + cek reserved saat input; pesan jelas; sarankan alternatif. |
| E2 | Subdomain reserved (`www`, `api`, dll) | Tolak via guard list. |
| E3 | Tenant diarsipkan tapi karyawan masih punya sesi aktif | `ResolveTenant` cek `status`; tenant `archived` → blokir akses POS, tampilkan halaman "tidak aktif", meski sesi auth valid. |
| E4 | User di-revoke dari tenant saat sedang aktif memakai POS | Cek `tenant_user.status` per-request; revoke berlaku segera → request berikutnya ditolak. |
| E5 | Owner menghapus dirinya / tenant tanpa owner | Larang menghapus owner terakhir; tenant harus selalu punya ≥1 Owner aktif. |
| E6 | Token undangan kedaluwarsa / dipakai ulang | Token sekali pakai + expiry; tampilkan "undangan tidak valid/kedaluwarsa". |
| E7 | Email undangan sudah jadi member tenant | Cegah duplikat (unique tenant_id+user_id); tampilkan status. |
| E8 | Kebocoran data lintas tenant (query lupa scope) | Global scope default + auto-fill tenant_id + test otomatis cross-tenant. Treat sebagai CRITICAL. |
| E9 | Akses subdomain tenant yang tidak ada | 404 yang ramah, bukan error mentah. |
| E10 | Cookie/sesi tidak terbawa lintas subdomain (config salah) | `SESSION_DOMAIN=.kasiro.com` wajib; uji eksplisit login di apex → akses di subdomain. |
| E11 | Stok minus / race condition saat 2 kasir transaksi bersamaan | Kurangi stok atomik (locking/transaction DB); validasi stok saat checkout. |
| E12 | Single DB sebagai single point of failure | Backup teruji + jalur evolusi skalabilitas (NFR-3/11). |
| E13 | Blast radius deploy global | Review + test wajib sebelum deploy (NFR-10). |
| E14 | Penghapusan akun user yang jadi Owner banyak tenant | Tentukan kebijakan: transfer ownership atau arsip tenant terdampak (lihat OQ). |

---

## 16. Delivery Milestones

> Outcome bisnis, bukan task engineering. `/plan` akan mengubah tiap milestone jadi rencana implementasi.
> Status: pending | in-progress | complete

| # | Milestone | Outcome (user-visible) | Status | Plan |
|---|---|---|---|---|
| 1 | **Fondasi multi-tenant & routing** | Subdomain me-resolve ke tenant yang benar dari DB; data ter-isolasi otomatis (`tenant_id` global scope) | complete | `.claude/plans/kasiro.plan.md` |
| 2 | **Auth terpusat & RBAC** | User register/login sekali; sesi jalan lintas subdomain; role per-tenant ditegakkan | in-progress | `.claude/plans/kasiro-m2-auth-rbac.plan.md` |
| 3 | **Create-tenant flow (Custom + Template + Quick-create)** | User bisa membuat tenant aktif lewat 3 jalur; subdomain & branding tersimpan | pending | — |
| 4 | **Dashboard platform** | Beranda, Kasir Saya, Arsip (+restore) berfungsi | pending | — |
| 5 | **Aplikasi POS tenant** | Kasir bisa transaksi; produk & kategori dikelola; stok berkurang | pending | — |
| 6 | **Manajemen Karyawan & Invite** | Owner undang/assign role/revoke karyawan via token aman | pending | — |
| 7 | **Pengaturan Tenant & Theme system** | Owner ubah nama/logo/theme/layout/palette; render config-driven via CSS variables | pending | — |
| 8 | **Laporan ringkas + Activation analytics** | Manager/Owner lihat penjualan harian & produk terlaris; funnel aktivasi terukur | pending | — |

---

## 17. Open Questions / Decisions Needed

- [ ] **OQ-1 (Auth stack):** Cukup session-based (Breeze/Fortify) untuk MVP, atau sudah perlu Sanctum sejak awal (antisipasi mobile/API)? *Rekomendasi: session-based dulu.*
- [ ] **OQ-2 (RBAC):** Native Gate/Policy kustom (rekomendasi MVP) vs Spatie laravel-permission + Teams (siap berkembang)? Konfirmasi pilihan.
- [ ] **OQ-3 (Pembuatan tenant otomatis vs moderasi):** Apakah tenant langsung live setelah dibuat, atau perlu antrian moderasi (anti-abuse/penyalahgunaan subdomain/brand)?
- [ ] **OQ-4 (Verifikasi email):** Wajib verifikasi email sebelum membuat tenant? (mempengaruhi activation funnel & anti-spam.)
- [ ] **OQ-5 (Galeri showcase):** Item showcase = `templates` yang `is_published`, atau juga menampilkan tenant nyata milik user lain (butuh consent/privasi)? Brief menyebut "contoh hasil user lain" — perlu kebijakan privasi.
- [ ] **OQ-6 (Reserved & kebijakan subdomain):** Daftar reserved final? Boleh ganti subdomain setelah live (redirect lama→baru?) atau kunci permanen?
- [ ] **OQ-7 (Ownership transfer):** Saat Owner ingin keluar/akun dihapus, mekanismenya transfer ownership atau arsip tenant? (terkait E5/E14.)
- [ ] **OQ-8 (Skala target konkret):** Berapa jumlah tenant & transaksi/hari yang ditargetkan di 6–12 bulan pertama? (mengkalibrasi NFR & indeks.)
- [ ] **OQ-9 (Penyimpanan logo/aset):** Local disk vs object storage (S3-compatible)? (mempengaruhi deploy & backup.)
- [ ] **OQ-10 (Quota per akun):** Batas jumlah tenant per user di MVP gratis (mencegah abuse)?
- [ ] **OQ-11 (Metrik target):** Angka target di §6 masih placeholder — perlu dikalibrasi setelah validasi awal.
- [ ] **OQ-12 (Manager & laporan sensitif):** Apakah Manager boleh lihat semua laporan, atau ada laporan tertentu (mis. omzet total) yang Owner-only?

---

## 18. Catatan Penutup
- Fokus PRD ini pada logika sistem, struktur data, dan flow fungsional — bukan desain visual (sesuai permintaan).
- Monetisasi sengaja ditunda; arsitektur tetap menyisakan ruang (role Owner, placeholder billing) agar penambahan billing tidak butuh refactor besar.
- Risiko terbesar yang harus dijaga ketat sepanjang development: **data isolation lintas tenant** (E8) dan **sesi cross-subdomain** (E10) — keduanya CRITICAL.

---
*Status: DRAFT — requirements only. Implementation planning pending via /plan.*
