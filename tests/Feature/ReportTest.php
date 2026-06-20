<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $owner;
    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner   = User::factory()->create();
        $this->tenant  = Tenant::factory()->create(['owner_id' => $this->owner->id]);
        $this->tenant->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);

        $this->cashier = User::factory()->create();
        $this->tenant->users()->attach($this->cashier, ['role' => 'cashier', 'status' => 'active']);
    }

    private function url(string $path): string
    {
        return "http://{$this->tenant->subdomain}.kasiro.com{$path}";
    }

    public function test_owner_can_view_reports(): void
    {
        $this->actingAs($this->owner)
            ->get($this->url('/reports'))
            ->assertOk()
            ->assertSee('Laporan Penjualan');
    }

    public function test_cashier_cannot_view_reports(): void
    {
        $this->actingAs($this->cashier)
            ->get($this->url('/reports'))
            ->assertForbidden();
    }

    public function test_today_summary_counts_todays_transactions(): void
    {
        Transaction::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'cashier_id'     => $this->cashier->id,
            'total'          => 50000,
            'paid'           => 50000,
            'transacted_at'  => now(),
        ]);

        $this->actingAs($this->owner)
            ->get($this->url('/reports'))
            ->assertOk()
            ->assertSee('50.000');
    }

    public function test_daily_sales_shows_last_30_days(): void
    {
        Transaction::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'cashier_id'    => $this->cashier->id,
            'total'         => 25000,
            'paid'          => 25000,
            'transacted_at' => now()->subDays(5),
        ]);

        $this->actingAs($this->owner)
            ->get($this->url('/reports'))
            ->assertOk()
            ->assertSee('25.000');
    }

    public function test_old_transactions_not_in_daily_sales(): void
    {
        Transaction::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'cashier_id'    => $this->cashier->id,
            'total'         => 99999,
            'paid'          => 99999,
            'transacted_at' => now()->subDays(40),
        ]);

        $this->actingAs($this->owner)
            ->get($this->url('/reports'))
            ->assertOk()
            ->assertDontSee('99.999');
    }

    public function test_top_products_shows_most_sold(): void
    {
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Kopi Susu']);

        $tx = Transaction::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'cashier_id'    => $this->cashier->id,
            'total'         => 30000,
            'paid'          => 30000,
            'transacted_at' => now(),
        ]);

        TransactionItem::create([
            'tenant_id'      => $this->tenant->id,
            'transaction_id' => $tx->id,
            'product_id'     => $product->id,
            'qty'            => 3,
            'unit_price'     => 10000,
            'subtotal'       => 30000,
        ]);

        $this->actingAs($this->owner)
            ->get($this->url('/reports'))
            ->assertOk()
            ->assertSee('Kopi Susu');
    }

    public function test_reports_only_show_this_tenants_data(): void
    {
        $other = Tenant::factory()->create(['owner_id' => $this->owner->id]);

        Transaction::factory()->create([
            'tenant_id'     => $other->id,
            'cashier_id'    => $this->cashier->id,
            'total'         => 777777,
            'paid'          => 777777,
            'transacted_at' => now(),
        ]);

        $this->actingAs($this->owner)
            ->get($this->url('/reports'))
            ->assertOk()
            ->assertDontSee('777.777');
    }
}
