<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTenantCustomTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Warung Budi',
            'subdomain' => 'warungbudi',
            'layout' => 'topbar',
            'theme' => 'modern',
            'color_palette' => 'violet',
        ], $overrides);
    }

    public function test_authenticated_user_can_access_custom_create_form(): void
    {
        $this->actingAs($this->user)
            ->get('http://kasiro.com/tenants/create/custom')
            ->assertOk()
            ->assertSee('layout')
            ->assertSee('color_palette');
    }

    public function test_custom_flow_creates_tenant_with_correct_theme_config(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', $this->validPayload());

        $tenant = Tenant::where('subdomain', 'warungbudi')->firstOrFail();

        $this->assertSame('Warung Budi', $tenant->name);
        $this->assertSame('topbar', $tenant->theme_config['layout']);
        $this->assertSame('modern', $tenant->theme_config['theme']);
        $this->assertSame('violet', $tenant->theme_config['color_palette']);
        $this->assertNull($tenant->template_id);
    }

    public function test_custom_flow_sets_owner_id(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', $this->validPayload());

        $tenant = Tenant::where('subdomain', 'warungbudi')->firstOrFail();

        $this->assertSame($this->user->id, $tenant->owner_id);
    }

    public function test_custom_flow_creates_owner_membership(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', $this->validPayload());

        $tenant = Tenant::where('subdomain', 'warungbudi')->firstOrFail();

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $tenant->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
            'status' => 'active',
        ]);
    }

    public function test_custom_flow_redirects_to_created_page(): void
    {
        $response = $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', $this->validPayload());

        $tenant = Tenant::where('subdomain', 'warungbudi')->firstOrFail();
        $response->assertRedirect(route('tenants.created', $tenant));
    }

    public function test_tenant_status_is_active_after_create(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', $this->validPayload());

        $this->assertDatabaseHas('tenants', [
            'subdomain' => 'warungbudi',
            'status' => 'active',
        ]);
    }
}
