<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $owner;
    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner  = User::factory()->create();
        $this->tenant = Tenant::factory()->create(['owner_id' => $this->owner->id]);
        $this->tenant->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);

        $this->cashier = User::factory()->create();
        $this->tenant->users()->attach($this->cashier, ['role' => 'cashier', 'status' => 'active']);
    }

    private function url(string $path): string
    {
        return "http://{$this->tenant->subdomain}.kasiro.com{$path}";
    }

    private function validProductData(array $overrides = []): array
    {
        return array_merge([
            'name'      => 'Kopi Hitam',
            'price'     => 10000,
            'stock'     => 50,
            'is_active' => 1,
        ], $overrides);
    }

    public function test_owner_can_view_product_list(): void
    {
        Product::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Teh Manis']);

        $this->actingAs($this->owner)
            ->get($this->url('/products'))
            ->assertOk()
            ->assertSee('Teh Manis');
    }

    public function test_cashier_can_view_product_list(): void
    {
        $this->actingAs($this->cashier)
            ->get($this->url('/products'))
            ->assertOk();
    }

    public function test_owner_can_create_product(): void
    {
        $this->actingAs($this->owner)
            ->post($this->url('/products'), $this->validProductData())
            ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'tenant_id' => $this->tenant->id,
            'name'      => 'Kopi Hitam',
            'stock'     => 50,
        ]);
    }

    public function test_cashier_cannot_create_product(): void
    {
        $this->actingAs($this->cashier)
            ->post($this->url('/products'), $this->validProductData())
            ->assertForbidden();
    }

    public function test_owner_can_update_product(): void
    {
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->owner)
            ->put($this->url("/products/{$product->id}"), $this->validProductData(['name' => 'Kopi Susu', 'stock' => 20]))
            ->assertRedirect();

        $this->assertEquals('Kopi Susu', $product->fresh()->name);
        $this->assertEquals(20, $product->fresh()->stock);
    }

    public function test_cashier_cannot_update_product(): void
    {
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->cashier)
            ->put($this->url("/products/{$product->id}"), $this->validProductData())
            ->assertForbidden();
    }

    public function test_owner_can_delete_product(): void
    {
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->owner)
            ->delete($this->url("/products/{$product->id}"))
            ->assertRedirect();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_product_is_isolated_per_tenant(): void
    {
        $other   = Tenant::factory()->create();
        $product = Product::factory()->create(['tenant_id' => $other->id]);

        $this->actingAs($this->owner)
            ->put($this->url("/products/{$product->id}"), $this->validProductData(['name' => 'Hack']))
            ->assertStatus(404);
    }

    public function test_inactive_product_is_listed(): void
    {
        Product::factory()->inactive()->create(['tenant_id' => $this->tenant->id, 'name' => 'Produk Nonaktif']);

        $this->actingAs($this->owner)
            ->get($this->url('/products'))
            ->assertOk()
            ->assertSee('Produk Nonaktif');
    }
}
