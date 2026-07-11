<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $owner;
    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->tenant = Tenant::factory()->create([
            'owner_id' => $this->owner->id,
            'name' => 'Kopi Senja',
        ]);
        $this->tenant->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);

        $this->cashier = User::factory()->create();
        $this->tenant->users()->attach($this->cashier, ['role' => 'cashier', 'status' => 'active']);

        app(TenantContext::class)->set($this->tenant);
        $coffee = Category::create(['name' => 'Kopi']);
        $food = Category::create(['name' => 'Makanan']);
        Product::create(['category_id' => $coffee->id, 'name' => 'Espresso', 'price' => 18000, 'stock' => 10, 'is_active' => true]);
        Product::create(['category_id' => $food->id, 'name' => 'Nasi Goreng', 'price' => 30000, 'stock' => 5, 'is_active' => true]);
        Transaction::create([
            'cashier_id' => $this->owner->id, 'total' => 18000, 'paid' => 20000,
            'change' => 2000, 'transacted_at' => now(),
        ]);
        app(TenantContext::class)->forget();
    }

    private function url(string $path): string
    {
        return "http://{$this->tenant->subdomain}.kasiro.my.id{$path}";
    }

    /* ---- Client-side search/filter/sort controls render with all rows present ---- */

    public function test_products_page_has_client_side_controls_and_all_rows(): void
    {
        $this->actingAs($this->owner)
            ->get($this->url('/products'))
            ->assertOk()
            ->assertSee('Espresso')
            ->assertSee('Nasi Goreng')
            ->assertSee('listController')          // shared Alpine controller is included
            ->assertSee('Semua Kategori')          // category filter
            ->assertSee('data-name', false);       // rows carry filter metadata
    }

    public function test_categories_page_has_client_side_controls(): void
    {
        $this->actingAs($this->owner)
            ->get($this->url('/categories'))
            ->assertOk()
            ->assertSee('Kopi')
            ->assertSee('Makanan')
            ->assertSee('listController');
    }

    public function test_my_stores_page_has_role_filter(): void
    {
        Tenant::factory()->create(['owner_id' => $this->owner->id, 'name' => 'Berkah Mart'])
            ->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($this->owner)
            ->get('http://kasiro.my.id/my-stores')
            ->assertOk()
            ->assertSee('Kopi Senja')
            ->assertSee('Berkah Mart')
            ->assertSee('Semua Peran')
            ->assertSee('listController');
    }

    public function test_archive_page_has_client_side_controls(): void
    {
        Tenant::factory()->archived()->create(['owner_id' => $this->owner->id, 'name' => 'Toko Tutup']);

        $this->actingAs($this->owner)
            ->get('http://kasiro.my.id/archive')
            ->assertOk()
            ->assertSee('Toko Tutup')
            ->assertSee('listController');
    }

    /* ---- #1: cashier may view Products & Categories (nav + access) ---- */

    public function test_cashier_can_view_products_and_categories(): void
    {
        $this->actingAs($this->cashier)->get($this->url('/products'))->assertOk();
        $this->actingAs($this->cashier)->get($this->url('/categories'))->assertOk();
    }

    public function test_cashier_nav_shows_product_and_category_links(): void
    {
        // POS page renders the tenant layout nav for the cashier.
        $response = $this->actingAs($this->cashier)->get($this->url('/pos'))->assertOk();
        $response->assertSee(route('tenant.products.index', ['subdomain' => $this->tenant->subdomain]));
        $response->assertSee(route('tenant.categories.index', ['subdomain' => $this->tenant->subdomain]));
    }

    /* ---- #4: dashboard (studio beranda) shows the user's own stores ---- */

    public function test_dashboard_shows_user_stores(): void
    {
        $this->actingAs($this->owner)
            ->get('http://kasiro.my.id/dashboard')
            ->assertOk()
            ->assertSee('Toko Aktif')
            ->assertSee('Sistem Kasir Terbaru');
    }
}
