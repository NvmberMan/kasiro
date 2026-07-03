<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosRbacTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $owner;
    private User $manager;
    private User $cashier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner   = User::factory()->create();
        $this->manager = User::factory()->create();
        $this->cashier = User::factory()->create();

        $this->tenant = Tenant::factory()->create(['owner_id' => $this->owner->id]);
        $this->tenant->users()->attach($this->owner,   ['role' => 'owner',   'status' => 'active']);
        $this->tenant->users()->attach($this->manager, ['role' => 'manager', 'status' => 'active']);
        $this->tenant->users()->attach($this->cashier, ['role' => 'cashier', 'status' => 'active']);

        $this->product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'price'     => 10000,
            'stock'     => 50,
        ]);
    }

    private function url(string $path): string
    {
        return "http://{$this->tenant->subdomain}.kasiro.my.id{$path}";
    }

    // ---- POS access (all roles) ----

    public function test_owner_can_access_pos(): void
    {
        $this->actingAs($this->owner)->get($this->url('/pos'))->assertOk();
    }

    public function test_manager_can_access_pos(): void
    {
        $this->actingAs($this->manager)->get($this->url('/pos'))->assertOk();
    }

    public function test_cashier_can_access_pos(): void
    {
        $this->actingAs($this->cashier)->get($this->url('/pos'))->assertOk();
    }

    // ---- Transaction history (owner + manager only) ----

    public function test_owner_can_view_transactions(): void
    {
        $this->actingAs($this->owner)->get($this->url('/transactions'))->assertOk();
    }

    public function test_manager_can_view_transactions(): void
    {
        $this->actingAs($this->manager)->get($this->url('/transactions'))->assertOk();
    }

    public function test_cashier_cannot_view_transactions(): void
    {
        $this->actingAs($this->cashier)->get($this->url('/transactions'))->assertForbidden();
    }

    // ---- Product management (owner + manager only) ----

    public function test_manager_can_create_product(): void
    {
        $this->actingAs($this->manager)
            ->post($this->url('/products'), [
                'name'  => 'Produk Manager',
                'price' => 5000,
                'stock' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['name' => 'Produk Manager']);
    }

    public function test_cashier_cannot_view_product_create_form(): void
    {
        $this->actingAs($this->cashier)
            ->get($this->url('/products/create'))
            ->assertForbidden();
    }

    // ---- Non-member cannot access tenant ----

    public function test_non_member_is_rejected(): void
    {
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->get($this->url('/pos'))->assertForbidden();
    }
}
