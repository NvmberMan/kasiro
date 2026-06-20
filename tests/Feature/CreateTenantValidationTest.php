<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTenantValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // --- Guest redirects ---

    public function test_guest_is_redirected_from_choose_flow(): void
    {
        $this->get('http://kasiro.com/tenants/create')
            ->assertRedirect();
    }

    public function test_guest_is_redirected_from_custom_create(): void
    {
        $this->get('http://kasiro.com/tenants/create/custom')
            ->assertRedirect();
    }

    public function test_guest_post_to_custom_store_redirects_to_login(): void
    {
        $this->post('http://kasiro.com/tenants/create/custom', [
            'name'          => 'X',
            'subdomain'     => 'x',
            'layout'        => 'modern',
            'theme'         => 'light',
            'color_palette' => 'default',
        ])->assertRedirect();
    }

    // --- Subdomain validation ---

    public function test_reserved_subdomain_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', [
                'name'          => 'Admin Shop',
                'subdomain'     => 'admin',
                'layout'        => 'modern',
                'theme'         => 'light',
                'color_palette' => 'default',
            ])
            ->assertSessionHasErrors('subdomain');
    }

    public function test_duplicate_subdomain_is_rejected(): void
    {
        Tenant::factory()->subdomain('taken')->create();

        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', [
                'name'          => 'Another',
                'subdomain'     => 'taken',
                'layout'        => 'modern',
                'theme'         => 'light',
                'color_palette' => 'default',
            ])
            ->assertSessionHasErrors('subdomain');
    }

    public function test_subdomain_with_uppercase_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', [
                'name'          => 'Bad',
                'subdomain'     => 'MyShop',
                'layout'        => 'modern',
                'theme'         => 'light',
                'color_palette' => 'default',
            ])
            ->assertSessionHasErrors('subdomain');
    }

    public function test_subdomain_starting_with_hyphen_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', [
                'name'          => 'Bad',
                'subdomain'     => '-badstart',
                'layout'        => 'modern',
                'theme'         => 'light',
                'color_palette' => 'default',
            ])
            ->assertSessionHasErrors('subdomain');
    }

    // --- Whitelist validation ---

    public function test_invalid_layout_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', [
                'name'          => 'Shop',
                'subdomain'     => 'myshop',
                'layout'        => 'hacker-layout',
                'theme'         => 'light',
                'color_palette' => 'default',
            ])
            ->assertSessionHasErrors('layout');
    }

    public function test_invalid_palette_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', [
                'name'          => 'Shop',
                'subdomain'     => 'myshop',
                'layout'        => 'modern',
                'theme'         => 'light',
                'color_palette' => 'rainbow-unicorn',
            ])
            ->assertSessionHasErrors('color_palette');
    }

    public function test_missing_template_id_is_rejected_on_template_flow(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/template', [
                'name'      => 'Shop',
                'subdomain' => 'myshop',
            ])
            ->assertSessionHasErrors('template_id');
    }

    public function test_nonexistent_template_id_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/template', [
                'name'        => 'Shop',
                'subdomain'   => 'myshop',
                'template_id' => 99999,
            ])
            ->assertSessionHasErrors('template_id');
    }

    public function test_name_is_required(): void
    {
        $template = Template::factory()->published()->create();

        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/template', [
                'subdomain'   => 'myshop',
                'template_id' => $template->id,
            ])
            ->assertSessionHasErrors('name');
    }
}
