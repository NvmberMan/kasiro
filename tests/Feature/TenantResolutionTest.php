<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_apex_host_serves_the_platform(): void
    {
        $this->get('http://kasiro.com/')
            ->assertOk()
            ->assertSee('Kasiro platform');
    }

    public function test_active_subdomain_resolves_to_its_tenant(): void
    {
        Tenant::factory()->subdomain('warungbudi')->create(['name' => 'Warung Budi']);

        $this->get('http://warungbudi.kasiro.com/')
            ->assertOk()
            ->assertJson([
                'tenant' => 'Warung Budi',
                'subdomain' => 'warungbudi',
            ]);
    }

    public function test_unknown_subdomain_returns_404(): void
    {
        $this->get('http://nonexistent.kasiro.com/')
            ->assertNotFound();
    }

    public function test_reserved_subdomain_returns_404(): void
    {
        $this->get('http://admin.kasiro.com/')
            ->assertNotFound();
    }

    public function test_archived_tenant_shows_inactive_page(): void
    {
        Tenant::factory()->archived()->subdomain('lawasstore')->create(['name' => 'Lawas Store']);

        $response = $this->get('http://lawasstore.kasiro.com/');

        $response->assertStatus(403);
        $response->assertSee('tidak aktif');
        $response->assertSee('Lawas Store');
    }
}
