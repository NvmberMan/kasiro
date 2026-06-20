# Plan: Kasiro — Fondasi Multi-Tenant & Routing

**Source PRD**: `.claude/prds/kasiro.prd.md`
**Selected Milestone**: #1 — Fondasi multi-tenant & routing
**Complexity**: Large (greenfield: scaffold Laravel + bangun fondasi tenancy)

## Summary
Repo masih kosong (belum ada Laravel — hanya `.claude`, `.git`, `.mcp.json`, `README.md`). Milestone ini men-scaffold proyek Laravel 12.x di atas MySQL, lalu membangun **fondasi single-database multi-tenancy**: resolusi tenant dari subdomain via middleware, `TenantContext` per-request, dan **isolasi data otomatis** (`tenant_id` global scope + auto-fill) sebagai requirement keamanan kelas-satu (PRD §9 NFR-5, §15 E8). Output milestone: request ke `<sub>.kasiro.com` me-resolve tenant yang benar dari DB dan data antar-tenant tidak bocor — terbukti oleh test otomatis.

> Catatan desain (image referensi Wix template gallery) menyangkut **galeri showcase / landing page** → relevan untuk Milestone 3–4, **bukan** Milestone 1. Dicatat agar tidak hilang, tapi di luar scope plan ini.

## Patterns to Mirror
| Category | Source | Pattern |
|---|---|---|
| Naming | — | **Tidak ada kode existing.** Tetapkan konvensi baseline: Model `PascalCase` singular (`Tenant`), migration `snake_case` plural (`tenants`), middleware `PascalCase` + suffix (`ResolveTenant`), trait `BelongsToTenant`, alias middleware `kebab` (`tenant`). |
| Errors | — | Belum ada. Tetapkan: `abort(404)` untuk tenant tidak ditemukan; halaman view khusus untuk tenant `archived`; exception domain `TenantResolutionException` bila perlu konteks. |
| Logging | — | Belum ada. Pakai default Laravel `Log` channel; log `warning` saat lookup subdomain gagal/cache miss anomali. |
| Data access | — | Belum ada. Tetapkan: semua model tenant-scoped pakai trait `BelongsToTenant` (global scope dari `TenantContext`); tidak ada query mentah lintas tenant tanpa scope eksplisit. |
| Tests | — | Belum ada. Tetapkan: Pest/PHPUnit feature test di `tests/Feature`, factory per model, test isolasi cross-tenant wajib. |

> Karena greenfield, plan ini **menetapkan** pola, bukan meniru. Semua milestone berikutnya mirror konvensi yang dibuat di sini.

## Files to Change
| File | Action | Why |
|---|---|---|
| (seluruh skeleton Laravel) | CREATE | `laravel new` / `composer create-project` — scaffold awal |
| `.env` / `.env.example` | UPDATE | Koneksi MySQL, `APP_URL=http://kasiro.com`, `SESSION_DOMAIN=.kasiro.com`, `SESSION_DRIVER=database` |
| `config/tenancy.php` | CREATE | Konfigurasi domain induk (`kasiro.com`), daftar **reserved subdomains**, TTL cache tenant |
| `database/migrations/xxxx_create_tenants_table.php` | CREATE | Tabel `tenants` (subset Milestone 1: id, owner_id nullable utk sekarang, name, subdomain unique, status, theme_config json, timestamps, archived_at) |
| `app/Models/Tenant.php` | CREATE | Eloquent model `Tenant` (+ cast `theme_config` → array, scope by subdomain) |
| `app/Support/TenantContext.php` | CREATE | Singleton per-request menyimpan tenant aktif (set/get/has/forget) |
| `app/Http/Middleware/ResolveTenant.php` | CREATE | Ekstrak subdomain dari host → guard reserved → lookup (cache) → set `TenantContext`; 404/archived handling |
| `app/Http/Middleware/EnsureTenantContext.php` | CREATE | Guard untuk route tenant: tolak bila tidak ada tenant aktif |
| `app/Models/Concerns/BelongsToTenant.php` | CREATE | Trait: global scope `where tenant_id` + auto-fill `tenant_id` saat `creating` |
| `app/Rules/ValidSubdomain.php` | CREATE | Validasi format + reserved + keunikan subdomain |
| `bootstrap/app.php` | UPDATE | Daftarkan alias middleware `tenant`, `tenant.context`; binding `TenantContext` |
| `routes/web.php` | UPDATE | Pisahkan **platform routes** (apex `kasiro.com`) dari **tenant routes** (`Route::domain('{subdomain}.kasiro.com')`) |
| `app/Providers/AppServiceProvider.php` | UPDATE | Bind `TenantContext` sebagai singleton di container |
| `resources/views/tenant/archived.blade.php` | CREATE | Halaman "tenant tidak aktif" (E3) |
| `database/factories/TenantFactory.php` | CREATE | Factory untuk test |
| `tests/Feature/TenantResolutionTest.php` | CREATE | Resolusi subdomain, 404, archived |
| `tests/Feature/TenantIsolationTest.php` | CREATE | Cross-tenant data leakage = 0 (CRITICAL) |
| `docs/local-dev.md` | CREATE | Setup Laragon vhost + wildcard DNS lokal (Acrylic/dnsmasq) |

## Tasks

### Task 1: Scaffold Laravel 12 + koneksi MySQL
- **Action**: Buat proyek Laravel 12.x di direktori (gunakan installer ke folder kosong/temp lalu pindahkan, karena dir tidak kosong — ada `.claude/.git/.mcp.json`). Set `.env`: DB MySQL (gunakan MCP mysql untuk buat database `kasiro`), `APP_URL=http://kasiro.com`, `SESSION_DRIVER=database`, `CACHE_STORE=database` (atau redis bila ada).
- **Mirror**: Konvensi default Laravel 12 (struktur slim, `bootstrap/app.php`).
- **Validate**: `php artisan --version` (12.x), `php artisan migrate` sukses (tabel default + sessions).

### Task 2: Migration + Model `Tenant`
- **Action**: Migration `tenants` (kolom subset Milestone 1 sesuai PRD §10; `subdomain` unique+index, `status` enum `active|archived`, `theme_config` JSON nullable, `archived_at` nullable). Model `Tenant` dengan cast `theme_config => array`, helper `findBySubdomain()`.
- **Mirror**: Konvensi naming yang ditetapkan di tabel Patterns.
- **Validate**: `php artisan migrate:fresh`; `Tenant::factory()->create()` di tinker/test.

### Task 3: `TenantContext` (singleton per-request)
- **Action**: Kelas `App\Support\TenantContext` dengan `set(Tenant)`, `get(): ?Tenant`, `id(): ?int`, `has(): bool`, `forget()`. Bind singleton di `AppServiceProvider`.
- **Mirror**: Service di-resolve via container, bukan facade global.
- **Validate**: Unit test set/get/forget.

### Task 4: Middleware `ResolveTenant`
- **Action**: Parse `request->getHost()` → ekstrak label subdomain relatif terhadap `config('tenancy.central_domain')`. Jika host = apex/`www` → lewati (mode platform). Jika subdomain reserved → 404. Lookup tenant by subdomain via **cache** (`Cache::remember`, TTL dari config); invalidasi disiapkan untuk milestone setting. Tidak ada → `abort(404)`. `status=archived` → render `tenant/archived` (E3). Ada+aktif → `TenantContext::set()`.
- **Mirror**: Registrasi alias di `bootstrap/app.php` via `->withMiddleware(fn ($m) => $m->alias([...]))` (Laravel 12.x — dikonfirmasi via context7).
- **Validate**: `tests/Feature/TenantResolutionTest.php`.

### Task 5: Pemisahan route platform vs tenant
- **Action**: Di `routes/web.php`, grup tenant pakai `Route::domain('{subdomain}.'.config('tenancy.central_domain'))->middleware(['tenant','tenant.context'])`. Platform routes (landing/auth/dashboard placeholder) di apex tanpa middleware tenant. Tambah satu route smoke per sisi (mis. tenant `/` mengembalikan nama tenant aktif).
- **Mirror**: `Route::domain()` grouping (Laravel 12.x — dikonfirmasi via context7).
- **Validate**: `curl -H "Host: warungbudi.kasiro.com" http://127.0.0.1:8000/` mengembalikan data tenant; `Host: kasiro.com` mengembalikan platform.

### Task 6: Trait `BelongsToTenant` (isolasi otomatis — CRITICAL)
- **Action**: Trait dengan `bootBelongsToTenant()`: `static::addGlobalScope` membatasi `where('tenant_id', TenantContext::id())` bila konteks ada; `static::creating` auto-isi `tenant_id` dari konteks (user tidak bisa override). Sediakan jalur eksplisit bypass untuk konteks administratif (mis. `withoutTenantScope()`), didokumentasikan & dibatasi.
- **Mirror**: Global scope via `booted()`/trait boot + `addGlobalScope` (Laravel 12.x — dikonfirmasi via context7).
- **Validate**: `tests/Feature/TenantIsolationTest.php` — buat 2 tenant + data; pastikan query tenant A tidak pernah melihat data tenant B; pastikan create otomatis isi `tenant_id` benar.

### Task 7: Reserved subdomain guard + `ValidSubdomain` rule
- **Action**: Daftar reserved di `config/tenancy.php` (`www, app, api, admin, mail, static, assets, cdn`). Rule `ValidSubdomain`: lowercase alfanumerik+hyphen, panjang wajar, bukan reserved, unik di tabel `tenants`. Dipakai saat resolusi (guard) & nanti saat create-tenant (Milestone 3).
- **Mirror**: Custom validation Rule object.
- **Validate**: Unit test rule (valid/invalid/reserved/duplikat).

### Task 8: Setup local dev (Laragon + wildcard DNS)
- **Action**: Tulis `docs/local-dev.md`: konfigurasi Laragon Apache/Nginx vhost `kasiro.com` + `ServerAlias *.kasiro.com`; karena Windows hosts file tak dukung wildcard, instruksikan **Acrylic DNS Proxy** (atau dnsmasq) me-resolve `*.kasiro.com → 127.0.0.1`. Sertakan fallback: tambah entri hosts manual per subdomain dev. Konfirmasi `SESSION_DOMAIN=.kasiro.com` (uji cross-subdomain disiapkan untuk Milestone 2).
- **Mirror**: —
- **Validate**: Manual — akses `kasiro.com` & `warungbudi.kasiro.com` di browser lokal resolve ke app yang sama.

### Task 9: Test suite fondasi
- **Action**: Lengkapi `TenantResolutionTest` (apex, subdomain valid, reserved → 404, tidak ada → 404, archived → halaman archived) dan `TenantIsolationTest` (no leakage, auto-fill). Set base test agar bisa simulasikan Host header.
- **Mirror**: Feature test + factory (konvensi yang ditetapkan).
- **Validate**: `php artisan test` hijau; isolasi 0 kebocoran.

## Validation
```bash
php artisan --version          # konfirmasi Laravel 12.x
php artisan migrate:fresh      # skema bersih termasuk tenants
php artisan test               # seluruh feature test hijau (resolusi + isolasi)
# Smoke routing (server: php artisan serve):
curl -s -H "Host: kasiro.com" http://127.0.0.1:8000/            # mode platform
curl -s -H "Host: warungbudi.kasiro.com" http://127.0.0.1:8000/ # resolve tenant
curl -s -H "Host: admin.kasiro.com" http://127.0.0.1:8000/      # reserved → 404
```

## Risks
| Risk | Likelihood | Mitigation |
|---|---|---|
| **Kebocoran data lintas tenant** (lupa scope / query mentah) | Medium | Trait global scope default + auto-fill + `TenantIsolationTest` wajib hijau sebelum lanjut (CRITICAL gate) |
| Wildcard DNS lokal ribet di Windows/Laragon | High | Dokumentasi Acrylic/dnsmasq + fallback hosts manual; tidak memblok backend (test pakai Host header simulasi) |
| Direktori tidak kosong saat `laravel new` | Medium | Scaffold ke folder temp lalu merge, jaga `.claude/.git/.mcp.json` |
| Global scope mengganggu seeding/job tanpa konteks tenant | Medium | Sediakan `withoutTenantScope()` & set konteks eksplisit di job/seeder |
| Pilihan hand-rolled vs package `stancl/tenancy` | Low | PRD memutuskan single-DB hand-rolled (global scope). `stancl/tenancy` dicatat sebagai alternatif bila kebutuhan berkembang — **bukan** scope sekarang |
| Cache tenant basi setelah update subdomain | Low | Invalidasi cache by subdomain disiapkan; eksekusi penuh di Milestone 7 (Pengaturan Tenant) |

## Acceptance
- [ ] Laravel 12.x ter-scaffold, `php artisan migrate` & `php artisan test` hijau
- [ ] `<sub>.kasiro.com` me-resolve tenant yang benar; apex = mode platform
- [ ] Subdomain reserved & tenant tidak ada → 404; tenant `archived` → halaman khusus
- [ ] **Isolasi data: 0 kebocoran lintas tenant** (test membuktikan); `tenant_id` auto-fill saat create
- [ ] `docs/local-dev.md` memuat setup wildcard DNS lokal
- [ ] Pola (naming/errors/data access/tests) ditetapkan untuk di-mirror milestone berikutnya
- [ ] Validation commands lulus
```

---
*Status: DRAFT plan — menunggu konfirmasi sebelum menulis kode.*
