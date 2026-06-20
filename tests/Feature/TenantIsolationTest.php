<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\Fixtures\TenantWidget;
use Tests\TestCase;

/**
 * CRITICAL: proves automatic cross-tenant data isolation (PRD §9 NFR-5, E8).
 * Any regression here is a security incident, not a test failure.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('tenant_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->index();
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function actingAsTenant(Tenant $tenant): void
    {
        app(TenantContext::class)->set($tenant);
    }

    public function test_create_auto_fills_tenant_id_from_context(): void
    {
        $tenant = Tenant::factory()->create();
        $this->actingAsTenant($tenant);

        $widget = TenantWidget::create(['name' => 'Kopi']);

        $this->assertSame($tenant->id, $widget->tenant_id);
    }

    public function test_user_cannot_override_tenant_id_on_create(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $this->actingAsTenant($tenantA);

        // Attacker tries to plant a row into tenant B.
        $widget = TenantWidget::create(['name' => 'Sabotase', 'tenant_id' => $tenantB->id]);

        $this->assertSame($tenantA->id, $widget->tenant_id);
    }

    public function test_queries_only_see_the_active_tenants_rows(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $this->actingAsTenant($tenantA);
        TenantWidget::create(['name' => 'A-1']);
        TenantWidget::create(['name' => 'A-2']);

        $this->actingAsTenant($tenantB);
        TenantWidget::create(['name' => 'B-1']);

        // Active tenant is B.
        $this->assertSame(1, TenantWidget::count());
        $this->assertEqualsCanonicalizing(['B-1'], TenantWidget::pluck('name')->all());

        // Switch to A.
        $this->actingAsTenant($tenantA);
        $this->assertSame(2, TenantWidget::count());
        $this->assertEqualsCanonicalizing(['A-1', 'A-2'], TenantWidget::pluck('name')->all());
    }

    public function test_tenant_a_can_never_find_tenant_b_row_by_id(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $this->actingAsTenant($tenantB);
        $bWidget = TenantWidget::create(['name' => 'B-secret']);

        $this->actingAsTenant($tenantA);
        $this->assertNull(TenantWidget::find($bWidget->id));
    }

    public function test_without_tenant_scope_can_access_all_rows(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $this->actingAsTenant($tenantA);
        TenantWidget::create(['name' => 'A-1']);
        $this->actingAsTenant($tenantB);
        TenantWidget::create(['name' => 'B-1']);

        $this->assertSame(2, TenantWidget::withoutTenantScope()->count());
    }

    public function test_no_active_tenant_means_no_scope_constraint(): void
    {
        $tenantA = Tenant::factory()->create();
        $this->actingAsTenant($tenantA);
        TenantWidget::create(['name' => 'A-1']);

        // Simulate a platform/console context with no tenant resolved.
        app(TenantContext::class)->forget();

        $this->assertSame(1, TenantWidget::count());
    }
}
