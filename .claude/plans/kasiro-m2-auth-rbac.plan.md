# Plan: Kasiro — Milestone 2: Auth Terpusat & RBAC

**Source PRD**: `.claude/prds/kasiro.prd.md`
**Selected Milestone**: #2 — Auth terpusat & RBAC
**Depends on**: Milestone 1 (`kasiro.plan.md`, COMPLETE) — TenantContext, ResolveTenant, BelongsToTenant, routing apex/subdomain sudah ada.
**Complexity**: Medium (scaffold Breeze + bangun lapisan keanggotaan & otorisasi per-tenant di atas fondasi yang ada)

## Keputusan (dikonfirmasi)
- **Auth stack**: Laravel **Breeze (Blade), session-based** (OQ-1). Sanctum ditunda.
- **RBAC**: **native Gate/Policy kustom** membaca role dari `tenant_user` pada `TenantContext` aktif (OQ-2). Tanpa Spatie.

## Summary
Milestone ini membangun **identitas & izin** di atas fondasi tenancy Milestone 1. Tiga outcome user-visible (PRD §16): (1) user **register/login sekali** dengan satu akun platform; (2) **sesi jalan lintas subdomain** (`SESSION_DOMAIN=.kasiro.com`) — login di `kasiro.com` terbawa ke `<sub>.kasiro.com` (FR-14, E10); (3) **role per-tenant ditegakkan server-side** (FR-15, NFR-6) — role melekat pada pasangan `(user, tenant)` lewat pivot `tenant_user`, bukan pada user global. Termasuk **revoke berlaku segera** (E4) dan penolakan non-member. Invite flow (FR-16–19) **bukan** scope milestone ini — itu Milestone 6; di sini hanya struktur `tenant_user` + resolusi role + penegakan izin yang disiapkan.

> Catatan scope: Breeze hanya menyediakan auth dasar (register/login/logout/reset/verify). Halaman dashboard nyata = Milestone 4. Di sini cukup placeholder terproteksi untuk membuktikan auth + RBAC bekerja.

## Patterns to Mirror
| Category | Source | Pattern |
|---|---|---|
| Middleware alias | `bootstrap/app.php` | Daftar alias via `$middleware->alias([...])`; tambah `tenant.member` di samping `tenant`, `tenant.context`. |
| Resolusi konteks | `app/Support/TenantContext.php` | Role aktif diturunkan dari `TenantContext::get()` + user login; jangan simpan role di session. |
| Guard route | `app/Http/Middleware/EnsureTenantContext.php` | Gaya guard tipis: cek kondisi → `abort()`; mirror untuk `EnsureTenantMember`. |
| Naming | Milestone 1 | Middleware `PascalCase`; enum `PascalCase` (`TenantRole`); pivot model `TenantUser`; test `*Test` di `tests/Feature`. |
| Data access | `BelongsToTenant` | **`tenant_user` TIDAK memakai `BelongsToTenant`** — query "tenant milik user" harus lintas-tenant by design; scope-nya manual & eksplisit. |
| Tests | `TenantIsolationTest` | Feature test + factory; simulasikan Host header untuk uji cross-subdomain. |

## Files to Change
| File | Action | Why |
|---|---|---|
| `composer.json` / vendor | UPDATE | `composer require laravel/breeze --dev` lalu `php artisan breeze:install blade` |
| `routes/auth.php` | CREATE (Breeze) | Route auth (login/register/reset/verify) — **dipindah ke grup apex** |
| `routes/web.php` | UPDATE | Bungkus auth + platform routes di `Route::domain($central)`; tenant routes tambah middleware `['auth','tenant.member']`; route smoke role aktif |
| `app/Http/Controllers/Auth/*` | CREATE (Breeze) | Controller auth standar Breeze (dipertahankan) |
| `resources/views/auth/*`, `layouts/*`, `dashboard.blade.php` | CREATE (Breeze) | View auth + layout; placeholder dashboard apex |
| `.env` / `.env.example` | UPDATE | Pastikan `SESSION_DRIVER=database`, `SESSION_DOMAIN=.kasiro.com`, `SESSION_SAME_SITE=lax` (NFR-7) |
| `database/migrations/xxxx_create_tenant_user_table.php` | CREATE | Pivot `(tenant_id, user_id, role, status)` unik `(tenant_id,user_id)`, FK cascade |
| `database/migrations/xxxx_add_owner_fk_to_tenants.php` | CREATE | Tambah FK `owner_id → users.id` (kolom sudah ada nullable dari M1) |
| `app/Enums/TenantRole.php` | CREATE | Enum `owner\|manager\|cashier` + helper kapabilitas |
| `app/Enums/MembershipStatus.php` | CREATE | Enum `active\|revoked` |
| `app/Models/TenantUser.php` | CREATE | Pivot model (`extends Pivot`), cast role/status ke enum |
| `app/Models/User.php` | UPDATE | Relasi `tenants()` belongsToMany withPivot(role,status); `ownedTenants()`; helper `roleFor(Tenant): ?TenantRole`, `isMemberOf(Tenant): bool`, `can…` |
| `app/Models/Tenant.php` | UPDATE | Relasi `users()` belongsToMany; `owner()` belongsTo |
| `app/Http/Middleware/EnsureTenantMember.php` | CREATE | Setelah `auth`+`ResolveTenant`: tolak bila user bukan member **aktif** tenant aktif (E4 revoke segera; non-member) |
| `app/Providers/AppServiceProvider.php` | UPDATE | `boot()` definisikan Gate matriks izin (§12) berbasis `TenantContext` + `roleFor()` |
| `app/Policies/TenantPolicy.php` | CREATE | Aksi owner-only (ubah setting, kelola karyawan) — disiapkan untuk M6/M7 |
| `bootstrap/app.php` | UPDATE | Daftar alias `tenant.member`; set `redirectGuestsTo` ke login apex |
| `database/factories/TenantUserFactory.php` | CREATE | Factory keanggotaan untuk test |
| `tests/Feature/Auth/*` | CREATE (Breeze) | Suite auth bawaan Breeze (disesuaikan Host apex) |
| `tests/Feature/CrossSubdomainSessionTest.php` | CREATE | E10/FR-14: cookie `.kasiro.com`; login apex → akses subdomain terautentikasi |
| `tests/Feature/RbacEnforcementTest.php` | CREATE | FR-15: matriks Owner/Manager/Cashier ditegakkan server-side |
| `tests/Feature/TenantMembershipTest.php` | CREATE | E4: non-member ditolak; revoke berlaku request berikutnya |
| `docs/local-dev.md` | UPDATE | Catatan uji login cross-subdomain lokal |

## Tasks

### Task 1: Install Breeze (Blade) + merge routing
- **Action**: `composer require laravel/breeze --dev` → `php artisan breeze:install blade --no-interaction`. Breeze menimpa `routes/web.php` & menambah `routes/auth.php`, view, layout, `tests/Feature/Auth/*`. **Re-merge** agar fondasi M1 utuh: pindahkan auth + dashboard ke dalam `Route::domain(config('tenancy.central_domain'))` (mode platform), pertahankan grup tenant `Route::domain('{subdomain}.'.$central)` dari M1.
- **Mirror**: Pemisahan apex vs tenant (M1 `routes/web.php`).
- **Validate**: `php artisan migrate:fresh`; `php artisan test` (suite Breeze hijau); `curl -H "Host: kasiro.com" .../login` → 200, `curl -H "Host: warungbudi.kasiro.com" .../login` → tidak mengekspos auth platform (login hanya di apex).

### Task 2: Sesi cross-subdomain
- **Action**: Pastikan `.env`/`.env.example`: `SESSION_DRIVER=database`, `SESSION_DOMAIN=.kasiro.com`, `SESSION_SAME_SITE=lax`, `SESSION_SECURE_COOKIE` via env (true di prod). Tabel `sessions` sudah ada (migrasi default M1). Verifikasi cookie di-set untuk domain induk.
- **Mirror**: PRD §12, NFR-7.
- **Validate**: `CrossSubdomainSessionTest` — login di apex set cookie domain `.kasiro.com`; request ke `<sub>.kasiro.com` membawa user terautentikasi.

### Task 3: Skema keanggotaan `tenant_user` + FK owner
- **Action**: Migration `tenant_user`: `id`, `tenant_id` (FK cascade, indexed), `user_id` (FK cascade, indexed), `role` (string, default `cashier`), `status` (string, default `active`), timestamps, `unique(tenant_id,user_id)`. Migration kedua: tambah FK `tenants.owner_id → users.id` (`nullOnDelete`). Enum `TenantRole`, `MembershipStatus`. Pivot model `TenantUser`.
- **Mirror**: Gaya migration M1 (`create_tenants_table`).
- **Validate**: `migrate:fresh` sukses; `TenantUserFactory` membuat baris valid; constraint unik teruji.

### Task 4: Relasi & resolusi role di model
- **Action**: `User::tenants()` belongsToMany `Tenant` `withPivot('role','status')->withTimestamps()` (pivot `TenantUser`); `User::ownedTenants()` hasMany; `User::roleFor(Tenant): ?TenantRole` (hanya membership `active`); `User::isMemberOf(Tenant): bool`. `Tenant::users()` belongsToMany; `Tenant::owner()` belongsTo. **Penting**: query pivot tidak boleh terkena `BelongsToTenant` global scope.
- **Mirror**: Konvensi relasi Eloquent standar.
- **Validate**: Unit/feature: `roleFor` mengembalikan role benar; mengembalikan `null` saat `revoked`/non-member.

### Task 5: Middleware `EnsureTenantMember` (E4 — revoke segera)
- **Action**: Alias `tenant.member`. Urutan pada route tenant: `['tenant','auth','tenant.member','tenant.context']`. Logika: ambil `TenantContext::get()` + `auth()->user()`; jika `roleFor(tenant)` null (bukan member aktif) → `abort(403)` (atau redirect ke apex sesuai UX). Karena dicek **per-request**, revoke berlaku segera.
- **Mirror**: `EnsureTenantContext` (guard tipis).
- **Validate**: `TenantMembershipTest` — non-member 403; set `status=revoked` lalu request berikutnya 403; member aktif lolos.

### Task 6: Gate/Policy matriks izin (FR-15, NFR-6)
- **Action**: Di `AppServiceProvider::boot()` definisikan Gate berbasis tenant aktif: `access-pos` (semua role), `manage-products` & `manage-categories` (owner|manager), `view-reports` (owner|manager), `manage-staff` (owner), `manage-tenant-settings` (owner), `manage-billing` (owner). Resolver baca `TenantContext` + `user->roleFor()`. `TenantPolicy` untuk aksi owner-only. Tambah route smoke tenant yang mengembalikan role aktif + hasil beberapa Gate.
- **Mirror**: PRD §12 matriks izin.
- **Validate**: `RbacEnforcementTest` — untuk tiap role, Gate mengizinkan/menolak persis sesuai matriks.

### Task 7: Suite test & dokumentasi
- **Action**: Lengkapi `CrossSubdomainSessionTest`, `RbacEnforcementTest`, `TenantMembershipTest`; sesuaikan suite Auth Breeze agar memakai Host apex (`kasiro.com`). Update `docs/local-dev.md` (uji login lintas subdomain). Pastikan global scope M1 tidak mengganggu auth/seed (set konteks eksplisit / `withoutTenantScope` bila perlu).
- **Mirror**: Konvensi test M1.
- **Validate**: `php artisan test` hijau menyeluruh.

## Validation
```bash
php artisan --version
php artisan migrate:fresh
php artisan test                 # Breeze auth + cross-subdomain + RBAC + membership hijau
# Smoke (php artisan serve):
curl -s -c jar -H "Host: kasiro.com" -d 'email=..&password=..' http://127.0.0.1:8000/login   # login apex
curl -s -b jar -H "Host: warungbudi.kasiro.com" http://127.0.0.1:8000/   # sesi terbawa, role aktif tampil
```

## Risks
| Risk | Likelihood | Mitigation |
|---|---|---|
| Breeze menimpa `routes/web.php` & menghapus routing tenant M1 | High | Backup sebelum install; re-merge grup apex/tenant; jalankan `TenantResolutionTest` M1 setelah merge sebagai gate regresi |
| Cookie sesi tidak terbawa lintas subdomain (config salah) | Medium | `SESSION_DOMAIN=.kasiro.com` + `CrossSubdomainSessionTest` wajib hijau (E10 CRITICAL) |
| Global scope `BelongsToTenant` mengganggu query `tenant_user`/`users` | Medium | `tenant_user` & `User` **tanpa** trait; resolusi role lintas-tenant eksplisit |
| Auth route bocor ke subdomain tenant | Low | Auth hanya di grup apex; tenant route butuh `auth`+`tenant.member` |
| Suite Auth Breeze gagal karena asumsi domain/Host | Medium | Set base test Host = apex; sesuaikan redirect `redirectGuestsTo` |
| Revoke tidak langsung berlaku | Low | Cek `status=active` per-request di `EnsureTenantMember` (E4) |

## Acceptance
- [ ] Breeze terpasang; register/login/logout/reset berjalan di apex `kasiro.com`
- [ ] **Sesi cross-subdomain**: login di apex terbawa ke `<sub>.kasiro.com` (E10/FR-14) — test hijau
- [ ] `tenant_user` + enum role/status; relasi `User↔Tenant` + `roleFor()` benar
- [ ] **RBAC server-side** sesuai matriks §12 (FR-15/NFR-6) — `RbacEnforcementTest` hijau
- [ ] **Revoke berlaku segera** & non-member ditolak (E4) — `TenantMembershipTest` hijau
- [ ] Routing tenant M1 tetap utuh (tidak ada regresi `TenantResolutionTest`/`TenantIsolationTest`)
- [ ] `php artisan test` hijau menyeluruh; `docs/local-dev.md` diperbarui

---
*Status: DRAFT — menunggu konfirmasi sebelum eksekusi. Auth: Breeze Blade session; RBAC: native Gate/Policy.*
