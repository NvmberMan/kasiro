<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_with_own_locale_overrides_the_studio_language(): void
    {
        $owner = User::factory()->create(['locale' => 'id']);
        $tenant = Tenant::factory()->create([
            'owner_id' => $owner->id,
            'locale'   => 'en',
        ]);
        $tenant->users()->attach($owner, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($owner)
            ->get("http://{$tenant->subdomain}.kasiro.my.id/")
            ->assertOk()
            ->assertSee('Home')
            ->assertDontSee('Beranda');
    }

    public function test_tenant_without_own_locale_follows_the_owners_studio_language(): void
    {
        $owner = User::factory()->create(['locale' => 'en']);
        $tenant = Tenant::factory()->create([
            'owner_id' => $owner->id,
            'locale'   => null,
        ]);
        $tenant->users()->attach($owner, ['role' => 'owner', 'status' => 'active']);

        $this->actingAs($owner)
            ->get("http://{$tenant->subdomain}.kasiro.my.id/")
            ->assertOk()
            ->assertSee('Home')
            ->assertDontSee('Beranda');
    }

    public function test_studio_locale_is_resolved_on_central_domain(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)
            ->get('http://kasiro.my.id/dashboard')
            ->assertOk()
            ->assertSee('Home')
            ->assertDontSee('Beranda');
    }
}
