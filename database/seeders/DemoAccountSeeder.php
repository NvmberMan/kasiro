<?php

namespace Database\Seeders;

use App\Enums\MembershipStatus;
use App\Enums\TenantRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Template;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Demo lengkap: satu akun owner yang memiliki dua toko, masing-masing dengan
 * staf, kategori, katalog produk (gambar diunduh dari internet), dan riwayat
 * transaksi selama ~45 hari terakhir.
 *
 * Idempoten: data utama dibuat dengan firstOrCreate dan katalog/transaksi hanya
 * di-generate jika toko belum punya produk, sehingga aman dijalankan ulang.
 *
 * Jalankan sendiri: php artisan db:seed --class=DemoAccountSeeder
 */
class DemoAccountSeeder extends Seeder
{
    /** Lama rentang riwayat transaksi (hari ke belakang). */
    private const HISTORY_DAYS = 45;

    public function run(): void
    {
        // Template dibutuhkan untuk template_id toko; pastikan tersedia.
        $this->callOnce(TemplateSeeder::class);

        $owner = User::firstOrCreate(
            ['email' => 'owner@kasiro.my.id'],
            ['name' => 'Budi Santoso', 'password' => 'password', 'email_verified_at' => now()],
        );

        $this->command->info("Owner: {$owner->email} (password: password)");

        foreach ($this->stores() as $store) {
            $this->seedStore($owner, $store);
        }

        // Pastikan tidak ada konteks tenant yang menempel setelah seeding.
        app(TenantContext::class)->forget();
    }

    /**
     * Definisi kedua toko beserta staf dan katalognya.
     *
     * @return array<int, array<string, mixed>>
     */
    private function stores(): array
    {
        return [
            [
                'name' => 'Kopi Senja',
                'subdomain' => 'kopisenja',
                'template' => 'kedai-kopi',
                'theme' => ['layout' => 'modern', 'theme' => 'warm', 'color_palette' => 'amber'],
                'logo_keyword' => 'coffee,logo',
                'manager' => ['name' => 'Sari Wulandari', 'email' => 'sari@kopisenja.my.id'],
                'cashiers' => [
                    ['name' => 'Andi Pratama', 'email' => 'andi@kopisenja.my.id'],
                    ['name' => 'Dewi Lestari', 'email' => 'dewi@kopisenja.my.id'],
                ],
                'catalog' => $this->coffeeCatalog(),
                'transactions' => 60,
            ],
            [
                'name' => 'Berkah Mart',
                'subdomain' => 'berkahmart',
                'template' => 'toko-retail',
                'theme' => ['layout' => 'sidebar', 'theme' => 'light', 'color_palette' => 'indigo'],
                'logo_keyword' => 'store,minimarket',
                'manager' => ['name' => 'Rudi Hartono', 'email' => 'rudi@berkahmart.my.id'],
                'cashiers' => [
                    ['name' => 'Nina Marlina', 'email' => 'nina@berkahmart.my.id'],
                    ['name' => 'Joko Susilo', 'email' => 'joko@berkahmart.my.id'],
                ],
                'catalog' => $this->retailCatalog(),
                'transactions' => 80,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $store
     */
    private function seedStore(User $owner, array $store): void
    {
        $template = Template::where('slug', $store['template'])->first();

        $tenant = Tenant::firstOrCreate(
            ['subdomain' => $store['subdomain']],
            [
                'owner_id' => $owner->id,
                'name' => $store['name'],
                'status' => Tenant::STATUS_ACTIVE,
                'template_id' => $template?->id,
                'theme_config' => $store['theme'],
            ],
        );

        $this->command->info("Toko: {$tenant->name} -> {$tenant->subdomain}.kasiro.my.id");

        // Logo toko (diunduh dari internet).
        if (! $tenant->logo_path) {
            $logo = $this->fetchImage($store['logo_keyword'], "logos/{$tenant->subdomain}.jpg", $tenant->id + 9000);
            if ($logo) {
                $tenant->update(['logo_path' => $logo]);
            }
        }

        // Keanggotaan: owner + 1 manager + 2 kasir.
        $this->ensureMember($tenant, $owner, TenantRole::Owner);

        $manager = User::firstOrCreate(
            ['email' => $store['manager']['email']],
            ['name' => $store['manager']['name'], 'password' => 'password', 'email_verified_at' => now()],
        );
        $this->ensureMember($tenant, $manager, TenantRole::Manager);

        $cashiers = [];
        foreach ($store['cashiers'] as $data) {
            $cashier = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => 'password', 'email_verified_at' => now()],
            );
            $this->ensureMember($tenant, $cashier, TenantRole::Cashier);
            $cashiers[] = $cashier;
        }

        // Semua penulisan data ber-tenant memakai konteks aktif agar tenant_id
        // terisi otomatis oleh trait BelongsToTenant.
        $context = app(TenantContext::class);
        $context->set($tenant);

        // Lewati jika katalog sudah ada (idempoten pada re-run).
        if (Product::query()->count() > 0) {
            $this->command->warn("  Katalog {$tenant->name} sudah ada, dilewati.");
            $context->forget();

            return;
        }

        $products = $this->seedCatalog($tenant, $store['catalog']);

        // Kasir untuk transaksi: owner + manager + para kasir.
        $operators = array_merge([$owner, $manager], $cashiers);
        $this->seedTransactions($products, $operators, $store['transactions']);

        $context->forget();
    }

    /**
     * Buat kategori + produk, mengunduh gambar tiap produk dari internet.
     *
     * @param  array<int, array<string, mixed>>  $catalog
     * @return array<int, Product>
     */
    private function seedCatalog(Tenant $tenant, array $catalog): array
    {
        $products = [];

        foreach ($catalog as $group) {
            $category = Category::create(['name' => $group['category']]);

            foreach ($group['items'] as $item) {
                $slug = Str::slug($item['name']);
                $image = $this->fetchImage(
                    $item['keyword'],
                    "products/{$tenant->subdomain}/{$slug}.jpg",
                    crc32($tenant->subdomain.$slug),
                );

                $products[] = Product::create([
                    'category_id' => $category->id,
                    'name' => $item['name'],
                    'image_path' => $image,
                    'price' => $item['price'],
                    'stock' => $item['stock'] ?? rand(15, 120),
                    'is_active' => $item['active'] ?? true,
                ]);
            }
        }

        $this->command->info('  '.count($products).' produk dibuat.');

        return $products;
    }

    /**
     * Generate riwayat transaksi acak (dengan itemnya) selama HISTORY_DAYS.
     *
     * @param  array<int, Product>  $products
     * @param  array<int, User>  $operators
     */
    private function seedTransactions(array $products, array $operators, int $count): void
    {
        // Hanya produk aktif & ada stok yang bisa terjual.
        $sellable = array_values(array_filter(
            $products,
            fn (Product $p) => $p->is_active && $p->stock > 0,
        ));

        if ($sellable === []) {
            return;
        }

        $methods = ['cash', 'cash', 'cash', 'qris', 'debit'];

        for ($i = 0; $i < $count; $i++) {
            $when = Carbon::now()
                ->subDays(rand(0, self::HISTORY_DAYS - 1))
                ->setTime(rand(8, 21), rand(0, 59), rand(0, 59));

            $lineCount = rand(1, 5);
            $picked = collect($sellable)->random(min($lineCount, count($sellable)));

            $lines = [];
            $total = 0;
            foreach ($picked as $product) {
                $qty = rand(1, 3);
                $unit = (float) $product->price;
                $subtotal = $unit * $qty;
                $total += $subtotal;
                $lines[] = ['product' => $product, 'qty' => $qty, 'unit' => $unit, 'subtotal' => $subtotal];
            }

            $method = $methods[array_rand($methods)];
            $paid = $method === 'cash' ? (float) (ceil($total / 5000) * 5000) : $total;
            if ($paid < $total) {
                $paid = $total;
            }

            $transaction = Transaction::create([
                'cashier_id' => $operators[array_rand($operators)]->id,
                'total' => $total,
                'paid' => $paid,
                'change' => $paid - $total,
                'transacted_at' => $when,
            ]);

            // Selaraskan timestamp pembuatan dengan waktu transaksi (laporan rapi).
            $transaction->forceFill(['created_at' => $when, 'updated_at' => $when])->saveQuietly();

            foreach ($lines as $line) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $line['product']->id,
                    'qty' => $line['qty'],
                    'unit_price' => $line['unit'],
                    'subtotal' => $line['subtotal'],
                ]);
            }
        }

        $this->command->info("  {$count} transaksi dibuat.");
    }

    /**
     * Daftarkan user sebagai anggota aktif tenant dengan peran tertentu.
     */
    private function ensureMember(Tenant $tenant, User $user, TenantRole $role): void
    {
        $tenant->users()->syncWithoutDetaching([
            $user->id => [
                'role' => $role->value,
                'status' => MembershipStatus::Active->value,
            ],
        ]);
    }

    /**
     * Unduh gambar dari internet ke disk publik. Mengembalikan path relatif
     * (untuk asset('storage/...')) atau null bila gagal. Tidak mengunduh ulang
     * jika file sudah ada.
     */
    private function fetchImage(string $keyword, string $path, int $lock): ?string
    {
        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return $path;
        }

        // Sumber utama: loremflickr (berbasis kata kunci, relevan dengan produk).
        $url = 'https://loremflickr.com/400/400/'.rawurlencode($keyword);
        $body = $this->download($url, ['lock' => $lock]);

        // Cadangan: picsum (selalu mengembalikan gambar).
        if ($body === null) {
            $body = $this->download("https://picsum.photos/seed/{$lock}/400/400");
        }

        if ($body === null) {
            $this->command->warn("  Gagal mengunduh gambar: {$keyword}");

            return null;
        }

        $disk->put($path, $body);

        return $path;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function download(string $url, array $query = []): ?string
    {
        try {
            // verify=false: banyak instalasi PHP lokal (mis. Laragon di Windows)
            // tidak mengonfigurasi CA bundle, sehingga unduhan HTTPS gagal dengan
            // cURL error 60. Ini hanya seeder demo lokal, jadi aman dilonggarkan.
            $response = Http::timeout(20)->retry(2, 500)
                ->withOptions(['verify' => false])
                ->get($url, $query);

            if ($response->successful() && strlen($response->body()) > 0) {
                return $response->body();
            }
        } catch (\Throwable $e) {
            // Diabaikan: pemanggil akan mencoba sumber cadangan / null.
        }

        return null;
    }

    /**
     * Katalog kedai kopi.
     *
     * @return array<int, array<string, mixed>>
     */
    private function coffeeCatalog(): array
    {
        return [
            ['category' => 'Kopi', 'items' => [
                ['name' => 'Espresso', 'price' => 18000, 'keyword' => 'espresso,coffee'],
                ['name' => 'Americano', 'price' => 20000, 'keyword' => 'americano,coffee'],
                ['name' => 'Cappuccino', 'price' => 25000, 'keyword' => 'cappuccino'],
                ['name' => 'Caffe Latte', 'price' => 27000, 'keyword' => 'latte,coffee'],
                ['name' => 'Kopi Susu Senja', 'price' => 22000, 'keyword' => 'iced,coffee'],
            ]],
            ['category' => 'Non-Kopi', 'items' => [
                ['name' => 'Matcha Latte', 'price' => 28000, 'keyword' => 'matcha,latte'],
                ['name' => 'Cokelat Panas', 'price' => 24000, 'keyword' => 'hot,chocolate'],
                ['name' => 'Teh Tarik', 'price' => 18000, 'keyword' => 'tea,milk'],
            ]],
            ['category' => 'Makanan', 'items' => [
                ['name' => 'Nasi Goreng Spesial', 'price' => 30000, 'keyword' => 'fried,rice'],
                ['name' => 'Mie Goreng', 'price' => 28000, 'keyword' => 'fried,noodles'],
                ['name' => 'Roti Bakar Cokelat', 'price' => 20000, 'keyword' => 'toast,bread'],
            ]],
            ['category' => 'Snack', 'items' => [
                ['name' => 'Kentang Goreng', 'price' => 18000, 'keyword' => 'french,fries'],
                ['name' => 'Pisang Goreng', 'price' => 15000, 'keyword' => 'fried,banana'],
                ['name' => 'Croissant', 'price' => 22000, 'keyword' => 'croissant'],
                ['name' => 'Donat Gula', 'price' => 12000, 'keyword' => 'donut', 'stock' => 0, 'active' => true],
            ]],
        ];
    }

    /**
     * Katalog minimarket / retail.
     *
     * @return array<int, array<string, mixed>>
     */
    private function retailCatalog(): array
    {
        return [
            ['category' => 'Sembako', 'items' => [
                ['name' => 'Beras Premium 5kg', 'price' => 68000, 'keyword' => 'rice,sack'],
                ['name' => 'Minyak Goreng 2L', 'price' => 38000, 'keyword' => 'cooking,oil'],
                ['name' => 'Gula Pasir 1kg', 'price' => 16000, 'keyword' => 'sugar'],
                ['name' => 'Telur Ayam 1kg', 'price' => 28000, 'keyword' => 'eggs'],
                ['name' => 'Tepung Terigu 1kg', 'price' => 13000, 'keyword' => 'flour'],
            ]],
            ['category' => 'Minuman', 'items' => [
                ['name' => 'Air Mineral 600ml', 'price' => 4000, 'keyword' => 'mineral,water'],
                ['name' => 'Teh Botol', 'price' => 5000, 'keyword' => 'tea,bottle'],
                ['name' => 'Kopi Sachet', 'price' => 2000, 'keyword' => 'coffee,sachet'],
                ['name' => 'Susu UHT 1L', 'price' => 18000, 'keyword' => 'milk,carton'],
            ]],
            ['category' => 'Makanan Ringan', 'items' => [
                ['name' => 'Keripik Kentang', 'price' => 12000, 'keyword' => 'potato,chips'],
                ['name' => 'Biskuit Cokelat', 'price' => 10000, 'keyword' => 'biscuit'],
                ['name' => 'Permen Mint', 'price' => 8000, 'keyword' => 'candy,mint'],
            ]],
            ['category' => 'Perawatan', 'items' => [
                ['name' => 'Sabun Mandi', 'price' => 5000, 'keyword' => 'soap,bar'],
                ['name' => 'Sampo Sachet', 'price' => 1500, 'keyword' => 'shampoo'],
                ['name' => 'Pasta Gigi', 'price' => 14000, 'keyword' => 'toothpaste'],
            ]],
            ['category' => 'Rumah Tangga', 'items' => [
                ['name' => 'Sabun Cuci Piring', 'price' => 9000, 'keyword' => 'dish,soap'],
                ['name' => 'Tisu Wajah', 'price' => 11000, 'keyword' => 'tissue'],
                ['name' => 'Detergen 800g', 'price' => 22000, 'keyword' => 'detergent'],
            ]],
        ];
    }
}
