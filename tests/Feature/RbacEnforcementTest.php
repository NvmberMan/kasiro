<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RbacEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $owner;

    private User $manager;

    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->subdomain('warungbudi')->create();
        $this->owner = User::factory()->create();
        $this->manager = User::factory()->create();
        $this->cashier = User::factory()->create();

        $this->tenant->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);
        $this->tenant->users()->attach($this->manager, ['role' => 'manager', 'status' => 'active']);
        $this->tenant->users()->attach($this->cashier, ['role' => 'cashier', 'status' => 'active']);

        app(TenantContext::class)->set($this->tenant);
    }

    protected function tearDown(): void
    {
        app(TenantContext::class)->forget();
        parent::tearDown();
    }

    public function test_owner_has_all_permissions(): void
    {
        $this->actingAs($this->owner);

        $this->assertTrue(Gate::allows('access-pos'));
        $this->assertTrue(Gate::allows('manage-products'));
        $this->assertTrue(Gate::allows('manage-categories'));
        $this->assertTrue(Gate::allows('view-reports'));
        $this->assertTrue(Gate::allows('manage-staff'));
        $this->assertTrue(Gate::allows('manage-tenant-settings'));
        $this->assertTrue(Gate::allows('manage-billing'));
    }

    public function test_manager_has_operational_but_not_owner_permissions(): void
    {
        $this->actingAs($this->manager);

        $this->assertTrue(Gate::allows('access-pos'));
        $this->assertTrue(Gate::allows('manage-products'));
        $this->assertTrue(Gate::allows('manage-categories'));
        $this->assertTrue(Gate::allows('view-reports'));

        $this->assertFalse(Gate::allows('manage-staff'));
        $this->assertFalse(Gate::allows('manage-tenant-settings'));
        $this->assertFalse(Gate::allows('manage-billing'));
    }

    public function test_cashier_can_only_access_pos(): void
    {
        $this->actingAs($this->cashier);

        $this->assertTrue(Gate::allows('access-pos'));

        $this->assertFalse(Gate::allows('manage-products'));
        $this->assertFalse(Gate::allows('manage-categories'));
        $this->assertFalse(Gate::allows('view-reports'));
        $this->assertFalse(Gate::allows('manage-staff'));
        $this->assertFalse(Gate::allows('manage-tenant-settings'));
        $this->assertFalse(Gate::allows('manage-billing'));
    }

    public function test_non_member_is_denied_all_permissions(): void
    {
        $outsider = User::factory()->create();
        $this->actingAs($outsider);

        $this->assertFalse(Gate::allows('access-pos'));
        $this->assertFalse(Gate::allows('manage-products'));
        $this->assertFalse(Gate::allows('manage-staff'));
    }

    public function test_tenant_policy_allows_owner_to_update_settings(): void
    {
        $this->actingAs($this->owner);

        $this->assertTrue($this->owner->can('update', $this->tenant));
        $this->assertTrue($this->owner->can('manageStaff', $this->tenant));
        $this->assertTrue($this->owner->can('manageBilling', $this->tenant));
    }

    public function test_tenant_policy_denies_manager_owner_only_actions(): void
    {
        $this->actingAs($this->manager);

        $this->assertFalse($this->manager->can('update', $this->tenant));
        $this->assertFalse($this->manager->can('manageStaff', $this->tenant));
        $this->assertFalse($this->manager->can('manageBilling', $this->tenant));
    }

    public function test_smoke_route_renders_tenant_layout_for_owner(): void
    {
        $this->actingAs($this->owner)
            ->get('http://warungbudi.kasiro.my.id/')
            ->assertOk()
            ->assertSee($this->tenant->name)
            ->assertSee('owner')
            ->assertSee('--brand-primary', false);
    }

    public function test_smoke_route_renders_tenant_layout_for_cashier(): void
    {
        $this->actingAs($this->cashier)
            ->get('http://warungbudi.kasiro.my.id/')
            ->assertOk()
            ->assertSee($this->tenant->name)
            ->assertSee('cashier')
            ->assertSee('--brand-primary', false);
    }

    public function test_bottombar_layout_renders(): void
    {
        $tenant = Tenant::factory()->subdomain('bottomshop')->create([
            'theme_config' => ['layout' => 'bottombar', 'theme' => 'modern', 'color_palette' => 'violet'],
        ]);
        $tenant->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);
        app(TenantContext::class)->set($tenant);

        $this->actingAs($this->owner)
            ->get('http://bottomshop.kasiro.my.id/')
            ->assertOk()
            ->assertSee($tenant->name)
            ->assertSee('owner')
            ->assertSee('--brand-primary', false);
    }
}
