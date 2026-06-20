<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantSettingsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $owner;
    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner  = User::factory()->create();
        $this->tenant = Tenant::factory()->create([
            'owner_id'     => $this->owner->id,
            'name'         => 'Toko Lama',
            'theme_config' => ['layout' => 'topbar', 'theme' => 'modern', 'color_palette' => 'violet'],
        ]);
        $this->tenant->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);

        $this->cashier = User::factory()->create();
        $this->tenant->users()->attach($this->cashier, ['role' => 'cashier', 'status' => 'active']);
    }

    private function url(string $path): string
    {
        return "http://{$this->tenant->subdomain}.kasiro.com{$path}";
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'          => 'Toko Baru',
            'subdomain'     => $this->tenant->subdomain,
            'layout'        => 'topbar',
            'theme'         => 'modern',
            'color_palette' => 'violet',
        ], $overrides);
    }

    public function test_owner_can_view_settings_page(): void
    {
        $this->actingAs($this->owner)
            ->get($this->url('/settings'))
            ->assertOk()
            ->assertSee('Pengaturan Toko');
    }

    public function test_cashier_cannot_view_settings_page(): void
    {
        $this->actingAs($this->cashier)
            ->get($this->url('/settings'))
            ->assertForbidden();
    }

    public function test_owner_can_update_tenant_name(): void
    {
        $this->actingAs($this->owner)
            ->put($this->url('/settings'), $this->validPayload(['name' => 'Nama Baru']))
            ->assertRedirect();

        $this->assertEquals('Nama Baru', $this->tenant->fresh()->name);
    }

    public function test_owner_can_change_color_palette(): void
    {
        $this->actingAs($this->owner)
            ->put($this->url('/settings'), $this->validPayload(['color_palette' => 'sky']))
            ->assertRedirect();

        $this->assertEquals('sky', $this->tenant->fresh()->theme_config['color_palette']);
    }

    public function test_owner_can_change_layout(): void
    {
        $this->actingAs($this->owner)
            ->put($this->url('/settings'), $this->validPayload(['layout' => 'sidebar']))
            ->assertRedirect();

        $this->assertEquals('sidebar', $this->tenant->fresh()->theme_config['layout']);
    }

    public function test_owner_can_change_theme(): void
    {
        $this->actingAs($this->owner)
            ->put($this->url('/settings'), $this->validPayload(['theme' => 'classic', 'color_palette' => 'slate']))
            ->assertRedirect();

        $config = $this->tenant->fresh()->theme_config;
        $this->assertEquals('classic', $config['theme']);
        $this->assertEquals('slate', $config['color_palette']);
    }

    public function test_palette_invalid_for_theme_falls_back_to_themes_first_palette(): void
    {
        // 'sky' is a modern palette, not valid for 'classic'
        $this->actingAs($this->owner)
            ->put($this->url('/settings'), $this->validPayload(['theme' => 'classic', 'color_palette' => 'sky']))
            ->assertRedirect();

        // ThemeConfig corrects 'sky' → first classic palette ('slate')
        $this->assertEquals('slate', $this->tenant->fresh()->theme_config['color_palette']);
    }

    public function test_owner_can_upload_logo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->owner)
            ->put($this->url('/settings'), array_merge(
                $this->validPayload(),
                ['logo' => UploadedFile::fake()->image('logo.png', 100, 100)]
            ))
            ->assertRedirect();

        $this->assertNotNull($this->tenant->fresh()->logo_path);
        Storage::disk('public')->assertExists($this->tenant->fresh()->logo_path);
    }

    public function test_old_logo_is_deleted_when_new_one_uploaded(): void
    {
        Storage::fake('public');

        $old = UploadedFile::fake()->image('old.png')->store('logos', 'public');
        $this->tenant->update(['logo_path' => $old]);

        $this->actingAs($this->owner)
            ->put($this->url('/settings'), array_merge(
                $this->validPayload(),
                ['logo' => UploadedFile::fake()->image('new.png', 100, 100)]
            ));

        Storage::disk('public')->assertMissing($old);
        $this->assertNotEquals($old, $this->tenant->fresh()->logo_path);
    }

    public function test_subdomain_change_redirects_to_new_url(): void
    {
        $newSub = 'newsubdomain' . rand(100, 999);

        $response = $this->actingAs($this->owner)
            ->put($this->url('/settings'), $this->validPayload(['subdomain' => $newSub]));

        $response->assertRedirect();
        $this->assertStringContainsString($newSub, $response->headers->get('Location'));
        $this->assertEquals($newSub, $this->tenant->fresh()->subdomain);
    }

    public function test_invalid_palette_is_rejected(): void
    {
        $this->actingAs($this->owner)
            ->put($this->url('/settings'), $this->validPayload(['color_palette' => 'invalid-palette']))
            ->assertSessionHasErrors('color_palette');
    }

    public function test_invalid_layout_is_rejected(): void
    {
        $this->actingAs($this->owner)
            ->put($this->url('/settings'), $this->validPayload(['layout' => 'hacked']))
            ->assertSessionHasErrors('layout');
    }

    public function test_invalid_theme_is_rejected(): void
    {
        $this->actingAs($this->owner)
            ->put($this->url('/settings'), $this->validPayload(['theme' => 'dark']))
            ->assertSessionHasErrors('theme');
    }

    public function test_duplicate_subdomain_is_rejected(): void
    {
        $other = Tenant::factory()->create();

        $this->actingAs($this->owner)
            ->put($this->url('/settings'), $this->validPayload(['subdomain' => $other->subdomain]))
            ->assertSessionHasErrors('subdomain');
    }

    public function test_cashier_cannot_update_settings(): void
    {
        $this->actingAs($this->cashier)
            ->put($this->url('/settings'), $this->validPayload())
            ->assertForbidden();
    }
}
