<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_beranda(): void
    {
        $this->get('http://kasiro.my.id/dashboard')->assertRedirect();
    }

    public function test_guest_is_redirected_from_my_stores(): void
    {
        $this->get('http://kasiro.my.id/my-stores')->assertRedirect();
    }

    public function test_guest_is_redirected_from_archive_page(): void
    {
        $this->get('http://kasiro.my.id/archive')->assertRedirect();
    }

    public function test_guest_cannot_archive_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        $this->post("http://kasiro.my.id/tenants/{$tenant->id}/archive")
            ->assertRedirect();
    }

    public function test_guest_cannot_restore_tenant(): void
    {
        $tenant = Tenant::factory()->archived()->create();

        $this->delete("http://kasiro.my.id/tenants/{$tenant->id}/archive")
            ->assertRedirect();
    }
}
