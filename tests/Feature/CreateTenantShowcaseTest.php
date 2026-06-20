<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTenantShowcaseTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Template $published;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user      = User::factory()->create();
        $this->published = Template::factory()->published()->create([
            'default_config' => [
                'layout'        => 'retro',
                'theme'         => 'dark',
                'color_palette' => 'amber',
            ],
        ]);
    }

    public function test_showcase_page_lists_published_templates(): void
    {
        $unpublished = Template::factory()->unpublished()->create();

        $this->actingAs($this->user)
            ->get('http://kasiro.com/tenants/showcase')
            ->assertOk()
            ->assertSee($this->published->name)
            ->assertDontSee($unpublished->name);
    }

    public function test_showcase_quick_create_stores_tenant_with_fixed_template(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/showcase', [
                'name'        => 'Kopi Nusantara',
                'subdomain'   => 'kopinusantara',
                'template_id' => $this->published->id,
            ]);

        $tenant = Tenant::where('subdomain', 'kopinusantara')->firstOrFail();

        $this->assertSame($this->published->id, $tenant->template_id);
        $this->assertSame('retro', $tenant->theme_config['layout']);
        $this->assertSame('dark', $tenant->theme_config['theme']);
        $this->assertSame('amber', $tenant->theme_config['color_palette']);
    }

    public function test_showcase_quick_create_does_not_require_branding_fields(): void
    {
        // Showcase flow only needs name + subdomain + template_id (no layout/theme/palette).
        $response = $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/showcase', [
                'name'        => 'Kopi Nusantara',
                'subdomain'   => 'kopinusantara2',
                'template_id' => $this->published->id,
            ]);

        $response->assertRedirect('http://kopinusantara2.kasiro.com/');
    }

    public function test_showcase_quick_create_creates_owner_membership(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/showcase', [
                'name'        => 'Kopi Nusantara',
                'subdomain'   => 'kopinusantara3',
                'template_id' => $this->published->id,
            ]);

        $tenant = Tenant::where('subdomain', 'kopinusantara3')->firstOrFail();

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $tenant->id,
            'user_id'   => $this->user->id,
            'role'      => 'owner',
            'status'    => 'active',
        ]);
    }

    public function test_showcase_rejects_unpublished_template(): void
    {
        $unpublished = Template::factory()->unpublished()->create();

        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/showcase', [
                'name'        => 'Bad',
                'subdomain'   => 'badshop',
                'template_id' => $unpublished->id,
            ])
            ->assertStatus(404);
    }
}
