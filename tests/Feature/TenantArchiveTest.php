<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantArchiveTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner  = User::factory()->create();
        $this->tenant = Tenant::factory()->create(['owner_id' => $this->owner->id]);
        $this->tenant->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);
    }

    // --- Archive ---

    public function test_owner_can_archive_active_tenant(): void
    {
        $this->actingAs($this->owner)
            ->post("http://kasiro.com/tenants/{$this->tenant->id}/archive")
            ->assertRedirect(route('my-stores'));

        $this->tenant->refresh();
        $this->assertTrue($this->tenant->isArchived());
        $this->assertNotNull($this->tenant->archived_at);
    }

    public function test_archive_preserves_tenant_user_memberships(): void
    {
        $member = User::factory()->create();
        $this->tenant->users()->attach($member, ['role' => 'cashier', 'status' => 'active']);

        $this->actingAs($this->owner)
            ->post("http://kasiro.com/tenants/{$this->tenant->id}/archive");

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $this->tenant->id,
            'user_id'   => $member->id,
            'role'      => 'cashier',
        ]);
    }

    public function test_manager_cannot_archive_tenant(): void
    {
        $manager = User::factory()->create();
        $this->tenant->users()->attach($manager, ['role' => 'manager', 'status' => 'active']);

        $this->actingAs($manager)
            ->post("http://kasiro.com/tenants/{$this->tenant->id}/archive")
            ->assertForbidden();

        $this->tenant->refresh();
        $this->assertTrue($this->tenant->isActive());
    }

    public function test_cashier_cannot_archive_tenant(): void
    {
        $cashier = User::factory()->create();
        $this->tenant->users()->attach($cashier, ['role' => 'cashier', 'status' => 'active']);

        $this->actingAs($cashier)
            ->post("http://kasiro.com/tenants/{$this->tenant->id}/archive")
            ->assertForbidden();
    }

    public function test_non_member_cannot_archive_tenant(): void
    {
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->post("http://kasiro.com/tenants/{$this->tenant->id}/archive")
            ->assertForbidden();
    }

    // --- Restore ---

    public function test_owner_can_restore_archived_tenant(): void
    {
        $archived = Tenant::factory()->archived()->create(['owner_id' => $this->owner->id]);
        $archived->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($this->owner)
            ->delete("http://kasiro.com/tenants/{$archived->id}/archive")
            ->assertRedirect(route('archive'));

        $archived->refresh();
        $this->assertTrue($archived->isActive());
        $this->assertNull($archived->archived_at);
    }

    public function test_manager_cannot_restore_archived_tenant(): void
    {
        $archived = Tenant::factory()->archived()->create(['owner_id' => $this->owner->id]);
        $manager  = User::factory()->create();
        $archived->users()->attach($manager, ['role' => 'manager', 'status' => 'active']);

        $this->actingAs($manager)
            ->delete("http://kasiro.com/tenants/{$archived->id}/archive")
            ->assertForbidden();
    }

    public function test_restore_sets_archived_at_to_null(): void
    {
        $archived = Tenant::factory()->archived()->create(['owner_id' => $this->owner->id]);
        $archived->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($this->owner)
            ->delete("http://kasiro.com/tenants/{$archived->id}/archive");

        $this->assertDatabaseHas('tenants', [
            'id'          => $archived->id,
            'status'      => 'active',
            'archived_at' => null,
        ]);
    }
}
