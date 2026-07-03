<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardHomeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_beranda_is_accessible_to_authenticated_user(): void
    {
        $this->actingAs($this->user)
            ->get('http://kasiro.my.id/dashboard')
            ->assertOk()
            ->assertSee($this->user->name);
    }

    public function test_beranda_shows_active_tenant_count(): void
    {
        $tenant = Tenant::factory()->create();
        $tenant->users()->attach($this->user, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($this->user)
            ->get('http://kasiro.my.id/dashboard')
            ->assertOk()
            ->assertSee('1');
    }

    public function test_beranda_shows_archived_count_for_owned_tenants(): void
    {
        Tenant::factory()->archived()->create(['owner_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->get('http://kasiro.my.id/dashboard')
            ->assertOk()
            ->assertSee('Diarsipkan');
    }

    public function test_beranda_shows_recent_active_tenants(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Warung Spesial']);
        $tenant->users()->attach($this->user, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($this->user)
            ->get('http://kasiro.my.id/dashboard')
            ->assertOk()
            ->assertSee('Warung Spesial');
    }

    public function test_beranda_does_not_show_archived_tenants_in_recent(): void
    {
        $archived = Tenant::factory()->archived()->create(['name' => 'Toko Lama', 'owner_id' => $this->user->id]);
        $archived->users()->attach($this->user, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($this->user)
            ->get('http://kasiro.my.id/dashboard')
            ->assertOk()
            ->assertDontSee('Toko Lama');
    }

    public function test_beranda_shows_empty_state_with_create_button_when_no_tenants(): void
    {
        $this->actingAs($this->user)
            ->get('http://kasiro.my.id/dashboard')
            ->assertOk()
            ->assertSee('Buat Toko Baru');
    }

    public function test_beranda_shows_at_most_three_recent_tenants(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $t = Tenant::factory()->create(['name' => "Toko {$i}"]);
            $t->users()->attach($this->user, ['role' => 'owner', 'status' => 'active']);
        }

        $response = $this->actingAs($this->user)
            ->get('http://kasiro.my.id/dashboard')
            ->assertOk();

        // At most 3 tenant cards in the recent section
        $content = $response->getContent();
        $count = substr_count($content, 'Buka Toko');
        $this->assertLessThanOrEqual(3, $count);
    }
}
