<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_apex_host_serves_the_platform(): void
    {
        $this->get('http://kasiro.my.id/')
            ->assertOk()
            ->assertSee('Kasiro');
    }

    public function test_active_subdomain_resolves_to_its_tenant(): void
    {
        $tenant = Tenant::factory()->subdomain('warungbudi')->create(['name' => 'Warung Budi']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($user)
            ->get('http://warungbudi.kasiro.my.id/')
            ->assertOk()
            ->assertSee('Warung Budi');
    }

    public function test_unknown_subdomain_returns_404(): void
    {
        $this->get('http://nonexistent.kasiro.my.id/')
            ->assertNotFound();
    }

    public function test_reserved_subdomain_returns_404(): void
    {
        $this->get('http://admin.kasiro.my.id/')
            ->assertNotFound();
    }

    public function test_archived_tenant_shows_inactive_page(): void
    {
        Tenant::factory()->archived()->subdomain('lawasstore')->create(['name' => 'Lawas Store']);

        $response = $this->get('http://lawasstore.kasiro.my.id/');

        $response->assertStatus(403);
        $response->assertSee('tidak aktif');
        $response->assertSee('Lawas Store');
    }
}
