<?php

namespace Tests\Feature\Storage;

use App\Models\Template;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LogoUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->user = User::factory()->create();
    }

    public function test_logo_is_stored_and_path_persisted(): void
    {
        $logo = UploadedFile::fake()->image('logo.png', 100, 100);

        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', [
                'name'          => 'Logo Shop',
                'subdomain'     => 'logoshop',
                'layout'        => 'modern',
                'theme'         => 'light',
                'color_palette' => 'default',
                'logo'          => $logo,
            ]);

        $tenant = Tenant::where('subdomain', 'logoshop')->firstOrFail();

        $this->assertNotNull($tenant->logo_path);
        Storage::disk('public')->assertExists($tenant->logo_path);
    }

    public function test_tenant_without_logo_has_null_logo_path(): void
    {
        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', [
                'name'          => 'No Logo',
                'subdomain'     => 'nologo',
                'layout'        => 'modern',
                'theme'         => 'light',
                'color_palette' => 'default',
            ]);

        $tenant = Tenant::where('subdomain', 'nologo')->firstOrFail();
        $this->assertNull($tenant->logo_path);
    }

    public function test_non_image_file_is_rejected(): void
    {
        $pdf = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/custom', [
                'name'          => 'Bad Logo',
                'subdomain'     => 'badlogo',
                'layout'        => 'modern',
                'theme'         => 'light',
                'color_palette' => 'default',
                'logo'          => $pdf,
            ])
            ->assertSessionHasErrors('logo');

        $this->assertDatabaseMissing('tenants', ['subdomain' => 'badlogo']);
    }

    public function test_logo_stored_via_template_flow(): void
    {
        $template = Template::factory()->published()->create();
        $logo     = UploadedFile::fake()->image('brand.jpg', 200, 200);

        $this->actingAs($this->user)
            ->post('http://kasiro.com/tenants/create/template', [
                'name'        => 'Template Logo',
                'subdomain'   => 'templatelogo',
                'template_id' => $template->id,
                'logo'        => $logo,
            ]);

        $tenant = Tenant::where('subdomain', 'templatelogo')->firstOrFail();

        $this->assertNotNull($tenant->logo_path);
        Storage::disk('public')->assertExists($tenant->logo_path);
    }
}
