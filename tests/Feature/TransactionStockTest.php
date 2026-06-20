<?php

namespace Tests\Feature;

use App\Actions\CreateTransaction;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class TransactionStockTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create();
        $this->tenant  = Tenant::factory()->create(['owner_id' => $this->cashier->id]);
        $this->tenant->users()->attach($this->cashier, ['role' => 'cashier', 'status' => 'active']);

        // Set TenantContext so BelongsToTenant trait auto-fills tenant_id
        app(TenantContext::class)->set($this->tenant);
    }

    protected function tearDown(): void
    {
        app(TenantContext::class)->forget();
        parent::tearDown();
    }

    public function test_stock_decrements_correctly_after_transaction(): void
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'price'     => 5000,
            'stock'     => 10,
        ]);

        app(CreateTransaction::class)->handle(
            cashier: $this->cashier,
            items: [['product_id' => $product->id, 'qty' => 3]],
            paid: 15000,
        );

        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_transaction_fails_when_stock_insufficient(): void
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'price'     => 5000,
            'stock'     => 2,
        ]);

        $this->expectException(RuntimeException::class);

        app(CreateTransaction::class)->handle(
            cashier: $this->cashier,
            items: [['product_id' => $product->id, 'qty' => 5]],
            paid: 25000,
        );
    }

    public function test_stock_is_not_decremented_when_transaction_fails(): void
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'price'     => 5000,
            'stock'     => 2,
        ]);

        try {
            app(CreateTransaction::class)->handle(
                cashier: $this->cashier,
                items: [['product_id' => $product->id, 'qty' => 5]],
                paid: 25000,
            );
        } catch (RuntimeException) {
        }

        $this->assertEquals(2, $product->fresh()->stock);
    }

    public function test_transaction_with_multiple_products(): void
    {
        $p1 = Product::factory()->create(['tenant_id' => $this->tenant->id, 'price' => 5000, 'stock' => 10]);
        $p2 = Product::factory()->create(['tenant_id' => $this->tenant->id, 'price' => 3000, 'stock' => 8]);

        $tx = app(CreateTransaction::class)->handle(
            cashier: $this->cashier,
            items: [
                ['product_id' => $p1->id, 'qty' => 2],
                ['product_id' => $p2->id, 'qty' => 3],
            ],
            paid: 25000,
        );

        $this->assertEquals(19000, $tx->total);
        $this->assertEquals(8, $p1->fresh()->stock);
        $this->assertEquals(5, $p2->fresh()->stock);
    }

    public function test_empty_cart_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);

        app(CreateTransaction::class)->handle(
            cashier: $this->cashier,
            items: [],
            paid: 0,
        );
    }
}
