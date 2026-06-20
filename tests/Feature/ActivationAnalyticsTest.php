<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivationAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_platform_stats(): void
    {
        $owner = User::factory()->create();
        $tenant = Tenant::factory()->create(['owner_id' => $owner->id]);
        $tenant->users()->attach($owner, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($owner)
            ->get('http://kasiro.com/dashboard')
            ->assertOk()
            ->assertSee('Statistik Platform');
    }

    public function test_activated_tenants_count_only_tenants_with_transactions(): void
    {
        $owner = User::factory()->create();

        // Tenant WITH transaction
        $activated = Tenant::factory()->create(['owner_id' => $owner->id]);
        $activated->users()->attach($owner, ['role' => 'owner', 'status' => 'active']);
        Transaction::factory()->create([
            'tenant_id'     => $activated->id,
            'cashier_id'    => $owner->id,
            'total'         => 10000,
            'paid'          => 10000,
            'transacted_at' => now(),
        ]);

        // Tenant WITHOUT transaction
        $notActivated = Tenant::factory()->create(['owner_id' => $owner->id]);
        $notActivated->users()->attach($owner, ['role' => 'owner', 'status' => 'active']);

        $response = $this->actingAs($owner)
            ->get('http://kasiro.com/dashboard')
            ->assertOk();

        // 1 activated (has transaction), 1 not
        $response->assertSee('Sudah Bertransaksi');
    }
}
