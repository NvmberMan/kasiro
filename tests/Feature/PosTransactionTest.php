<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTransactionTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $cashier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create();
        $this->tenant  = Tenant::factory()->create(['owner_id' => $this->cashier->id]);
        $this->tenant->users()->attach($this->cashier, ['role' => 'cashier', 'status' => 'active']);

        $this->product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'price'     => 10000,
            'stock'     => 20,
        ]);
    }

    private function url(string $path): string
    {
        return "http://{$this->tenant->subdomain}.kasiro.com{$path}";
    }

    private function cart(int $qty = 1): string
    {
        return json_encode([['product_id' => $this->product->id, 'qty' => $qty]]);
    }

    public function test_pos_page_is_accessible_to_all_roles(): void
    {
        $this->actingAs($this->cashier)
            ->get($this->url('/pos'))
            ->assertOk()
            ->assertSee($this->product->name);
    }

    public function test_checkout_creates_transaction_record(): void
    {
        $this->actingAs($this->cashier)
            ->post($this->url('/pos/checkout'), [
                'cart' => $this->cart(2),
                'paid' => 25000,
            ])
            ->assertRedirect($this->url('/pos'));

        $this->assertDatabaseHas('transactions', [
            'tenant_id'  => $this->tenant->id,
            'cashier_id' => $this->cashier->id,
            'total'      => 20000,
            'paid'       => 25000,
            'change'     => 5000,
        ]);
    }

    public function test_checkout_creates_transaction_items(): void
    {
        $this->actingAs($this->cashier)
            ->post($this->url('/pos/checkout'), [
                'cart' => $this->cart(3),
                'paid' => 30000,
            ]);

        $tx = Transaction::latest()->first();

        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $tx->id,
            'product_id'     => $this->product->id,
            'qty'            => 3,
            'unit_price'     => 10000,
            'subtotal'       => 30000,
        ]);
    }

    public function test_checkout_decrements_stock(): void
    {
        $this->actingAs($this->cashier)
            ->post($this->url('/pos/checkout'), [
                'cart' => $this->cart(5),
                'paid' => 50000,
            ]);

        $this->assertEquals(15, $this->product->fresh()->stock);
    }

    public function test_checkout_with_empty_cart_fails(): void
    {
        $this->actingAs($this->cashier)
            ->post($this->url('/pos/checkout'), [
                'cart' => json_encode([]),
                'paid' => 10000,
            ])
            ->assertSessionHasErrors();
    }

    public function test_checkout_with_insufficient_stock_fails(): void
    {
        $this->actingAs($this->cashier)
            ->post($this->url('/pos/checkout'), [
                'cart' => $this->cart(100),
                'paid' => 1000000,
            ])
            ->assertSessionHasErrors(['cart']);

        $this->assertEquals(20, $this->product->fresh()->stock);
    }

    public function test_guest_cannot_access_pos(): void
    {
        $this->get($this->url('/pos'))->assertRedirect();
    }
}
