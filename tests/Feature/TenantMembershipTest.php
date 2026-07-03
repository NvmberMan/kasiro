<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantMembershipTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->subdomain('warungbudi')->create(['name' => 'Warung Budi']);
    }

    public function test_non_member_is_rejected_with_403(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('http://warungbudi.kasiro.my.id/')
            ->assertForbidden();
    }

    public function test_active_member_can_access_tenant_route(): void
    {
        $user = User::factory()->create();
        $this->tenant->users()->attach($user, ['role' => 'cashier', 'status' => 'active']);

        $this->actingAs($user)
            ->get('http://warungbudi.kasiro.my.id/')
            ->assertOk();
    }

    public function test_revoked_member_is_rejected_on_next_request(): void
    {
        $user = User::factory()->create();
        $this->tenant->users()->attach($user, ['role' => 'cashier', 'status' => 'revoked']);

        $this->actingAs($user)
            ->get('http://warungbudi.kasiro.my.id/')
            ->assertForbidden();
    }

    public function test_revoke_takes_effect_immediately_without_re_login(): void
    {
        $user = User::factory()->create();
        $this->tenant->users()->attach($user, ['role' => 'owner', 'status' => 'active']);

        // First request succeeds
        $this->actingAs($user)
            ->get('http://warungbudi.kasiro.my.id/')
            ->assertOk();

        // Revoke membership
        TenantUser::where('tenant_id', $this->tenant->id)
            ->where('user_id', $user->id)
            ->update(['status' => 'revoked']);

        // Next request is blocked without re-login (E4 — immediate effect)
        $this->actingAs($user)
            ->get('http://warungbudi.kasiro.my.id/')
            ->assertForbidden();
    }

    public function test_unauthenticated_user_is_redirected_to_apex_login(): void
    {
        $this->get('http://warungbudi.kasiro.my.id/')
            ->assertRedirect('http://kasiro.my.id/login');
    }
}
