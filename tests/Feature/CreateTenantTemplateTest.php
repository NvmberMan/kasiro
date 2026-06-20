<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTenantTemplateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Template $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = User::factory()->create();
        $this->template = Template::factory()->published()->create([
            'default_config' => [
                'layout'        => 'sidebar',
                'theme'         => 'classic',
                'color_palette' => 'slate',
            ],
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'        => 'Toko Mawar',
            'subdomain'   => 'tokomawar',
            'template_id' => $this->template->id,
        ], $overrides);
    }

    public function test_authenticated_user_can_access_template_create_form(): void
    {
        $this->actingAs($this->user)
            ->get('http://kasiro.com/tenants/create/template')
            ->assertOk()
            ->assertSee($this->template->name);
    }

    public function test_template_flow_snapshots_default_config_into_theme_config(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/template', $this->validPayload());

        $tenant = Tenant::where('subdomain', 'tokomawar')->firstOrFail();

        $this->assertSame('sidebar', $tenant->theme_config['layout']);
        $this->assertSame('classic', $tenant->theme_config['theme']);
        $this->assertSame('slate', $tenant->theme_config['color_palette']);
        $this->assertSame($this->template->id, $tenant->template_id);
    }

    public function test_snapshot_is_independent_from_template_changes(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/template', $this->validPayload());

        $tenant = Tenant::where('subdomain', 'tokomawar')->firstOrFail();

        // Mutate the template after tenant creation.
        $this->template->update([
            'default_config' => [
                'layout'        => 'topbar',
                'theme'         => 'retro',
                'color_palette' => 'amber',
            ],
        ]);

        $tenant->refresh();

        // Tenant's theme_config must remain unchanged (snapshot, not live ref).
        $this->assertSame('sidebar', $tenant->theme_config['layout']);
        $this->assertSame('slate', $tenant->theme_config['color_palette']);
    }

    public function test_template_flow_creates_owner_membership(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/template', $this->validPayload());

        $tenant = Tenant::where('subdomain', 'tokomawar')->firstOrFail();

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $tenant->id,
            'user_id'   => $this->user->id,
            'role'      => 'owner',
            'status'    => 'active',
        ]);
    }

    public function test_template_flow_redirects_to_tenant_subdomain(): void
    {
        $response = $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/template', $this->validPayload());

        $response->assertRedirect('http://tokomawar.kasiro.com/');
    }

    public function test_unpublished_template_cannot_be_used(): void
    {
        $unpublished = Template::factory()->unpublished()->create();

        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/template', [
                'name'        => 'Test',
                'subdomain'   => 'testshop',
                'template_id' => $unpublished->id,
            ])
            ->assertStatus(404);
    }
}
