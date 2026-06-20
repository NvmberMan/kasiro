# Plan: Kasiro — Milestone 3: Pembuatan Tenant (Custom / Template / Showcase)

**Source PRD**: `.claude/prds/kasiro.prd.md`
**Selected Milestone**: #3 — Pembuatan tenant lewat 3 jalur; subdomain & branding tersimpan
**Depends on**: Milestone 1 (`kasiro.plan.md`, COMPLETE) + Milestone 2 (`kasiro-m2-auth-rbac.plan.md`, COMPLETE) — TenantContext, ResolveTenant, BelongsToTenant, routing apex/subdomain, Breeze auth, `tenant_user` pivot, `TenantRole`/`MembershipStatus`, `EnsureTenantMember`, Gate matrix, `ValidSubdomain` sudah ada.
**Complexity**: Medium (3 create flow di atas satu controller + form-request; tabel `templates`; snapshot theme_config; upload logo; auto-membership owner)

## Keputusan (dikonfirmasi)
- **Whitelist layout** (3): `modern`, `classic`, `retro` — masing-masing = Blade layout `resources/views/tenant/layouts/{layout}.blade.php` (PRD §13). Disimpan di `config/branding.php` sebagai single source of truth, dibaca form-request & view.
- **Whitelist theme/mode** (3): `light`, `dark`, `warm`.
- **Whitelist color palette** (5): `default`, `emerald`, `indigo`, `rose`, `amber` — tiap palette = set CSS custom properties (`--brand-primary`, `--brand-accent`, `--brand-bg`, `--brand-fg`) di `config/branding.php`.
- **Bentuk `theme_config`** (JSON, denormalized): `{ "layout": "modern", "theme": "light", "color_palette": "emerald" }`. Konsisten dengan `TenantFactory` yang sudah ada. CSS properties di-resolve dari palette key saat render, bukan disimpan di kolom.
- **Storage logo** (OQ-9 ditunda): **local disk publik** `storage/app/public/logos` via `Storage::disk('public')`, kolom `tenants.logo_path` simpan path relatif. `php artisan storage:link` wajib. S3 di luar scope.
- **Wizard approach**: **single POST endpoint per flow + form Blade ringan** (bukan multi-step stateful/session). Pemilihan layout/theme/palette dikirim sebagai field form biasa di flow custom; di flow template/showcase field itu absen (di-derive dari template). KISS — tanpa state antar-langkah.
- **Template snapshot**: pada flow template & showcase, `theme_config = template.default_config` di-**copy** saat create (denormalisasi), `template_id = template.id`. Perubahan template kemudian **tidak** mengubah tenant lama (PRD §13).
- **Custom flow**: `template_id = null`, `theme_config` dibangun dari pilihan whitelist user.
- **Setelah create**: redirect **absolute** ke `http://<subdomain>.<central>/` (subdomain tenant baru), bukan apex.
- **Auto-membership**: buat 1 baris `tenant_user` (`role=owner`, `status=active`) menautkan user login → tenant baru, dalam **satu DB transaction** dengan insert tenant.
- **FK `template_id`**: ditambahkan via migration `add_template_fk_to_tenants` (kolom sudah ada nullable dari M1), `nullOnDelete`.

## Summary
Milestone ini membangun **onboarding pembuatan tenant** di apex (`kasiro.com`), di atas auth + RBAC M2. Outcome user-visible (PRD §16): user terautentikasi bisa **membuat tenant aktif lewat 3 jalur** — (1) **Custom**: pilih layout + theme + palette dari whitelist, lalu isi name/subdomain/logo; (2) **Template**: pilih preset dari tabel `templates`, `theme_config` = snapshot `default_config`, lalu isi name/subdomain/logo; (3) **Quick-create dari showcase**: template fixed dari item galeri yang diklik, lewati kustomisasi, hanya isi name/subdomain/logo. Setiap jalur menyimpan **subdomain** (validasi `ValidSubdomain`) + **branding** (`theme_config`, `logo_path`), membuat **owner membership** otomatis, lalu **redirect ke subdomain tenant baru**. Subdomain rendering memakai layout + CSS custom properties dari `theme_config` (membuktikan branding tersimpan & terpakai).

> Catatan scope: Editing branding tenant setelah create = Milestone Pengaturan Tenant (bukan di sini). Invite karyawan = M6. Di sini cukup **create + owner auto-join + render branding** untuk membuktikan tiga jalur bekerja. Galeri showcase = render baris `templates` ber-`is_published=true`; tanpa CMS.

## Patterns to Mirror
| Category | Source | Pattern |
|---|---|---|
| Route apex | `routes/web.php` (`Route::domain($central)`) | Semua route create-tenant di grup apex + middleware `['auth','verified']`; tenant subdomain TIDAK menyentuh create flow. |
| Migration style | `database/migrations/2025_06_20_000001_create_tenants_table.php` | Anonymous class migration; `add_*_fk_to_tenants` mirror `2025_06_20_000003_add_owner_fk_to_tenants.php` (`nullOnDelete`). |
| Validation rule | `app/Rules/ValidSubdomain.php` | Subdomain divalidasi lewat rule yang sudah ada — JANGAN duplikasi regex/reserved-list; reuse `new ValidSubdomain()`. |
| Enum + helper | `app/Enums/TenantRole.php` | Owner membership pakai `TenantRole::Owner` & `MembershipStatus::Active` (bukan string literal di kode app). |
| Pivot attach | `Tenant::users()` + `TenantMembershipTest` | Owner row via `$tenant->users()->attach($user, ['role'=>TenantRole::Owner->value,'status'=>MembershipStatus::Active->value])`. |
| Mass-assignment safety | `BelongsToTenant` | `Tenant` TIDAK pakai `BelongsToTenant` (tenant adalah root, bukan child) — create eksplisit; `theme_config`/`template_id` di-set controller, bukan dari raw input mentah. |
| Form-request style | Breeze `RegisteredUserController::store` validate array | Pakai dedicated FormRequest (`CreateTenantRequest`) dengan `rules()` array; whitelist via `Rule::in(array_keys(config('branding.*')))`. |
| Factory | `TenantFactory`, `TenantUserFactory` | Tambah `TemplateFactory`; `theme_config` shape konsisten dengan `TenantFactory`. |
| Tests | `TenantMembershipTest`, `TenantResolutionTest` | Feature test + Host header apex (`http://kasiro.com/...`); assert redirect ke subdomain via absolute URL. |

## Files to Change
| File | Action | Why |
|---|---|---|
| `database/migrations/xxxx_create_templates_table.php` | CREATE | Tabel `templates` (PRD §10): `id,name,slug(unique),description,preview_image(nullable),default_config(json),is_published(bool default false),timestamps`. |
| `database/migrations/xxxx_add_template_fk_to_tenants.php` | CREATE | FK `tenants.template_id → templates.id` `nullOnDelete` (kolom sudah ada nullable dari M1). |
| `app/Models/Template.php` | CREATE | Model `Template`; cast `default_config→array`, `is_published→bool`; scope `published()`; relasi `tenants()` hasMany. |
| `app/Models/Tenant.php` | UPDATE | Tambah relasi `template(): BelongsTo`; helper `layout()`, `colorPalette()` baca `theme_config`. |
| `config/branding.php` | CREATE | Single source of truth whitelist: `layouts` (3), `themes` (3), `palettes` (5 → map CSS vars), + `defaults`. |
| `app/Http/Controllers/Tenant/CreateTenantController.php` | CREATE | Apex controller: `chooseFlow()`, `createCustom()`/`storeCustom()`, `createFromTemplate()`/`storeFromTemplate()`, `showcase()`, `storeFromShowcase()`. |
| `app/Http/Requests/CreateTenantRequest.php` | CREATE | Validasi bersama: `name`,`subdomain`(`ValidSubdomain`),`logo`(image,max,mimes). Flag/subclass untuk field branding (custom) vs `template_id` (template/showcase). |
| `app/Actions/CreateTenant.php` | CREATE | Action transaksional: insert `Tenant` + attach owner `tenant_user` + simpan logo; menerima `theme_config` & `template_id` ter-resolve. Dipanggil ketiga flow (DRY). |
| `app/Support/ThemeConfig.php` | CREATE | Helper: `fromCustomInput(array): array`, `fromTemplate(Template): array` (snapshot), `cssVariables(array): array` (resolve palette → CSS props untuk `<head>`). |
| `routes/web.php` | UPDATE | Tambah grup route create-tenant di apex middleware `['auth','verified']`. |
| `resources/views/tenants/choose.blade.php` | CREATE | Landing 3 jalur (Custom / Template / Showcase). |
| `resources/views/tenants/create-custom.blade.php` | CREATE | Form custom: radio layout/theme/palette + name/subdomain/logo. |
| `resources/views/tenants/create-template.blade.php` | CREATE | Pilih template (grid published) → form name/subdomain/logo (hidden `template_id`). |
| `resources/views/tenants/showcase.blade.php` | CREATE | Galeri published; tiap item link quick-create membawa `template` fixed. |
| `resources/views/tenants/partials/branding-fields.blade.php` | CREATE | Partial name/subdomain/logo dipakai ketiga form (DRY). |
| `resources/views/tenant/layouts/modern.blade.php` | CREATE | Layout `modern` + inject CSS vars dari `theme_config` via `<x-brand-styles>`. |
| `resources/views/tenant/layouts/classic.blade.php` | CREATE | Layout `classic`. |
| `resources/views/tenant/layouts/retro.blade.php` | CREATE | Layout `retro`. |
| `resources/views/components/brand-styles.blade.php` | CREATE | Komponen `<style>` yang merender CSS vars dari `ThemeConfig::cssVariables()`. |
| `database/factories/TemplateFactory.php` | CREATE | Factory template + state `published()`; `default_config` shape valid. |
| `database/seeders/TemplateSeeder.php` | CREATE | Seed ≥3 template published untuk showcase. |
| `database/seeders/DatabaseSeeder.php` | UPDATE | Panggil `TemplateSeeder`. |
| `tests/Feature/CreateTenantCustomTest.php` | CREATE | Flow 1: custom theme tersimpan, `template_id=null`, owner membership, redirect subdomain. |
| `tests/Feature/CreateTenantTemplateTest.php` | CREATE | Flow 2: snapshot `default_config→theme_config`, snapshot independen dari perubahan template. |
| `tests/Feature/CreateTenantShowcaseTest.php` | CREATE | Flow 3: quick-create dari published item; template fixed; hanya name/subdomain/logo. |
| `tests/Feature/CreateTenantValidationTest.php` | CREATE | Subdomain invalid/reserved/duplikat; whitelist violation; guest → login apex. |
| `tests/Feature/Storage/LogoUploadTest.php` | CREATE | `Storage::fake('public')`; logo tersimpan; non-image ditolak. |
| `docs/local-dev.md` | UPDATE | Catatan `storage:link`, uji 3 flow lokal, redirect subdomain. |

## Tasks

### Task 1: Tabel `templates` + FK `template_id` + model
- **Action**: Migration `create_templates_table` (`id,name,slug unique,description nullable,preview_image nullable,default_config json,is_published bool default false,timestamps`). Migration `add_template_fk_to_tenants` (`$table->foreign('template_id')->references('id')->on('templates')->nullOnDelete()`). Model `App\Models\Template` (cast `default_config→array`,`is_published→bool`; `scopePublished`; `tenants(): HasMany`). Tambah `Tenant::template(): BelongsTo`.
- **Mirror**: `create_tenants_table` + `add_owner_fk_to_tenants` (anonymous class, `nullOnDelete`).
- **Validate**: `php artisan migrate:fresh`; tinker: `Template::factory()->create()` + FK constraint hidup; `Tenant::find(x)->template` resolves.

### Task 2: Config whitelist branding + helper `ThemeConfig`
- **Action**: `config/branding.php` ekspor `layouts` (`modern`,`classic`,`retro`), `themes` (`light`,`dark`,`warm`), `palettes` (`default`,`emerald`,`indigo`,`rose`,`amber` → tiap key map ke `['--brand-primary'=>...,'--brand-accent'=>...,'--brand-bg'=>...,'--brand-fg'=>...]`), dan `defaults`. `App\Support\ThemeConfig`: `fromCustomInput(array $input): array` (validasi terhadap whitelist + fallback default), `fromTemplate(Template $t): array` (copy `default_config`, validasi key), `cssVariables(array $themeConfig): array` (resolve palette → CSS props; fallback `default`).
- **Mirror**: `config/tenancy.php` (struktur config), `TenantFactory.theme_config` shape.
- **Validate**: Unit test `ThemeConfig`: input valid → shape benar; palette tak dikenal → fallback `default`; `fromTemplate` snapshot persis `default_config`.

### Task 3: `CreateTenantRequest` + reuse `ValidSubdomain`
- **Action**: `App\Http\Requests\CreateTenantRequest`: `authorize()` true (route sudah `auth`); `rules()` base → `name: required|string|max:255`, `subdomain: required|string|new ValidSubdomain()`, `logo: nullable|image|mimes:png,jpg,jpeg,webp|max:2048`. Extend dengan dua subclass tipis: `CreateTenantCustomRequest` (tambah `layout`,`theme`,`color_palette` via `Rule::in`), `CreateTenantFromTemplateRequest` (tambah `template_id: required|integer|exists:templates,id`).
- **Mirror**: Breeze `RegisteredUserController::store` validate array; `ValidSubdomain` reuse.
- **Validate**: `CreateTenantValidationTest` — subdomain reserved/duplikat/format salah ditolak; layout/palette di luar whitelist ditolak.

### Task 4: Action `CreateTenant` (transaksi + owner membership + logo)
- **Action**: `App\Actions\CreateTenant::handle(User $owner, array $data, array $themeConfig, ?int $templateId): Tenant`. Dalam `DB::transaction`: simpan logo bila ada (`Storage::disk('public')->putFile('logos', $file)` → `logo_path`), buat `Tenant`, lalu `$tenant->users()->attach($owner->id, ['role'=>TenantRole::Owner->value,'status'=>MembershipStatus::Active->value])`. Hapus file logo bila exception. Return tenant.
- **Mirror**: `TenantMembershipTest` attach pattern; enum values bukan string literal.
- **Validate**: Feature: setelah create, ada 1 `tenant_user` (owner/active); `owner_id` = user; rollback bila attach gagal.

### Task 5: Controller + routes (3 flow) di apex
- **Action**: `App\Http\Controllers\Tenant\CreateTenantController`: `chooseFlow()` → view `tenants.choose`; `createCustom()` → form custom; `storeCustom(CreateTenantCustomRequest)` → `ThemeConfig::fromCustomInput` + `CreateTenant::handle(templateId=null)`; `createFromTemplate()` → list published; `storeFromTemplate(CreateTenantFromTemplateRequest)` → load published `Template`, `ThemeConfig::fromTemplate`, `CreateTenant::handle`; `showcase()` → galeri published; `storeFromShowcase(CreateTenantFromTemplateRequest)` → identik template flow. Semua `store*` → `redirect()->away("http://{$tenant->subdomain}.".config('tenancy.central_domain')."/")`. Routes di grup apex middleware `['auth','verified']`.
- **Mirror**: `routes/web.php` grup apex; `redirect()->away()` untuk URL absolut lintas domain.
- **Validate**: POST sukses → 302 header `Location: http://<sub>.kasiro.com/`; guest → redirect login apex.

### Task 6: Views + Blade layouts branding
- **Action**: `tenants/choose` (3 card jalur), `create-custom` (radio layout/theme/palette + preview swatch), `create-template` (grid card published + hidden `template_id`), `showcase` (galeri published quick-create), partial `branding-fields` (name/subdomain/logo error). Layout `tenant/layouts/{modern,classic,retro}.blade.php` extend induk yang sama, tiap layout memanggil `<x-brand-styles :config="$tenant->theme_config" />`. Komponen `brand-styles.blade.php` → render `<style>:root{ --brand-...: ...; }</style>` dari `ThemeConfig::cssVariables`. Route `tenant.home` pilih layout dari `$tenant->theme_config['layout']`.
- **Mirror**: Breeze Blade components (`x-app-layout`, `x-input-error`); `layouts/guest.blade.php` struktur.
- **Validate**: Render ketiga form 200; subdomain baru muat layout + CSS vars (cek `:root` di HTML response).

### Task 7: Factory + seeder showcase
- **Action**: `TemplateFactory` (fields valid, `default_config` shape `{layout,theme,color_palette}` dari whitelist, `is_published=false`) + state `published()`. `TemplateSeeder` seed ≥3 published: "Kedai Kopi" (modern/warm/emerald), "Toko Retail" (classic/light/indigo), "Restoran" (retro/dark/rose). `DatabaseSeeder` panggil `TemplateSeeder`.
- **Mirror**: `TenantFactory`, `TenantUserFactory` style.
- **Validate**: `migrate:fresh --seed`; `Template::published()->count() >= 3`; showcase merender seeded rows.

### Task 8: Suite test + dokumentasi
- **Action**: Lengkapi semua test class (lihat Files to Change). Semua logo test → `Storage::fake('public')`. Assert snapshot independen (ubah `default_config` setelah create → `tenant->theme_config` lama tetap). Regresi: jalankan `php artisan test` penuh sebelum commit. Update `docs/local-dev.md`.
- **Mirror**: `TenantMembershipTest`, `TenantResolutionTest` konvensi.
- **Validate**: `php artisan test` hijau menyeluruh (M1+M2+M3, tanpa regresi).

## Validation
```bash
php artisan --version
php artisan migrate:fresh --seed
php artisan storage:link
php artisan test

# Smoke (php artisan serve):
curl -s -b jar -H "Host: kasiro.com" http://127.0.0.1:8000/tenants/create
curl -s -b jar -H "Host: kasiro.com" http://127.0.0.1:8000/tenants/showcase
# POST custom → 302 Location: http://<subdomain>.kasiro.com/
curl -s -H "Host: <subdomain>.kasiro.com" http://127.0.0.1:8000/ | grep -- '--brand-'
```

## Risks
| Risk | Likelihood | Mitigation |
|---|---|---|
| `theme_config` menyimpan nilai di luar whitelist (input mentah) | Medium | Semua write lewat `ThemeConfig::fromCustomInput/fromTemplate` + `Rule::in` di FormRequest |
| Snapshot template jadi live reference | Medium | `fromTemplate` copy `default_config`; test ubah template setelah create → tenant lama tetap |
| `Tenant` kena global scope saat create di apex tanpa TenantContext | Medium | `Tenant` tidak pakai `BelongsToTenant`; create eksplisit tanpa scope |
| Logo orphan bila transaksi rollback | Medium | `DB::transaction` + hapus file pada catch |
| Redirect ke subdomain salah (relative bukan absolute) | Medium | `redirect()->away("http://...")` assert URL absolut di test |
| Race subdomain (dua user klaim bersamaan) | Low | `ValidSubdomain` + unique index DB; tangani `QueryException` |
| `template_id` menunjuk template unpublished via tamper | Low | Load dengan `published()` scope; validasi `exists` + cek is_published |

## Acceptance
- [ ] Tabel `templates` + FK `template_id → templates.id` (`nullOnDelete`); model `Template` — `migrate:fresh --seed` hijau
- [ ] `config/branding.php` whitelist: 3 layout, 3 theme, 5 palette; `ThemeConfig` helper teruji (unit)
- [ ] **Flow Custom**: theme dari whitelist; `template_id=null`; owner membership; redirect subdomain — test hijau
- [ ] **Flow Template**: snapshot `default_config→theme_config`; `template_id` terisi; snapshot independen — test hijau
- [ ] **Flow Showcase**: quick-create dari published item; template fixed — test hijau
- [ ] **Validasi**: subdomain reserved/duplikat/format ditolak; whitelist violation ditolak; guest → login apex
- [ ] **Logo**: tersimpan di disk `public`; `logo_path` terisi; non-image ditolak
- [ ] **Owner auto-membership**: 1 `tenant_user` (owner/active) + `owner_id` set, dalam satu transaksi
- [ ] Subdomain baru merender layout + CSS vars sesuai `theme_config`
- [ ] Tanpa regresi M1/M2; `php artisan test` hijau menyeluruh; `docs/local-dev.md` diperbarui

---
*Status: DRAFT — menunggu konfirmasi sebelum eksekusi. 3 flow via satu Action transaksional; theme via config whitelist + ThemeConfig helper; logo di disk public; snapshot template denormalized.*
