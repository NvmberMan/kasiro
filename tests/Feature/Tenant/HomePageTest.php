<?php

namespace Tests\Feature\Tenant;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $owner;
    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['name' => 'Budi']);
        $this->tenant = Tenant::factory()->create([
            'owner_id' => $this->owner->id,
            'name' => 'Kopi Senja',
            'theme_config' => ['layout' => 'modern', 'theme' => 'warm', 'color_palette' => 'amber'],
        ]);
        $this->tenant->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);

        $this->cashier = User::factory()->create();
        $this->tenant->users()->attach($this->cashier, ['role' => 'cashier', 'status' => 'active']);

        // Buat data ber-tenant dalam konteks aktif.
        app(TenantContext::class)->set($this->tenant);
        $category = Category::create(['name' => 'Kopi']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Espresso',
            'price' => 18000,
            'stock' => 10,
            'is_active' => true,
        ]);
        Transaction::create([
            'cashier_id' => $this->owner->id,
            'total' => 18000,
            'paid' => 20000,
            'change' => 2000,
            'payment_method' => 'cash',
            'transacted_at' => now(),
        ]);
        app(TenantContext::class)->forget();
    }

    private function url(string $path = '/'): string
    {
        return "http://{$this->tenant->subdomain}.kasiro.my.id{$path}";
    }

    public function test_home_page_renders_store_name_and_quick_actions(): void
    {
        $this->actingAs($this->owner)
            ->get($this->url('/'))
            ->assertOk()
            ->assertSee('Kopi Senja')
            ->assertSee('Buka Kasir')
            ->assertSee('Akses Cepat');
    }

    public function test_owner_sees_sales_summary(): void
    {
        $this->actingAs($this->owner)
            ->get($this->url('/'))
            ->assertOk()
            ->assertSee('Penjualan Hari Ini')
            ->assertSee('Transaksi Terbaru');
    }

    public function test_cashier_does_not_see_sales_summary(): void
    {
        $this->actingAs($this->cashier)
            ->get($this->url('/'))
            ->assertOk()
            ->assertDontSee('Penjualan Hari Ini')
            ->assertDontSee('Transaksi Terbaru');
    }

    public function test_non_member_cannot_view_home(): void
    {
        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->get($this->url('/'))
            ->assertForbidden();
    }
}
