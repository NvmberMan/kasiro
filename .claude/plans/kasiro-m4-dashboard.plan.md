# Plan: Kasiro — Milestone 4: Dashboard Platform (Beranda / Kasir Saya / Arsip)

**Source PRD**: `.claude/prds/kasiro.prd.md`
**Selected Milestone**: #4 — Beranda, Kasir Saya, Arsip (+restore) berfungsi (PRD §16)
**Depends on**: M1 + M2 + M3 COMPLETE — TenantContext, ResolveTenant, BelongsToTenant, Breeze auth, `tenant_user` pivot, `TenantRole`/`MembershipStatus`, `EnsureTenantMember`, Gate matrix, `TenantPolicy`, `CreateTenant` action, `TenantFactory` (+`archived()` state) sudah ada.
**Complexity**: Low–Medium (3 halaman read + 2 mutasi state owner-only di atas model & policy yang sudah ada; tanpa migration, tanpa tabel baru)

## Keputusan (dikonfirmasi)
- **Lokasi**: Semua halaman dashboard **apex-only** (`kasiro.com`), di dalam `Route::domain($central)`, middleware `['auth','verified']`. Tidak ada di subdomain tenant (PRD §4).
- **Arsip = soft state**, bukan hard delete: `status=archived`, `archived_at=now()`. Restore = `status=active`, `archived_at=null`. **Tidak ada data yang dihapus** (FR-8).
- **Sumber "tenant milik/terkait user"**: `User::tenants()` (BelongsToMany via `tenant_user`) difilter `wherePivot('status', active)`. Karena `CreateTenant` selalu attach owner ke `tenant_user` (owner/active), relasi ini mencakup owner maupun member — tidak perlu union dengan `ownedTenants()`. **Kasir Saya** tambahan filter `tenants.status = active`; **Arsip** filter `tenants.status = archived` **dan** owner-only.
- **Visibilitas Arsip**: hanya tenant yang user **OWNER**-nya (bukan sekadar member). Query: `User::ownedTenants()->where('status', archived)`.
- **Otorisasi mutasi**: `$this->authorize('archive', $tenant)` / `$this->authorize('restore', $tenant)` → `TenantPolicy`. Policy baca `user->roleFor($tenant)?->canManageTenantSettings()` (owner-only).
- **`roleFor()` pada tenant archived**: memfilter `MembershipStatus::Active` (bukan status tenant). Saat tenant diarsipkan, baris `tenant_user` owner tetap `active` → `roleFor()` tetap mengembalikan `Owner` → policy `restore` lolos. Tidak perlu ubah `roleFor`.
- **Route model binding `{tenant}`**: `Tenant` tidak memakai `BelongsToTenant` global scope (ia root) → binding menemukan tenant archived maupun active by ID tanpa override.
- **Form restore/archive**: `@csrf` + `@method('DELETE')` untuk restore, POST untuk archive, dengan `onsubmit="return confirm(...)"`, KISS.
- **Aksi mutasi**: dedicated Action class `ArchiveTenant` & `RestoreTenant` (mirror `CreateTenant`), atomik via `DB::transaction`.
- **Tanpa migration**: kolom `status`, `archived_at` sudah ada (M1). `TenantFactory::archived()` sudah ada.

## Summary
Milestone ini membangun **dashboard platform** di apex (`kasiro.com`) di atas auth+RBAC (M2) dan create-tenant (M3). Tiga outcome user-visible: (1) **Beranda** — sapaan + ringkasan count tenant aktif & arsip + 3 tenant aktif terbaru (mini-card link ke subdomain) + tombol "Buat Toko Baru"; (2) **Kasir Saya** — grid tenant **aktif** tempat user owner/member aktif, tiap card tampilkan logo, nama, subdomain, badge role, link subdomain, tombol **Arsipkan** (owner-only); (3) **Arsip** — daftar tenant **archived** milik user (owner) dengan `archived_at` + tombol **Pulihkan**. Arsip/restore adalah **soft state** — tidak menghapus data (FR-8). Hanya **owner** yang dapat mengarsipkan/memulihkan, ditegakkan via `TenantPolicy`.

> Catatan scope: Tidak ada perubahan skema. Editing branding, invite karyawan, billing = milestone lain.

## Patterns to Mirror
| Category | Source | Pattern |
|---|---|---|
| Route apex | `routes/web.php` | Grup apex + `['auth','verified']`; subdomain tidak menyentuh dashboard. |
| Controller tipis | `CreateTenantController` | Controller delegasi ke Action; `extends Controller`; return `View`/`RedirectResponse`. |
| Action | `CreateTenant` | Single `handle()`; `DB::transaction`; tanpa logika view. |
| Policy owner-only | `TenantPolicy::update()` | `$user->roleFor($tenant)?->canManageTenantSettings() ?? false`. |
| Query lintas-tenant | `User::roleFor()` | `User::tenants()->wherePivot('status', active)->where('tenants.status', active)`; prefix kolom wajib (ambigu `status`). |
| View Blade | `tenants/choose.blade.php` | `<x-app-layout>` + `<x-slot name="header">`; grid card Tailwind. |
| Nav | `layouts/navigation.blade.php` | `<x-nav-link :href :active="request()->routeIs(...)">` + responsive padanan. |
| Subdomain URL | `CreateTenantController::tenantUrl()` | `'http://'.$subdomain.'.'.config('tenancy.central_domain').'/'`. |
| Tests | `TenantMembershipTest`, `CreateTenant*Test` | Feature test + factory; Host apex `kasiro.com`. |

## Files to Change
| File | Action | Why |
|---|---|---|
| `app/Policies/TenantPolicy.php` | UPDATE | Tambah `archive(User,Tenant)` & `restore(User,Tenant)` — owner-only (mirror `update()`). |
| `app/Actions/ArchiveTenant.php` | CREATE | `handle(Tenant): Tenant` → set `status=archived`, `archived_at=now()` dalam `DB::transaction`. |
| `app/Actions/RestoreTenant.php` | CREATE | `handle(Tenant): Tenant` → set `status=active`, `archived_at=null` dalam `DB::transaction`. |
| `app/Models/Tenant.php` | UPDATE | Tambah `scopeActive`/`scopeArchived` Builder scope untuk readability query. |
| `app/Http/Controllers/DashboardController.php` | CREATE | `home()` → Beranda; `myStores()` → Kasir Saya; `archive()` → Arsip. |
| `app/Http/Controllers/Tenant/TenantArchiveController.php` | CREATE | `store(Tenant)` → archive; `destroy(Tenant)` → restore. |
| `routes/web.php` | UPDATE | Ganti closure `/dashboard` → `DashboardController@home`; tambah `/my-stores`, `/archive`, archive routes. |
| `resources/views/dashboard.blade.php` | UPDATE | Ganti placeholder Breeze → Beranda (sapaan, ringkasan, 3 tenant terbaru, tombol create). |
| `resources/views/dashboard/my-stores.blade.php` | CREATE | Grid tenant aktif + badge role + tombol arsipkan (owner-only) + empty state. |
| `resources/views/dashboard/archive.blade.php` | CREATE | Daftar tenant archived + `archived_at` + tombol pulihkan + empty state. |
| `resources/views/components/tenant-card.blade.php` | CREATE | Komponen reusable card tenant (logo, nama, subdomain, badge, link, slot aksi). |
| `resources/views/layouts/navigation.blade.php` | UPDATE | Tambah link Beranda, Kasir Saya, Arsip (desktop + responsive). |
| `tests/Feature/DashboardHomeTest.php` | CREATE | Beranda: sapaan, count ringkasan, 3 terbaru, tombol create. |
| `tests/Feature/MyStoresTest.php` | CREATE | Hanya tenant aktif milik/member user; badge role; tombol arsip owner-only; empty state. |
| `tests/Feature/ArchivePageTest.php` | CREATE | Hanya tenant archived **owned**; tombol restore; member-only tidak melihat; empty state. |
| `tests/Feature/TenantArchiveTest.php` | CREATE | Archive/restore owner; non-owner 403; data terjaga; `archived_at` toggle. |
| `tests/Feature/DashboardAuthTest.php` | CREATE | Guest → redirect login apex (5 endpoint). |
| `docs/local-dev.md` | UPDATE | Smoke dashboard + archive/restore lokal. |

## Tasks

### Task 1: Policy archive/restore (owner-only)
- **Action**: `TenantPolicy::archive(User $user, Tenant $tenant): bool` dan `restore(User $user, Tenant $tenant): bool` → `return $user->roleFor($tenant)?->canManageTenantSettings() ?? false;`. Policy sudah ter-register via `Gate::policy(Tenant::class, TenantPolicy::class)` — tidak perlu ubah provider.
- **Mirror**: `TenantPolicy::update()`.
- **Validate**: owner → `true`; manager/cashier/non-member → `false`; owner pada tenant **archived** tetap `true` (baris `tenant_user` owner tetap `active`).

### Task 2: Action ArchiveTenant + RestoreTenant + scope Tenant
- **Action**: `ArchiveTenant::handle(Tenant): Tenant` → `DB::transaction` update `status = STATUS_ARCHIVED`, `archived_at = now()`. `RestoreTenant::handle(Tenant): Tenant` → update `status = STATUS_ACTIVE`, `archived_at = null`. **Tidak hapus** baris `tenant_user` atau relasi. Tambah `Tenant::scopeActive(Builder)` / `Tenant::scopeArchived(Builder)` untuk readability query.
- **Mirror**: `CreateTenant::handle` (transaksi, return model).
- **Validate**: setelah archive: `isArchived()===true`, `archived_at` not null, `tenant_user` tetap; setelah restore: `isActive()===true`, `archived_at===null`.

### Task 3: DashboardController (3 halaman)
- **Action**: `DashboardController::home()`: hitung `$activeCount` (`user->tenants()->wherePivot('status', active)->where('tenants.status', active)->count()`), `$archivedCount` (`user->ownedTenants()->where('status', archived)->count()`), `$recent` (take 3, `latest('tenants.created_at')`). `myStores()`: `$tenants` aktif where member aktif, order by `tenants.name`. `archive()`: `$tenants` dari `ownedTenants()` archived, latest `archived_at`.
- **Mirror**: controller tipis `CreateTenantController`.
- **Validate**: feature test tiap method memberi himpunan tenant benar (Task 6 test). Kolom `status` prefix `tenants.` untuk hindari ambigu pivot.

### Task 4: TenantArchiveController (archive + restore)
- **Action**: `TenantArchiveController::store(Request, Tenant, ArchiveTenant)` → `$this->authorize('archive', $tenant)` + `$action->handle($tenant)` + `redirect()->route('my-stores')->with('status','tenant-archived')`. `destroy(Request, Tenant, RestoreTenant)` → authorize restore + handle + `redirect()->route('archive')->with('status','tenant-restored')`. Route model binding by ID tanpa scope.
- **Mirror**: `ProfileController::destroy` (authorize + action + redirect with status).
- **Validate**: owner → 302 ke route benar; non-owner → 403; guest → redirect login.

### Task 5: Routes + update nav
- **Action**: Di `routes/web.php` grup apex middleware `['auth','verified']`: ganti closure `/dashboard` → `DashboardController@home`; tambah `GET /my-stores` → `myStores`; `GET /archive` → `archive`; `POST /tenants/{tenant}/archive` → `TenantArchiveController@store` (name: `tenants.archive`); `DELETE /tenants/{tenant}/archive` → `TenantArchiveController@destroy` (name: `tenants.restore`). Di `navigation.blade.php`: ganti label "Dashboard" → "Beranda", tambah `<x-nav-link>` Kasir Saya + Arsip (desktop + responsive) dengan `:active="request()->routeIs('...')"`.
- **Mirror**: pola nav-link Breeze; pola route apex M3.
- **Validate**: `php artisan route:list` tampilkan 5 route baru; nav 3 link benar.

### Task 6: Views (Beranda, Kasir Saya, Arsip, tenant-card)
- **Action**: `components/tenant-card.blade.php` — props `:$tenant`, `:$role = null`, slot aksi. Render: logo (placeholder inisial jika null), nama, `subdomain`, badge role (warna berbeda per role), link absolut ke subdomain. `dashboard.blade.php` (Beranda) — sapaan, 2 stat (`$activeCount`/`$archivedCount`), grid `$recent`, tombol "Buat Toko Baru", empty state. `dashboard/my-stores.blade.php` — grid `$tenants`; tiap card: `<x-tenant-card :tenant :role="auth()->user()->roleFor($tenant)">` + slot tombol Arsipkan (`<form POST>` hanya bila `$role?->canManageTenantSettings()`). `dashboard/archive.blade.php` — list `$tenants`; tiap baris: nama, subdomain, `Diarsipkan {{ $tenant->archived_at->diffForHumans() }}`, form restore (`@method('DELETE')`). Flash status ditampilkan via session `'status'`.
- **Mirror**: `tenants/choose.blade.php`; `x-app-layout`/`x-input-error` Breeze.
- **Validate**: render 200; tombol arsip hanya untuk owner; empty state benar; link subdomain absolut.

### Task 7: Suite test + dokumentasi
- **Action**: 5 test class. `DashboardHomeTest`: login → `/dashboard` 200, lihat nama user, count benar, 3 terbaru, link create. `MyStoresTest`: hanya aktif + member aktif; archived tidak muncul; orang lain tidak muncul; badge role; tombol arsip owner-only. `ArchivePageTest`: hanya owned-archived; member-non-owner tidak lihat; tombol restore ada; empty state. `TenantArchiveTest`: owner archive → DB archived; non-owner 403; data `tenant_user` tetap; owner restore → active; `archived_at` null; restore non-owner 403. `DashboardAuthTest`: guest 5 endpoint → redirect. Update `docs/local-dev.md`. Gate regresi: suite penuh hijau (M1–M3).
- **Mirror**: `TenantMembershipTest`, `TenantArchiveTest*` konvensi.
- **Validate**: `php artisan test` hijau menyeluruh.

## Validation
```bash
php artisan --version
php artisan migrate:fresh --seed
php artisan route:list | findstr "my-stores\|archive\|dashboard"
php artisan test

# Smoke (php artisan serve):
curl -s -b jar -H "Host: kasiro.com" http://127.0.0.1:8000/dashboard
curl -s -b jar -H "Host: kasiro.com" http://127.0.0.1:8000/my-stores
curl -s -b jar -H "Host: kasiro.com" http://127.0.0.1:8000/archive
```

## Risks
| Risk | Likelihood | Mitigation |
|---|---|---|
| `roleFor()` null untuk owner saat tenant archived → restore gagal | Medium | Membership status ≠ tenant status; `tenant_user.status` owner tetap active. Test eksplisit. |
| Kasir Saya tampilkan tenant archived | Medium | Filter wajib `where('tenants.status', active)` selain `wherePivot`; test membuktikan. |
| Arsip tampilkan tenant di mana user hanya member | Medium | Query via `ownedTenants()` (owner_id); test member-non-owner tidak lihat. |
| Non-owner bisa archive/restore via crafted request | High | `$this->authorize('archive'/'restore', $tenant)` di controller; policy owner-only; test → 403. |
| Kolom `status` ambigu (pivot + tenants) | Medium | Prefix `tenants.status` di semua query; test data isolation. |
| Archive hapus data (salah paham FR-8) | Medium | Action hanya update kolom; test assert `tenant_user` tetap utuh. |

## Acceptance
- [ ] `TenantPolicy::archive`/`restore` owner-only — owner pada tenant archived tetap lolos restore
- [ ] `ArchiveTenant`/`RestoreTenant`: toggle `status`+`archived_at` atomik, **tanpa hapus data**
- [ ] **Beranda**: sapaan, count aktif+arsip, 3 terbaru, tombol create — test hijau (FR-7)
- [ ] **Kasir Saya**: hanya tenant aktif user (owner+member), badge role, tombol Arsipkan owner-only, empty state — test hijau (FR-7)
- [ ] **Arsip**: hanya tenant archived **owned**, `archived_at`, tombol Pulihkan, empty state — test hijau (FR-7)
- [ ] Archive/Restore: owner ✓, non-owner → 403, data preserved — test hijau (FR-8)
- [ ] Auth: guest → redirect login apex (5 endpoint) — test hijau
- [ ] Nav apex: Beranda / Kasir Saya / Arsip dengan active state
- [ ] Tanpa migration; tanpa regresi M1/M2/M3; `php artisan test` hijau menyeluruh

---
*Status: DRAFT — menunggu konfirmasi sebelum eksekusi. Apex-only dashboard; archive = soft state owner-only; tanpa perubahan skema.*
