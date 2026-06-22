# Plan: Kasiro M5 — Aplikasi POS Tenant

**Source PRD**: §7 MVP → §8.2 FR-10/11/12, §10 schema, §15 E11
**Milestone**: #5 — Kasir bisa transaksi; produk & kategori dikelola; stok berkurang atomik

## Summary

M5 membangun **inti aplikasi kasir (POS) di sisi tenant** (`*.kasiro.com`):
- CRUD **Produk** & **Kategori** (Manager + Owner only untuk write)
- **Interface POS** — grid produk, keranjang Alpine.js, checkout
- **Transaksi** — record + pengurangan stok atomik (DB lock, E11 race condition)
- **Riwayat transaksi** — Owner + Manager view

Semua data ter-isolasi via `BelongsToTenant` global scope (PRD NFR-5, E8).

## Schema (4 new tables)

| Table | Key columns |
|---|---|
| `categories` | id, tenant_id (idx), name |
| `products` | id, tenant_id, category_id (nullable, FK), name, sku, price decimal(10,2), stock int, is_active bool |
| `transactions` | id, tenant_id, cashier_id (FK users), total/paid/change decimal(12,2), payment_method, transacted_at |
| `transaction_items` | id, tenant_id, transaction_id (FK), product_id (FK), qty, unit_price decimal(10,2), subtotal decimal(10,2) |

Index komposit `(tenant_id, category_id)` di products.

## RBAC Matrix

| Aksi | Owner | Manager | Cashier |
|---|---|---|---|
| Lihat produk/kategori | ✓ | ✓ | ✓ |
| CRUD produk/kategori | ✓ | ✓ | ✗ |
| Buat transaksi (POS) | ✓ | ✓ | ✓ |
| Lihat riwayat transaksi | ✓ | ✓ | ✗ |

## Tasks

### Task 1: Migrations
4 migration files (timestamp-ordered).

### Task 2: Models + Factories
Category, Product, Transaction, TransactionItem — semua pakai BelongsToTenant.

### Task 3: Policies
CategoryPolicy, ProductPolicy, TransactionPolicy — registered via auto-discovery.

### Task 4: CreateTransaction action
`DB::transaction` + `lockForUpdate()` (E11 protection) + stock validation + item creation + stock decrement.

### Task 5: Controllers
CategoryController, ProductController, PosController, TransactionController — semua pakai `Gate::authorize`.

### Task 6: Routes
Tambah ke tenant route group: `/categories`, `/products`, `/pos`, `/pos/checkout`, `/transactions`.

### Task 7: Tenant layout nav + `<x-tenant-page>` component
- Tambah nav bar ke tenant layouts (partial shared)
- `resources/views/components/tenant-page.blade.php` — wraps dynamic layout

### Task 8: Views
categories/index+form, products/index+form, pos/index (Alpine.js), transactions/index.

### Task 9: Tests
CategoryCrudTest, ProductCrudTest, PosTransactionTest, TransactionStockTest, PosRbacTest.

## Risks
| Risk | Mitigation |
|---|---|
| Race condition stok minus (E11) | `lockForUpdate()` + validasi stok dalam transaction |
| Kebocoran data lintas tenant | BelongsToTenant global scope + test isolasi |
| Alpine.js cart complexity | Simpan cart di x-data; submit sebagai JSON via form hidden input |

---
*Status: in-progress*
