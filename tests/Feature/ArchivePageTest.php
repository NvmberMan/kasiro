<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchivePageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_archive_page_shows_owned_archived_tenants(): void
    {
        $tenant = Tenant::factory()->archived()->create([
            'name'     => 'Toko Lama',
            'owner_id' => $this->user->id,
        ]);
        $tenant->users()->attach($this->user, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($this->user)
            ->get('http://kasiro.my.id/archive')
            ->assertOk()
            ->assertSee('Toko Lama');
    }

    public function test_archive_page_does_not_show_active_tenants(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Toko Aktif']);
        $tenant->users()->attach($this->user, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($this->user)
            ->get('http://kasiro.my.id/archive')
            ->assertOk()
            ->assertDontSee('Toko Aktif');
    }

    public function test_archive_page_does_not_show_tenant_archived_by_another_owner(): void
    {
        $other = User::factory()->create();
        $tenant = Tenant::factory()->archived()->create([
            'name'     => 'Toko Milik Orang Lain',
            'owner_id' => $other->id,
        ]);
        $tenant->users()->attach($this->user, ['role' => 'manager', 'status' => 'active']);

        $this->actingAs($this->user)
            ->get('http://kasiro.my.id/archive')
            ->assertOk()
            ->assertDontSee('Toko Milik Orang Lain');
    }

    public function test_archive_page_shows_restore_button(): void
    {
        Tenant::factory()->archived()->create([
            'name'     => 'Toko Arsip',
            'owner_id' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->get('http://kasiro.my.id/archive')
            ->assertOk()
            ->assertSee('Pulihkan');
    }

    public function test_archive_page_shows_empty_state_when_no_archived_tenants(): void
    {
        $this->actingAs($this->user)
            ->get('http://kasiro.my.id/archive')
            ->assertOk()
            ->assertSee('Tidak ada toko yang diarsipkan');
    }
}
