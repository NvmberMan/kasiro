<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyStoresTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_my_stores_shows_active_tenants_user_is_member_of(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Toko Aktif']);
        $tenant->users()->attach($this->user, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($this->user)
            ->get('http://kasiro.com/my-stores')
            ->assertOk()
            ->assertSee('Toko Aktif');
    }

    public function test_my_stores_does_not_show_archived_tenants(): void
    {
        $archived = Tenant::factory()->archived()->create(['name' => 'Toko Arsip']);
        $archived->users()->attach($this->user, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($this->user)
            ->get('http://kasiro.com/my-stores')
            ->assertOk()
            ->assertDontSee('Toko Arsip');
    }

    public function test_my_stores_does_not_show_other_users_tenants(): void
    {
        $other = User::factory()->create();
        $tenant = Tenant::factory()->create(['name' => 'Toko Orang Lain']);
        $tenant->users()->attach($other, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($this->user)
            ->get('http://kasiro.com/my-stores')
            ->assertOk()
            ->assertDontSee('Toko Orang Lain');
    }

    public function test_my_stores_shows_archive_button_for_owner(): void
    {
        $tenant = Tenant::factory()->create();
        $tenant->users()->attach($this->user, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($this->user)
            ->get('http://kasiro.com/my-stores')
            ->assertOk()
            ->assertSee('Arsipkan');
    }

    public function test_my_stores_does_not_show_archive_button_for_manager(): void
    {
        $tenant = Tenant::factory()->create();
        $tenant->users()->attach($this->user, ['role' => 'manager', 'status' => 'active']);

        $this->actingAs($this->user)
            ->get('http://kasiro.com/my-stores')
            ->assertOk()
            ->assertDontSee('Arsipkan');
    }

    public function test_my_stores_shows_role_badge(): void
    {
        $tenant = Tenant::factory()->create();
        $tenant->users()->attach($this->user, ['role' => 'cashier', 'status' => 'active']);

        $this->actingAs($this->user)
            ->get('http://kasiro.com/my-stores')
            ->assertOk()
            ->assertSee('Kasir');
    }

    public function test_my_stores_shows_empty_state_when_no_tenants(): void
    {
        $this->actingAs($this->user)
            ->get('http://kasiro.com/my-stores')
            ->assertOk()
            ->assertSee('Anda belum memiliki');
    }
}
