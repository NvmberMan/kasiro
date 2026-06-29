<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\PreviewController;
use App\Jobs\GenerateTemplateScreenshot;
use App\Models\Category;
use App\Models\Product;
use App\Models\Template;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TemplateScreenshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_screenshot_url_is_null_when_not_generated(): void
    {
        $template = Template::factory()->create(['screenshot_path' => null]);

        $this->assertNull($template->screenshotUrl());
    }

    public function test_screenshot_url_points_to_stored_file_with_cache_buster(): void
    {
        $template = Template::factory()->create([
            'screenshot_path' => 'screenshots/template-demo.jpg',
        ]);

        $url = $template->screenshotUrl();

        $this->assertNotNull($url);
        $this->assertStringContainsString('storage/screenshots/template-demo.jpg', $url);
        $this->assertStringContainsString('?v=', $url);
    }

    public function test_template_preview_rejects_missing_token(): void
    {
        $donor = $this->donorTenant();
        $template = Template::factory()->published()->create();

        $this->get("http://{$donor->subdomain}.kasiro.com/__template-preview/{$template->id}")
            ->assertForbidden();
    }

    public function test_template_preview_renders_with_valid_token(): void
    {
        $donor = $this->donorTenant();
        $template = Template::factory()->published()->create([
            'name' => 'Tema Restoran',
            'default_config' => [
                'layout' => 'bottombar',
                'theme' => 'retro',
                'color_palette' => 'rose',
            ],
        ]);

        $token = 'valid-token';
        Cache::put(PreviewController::templateCacheKey($template->id), $token, now()->addMinutes(10));

        $this->get("http://{$donor->subdomain}.kasiro.com/__template-preview/{$template->id}?token={$token}")
            ->assertOk()
            // The donor's branding is overlaid with the template's name.
            ->assertSee('Tema Restoran');
    }

    public function test_command_queues_a_job_per_published_template(): void
    {
        Queue::fake();
        $this->donorTenant();

        Template::factory()->published()->count(2)->create();
        Template::factory()->unpublished()->create();

        $this->artisan('templates:screenshots', ['--queue' => true])
            ->assertSuccessful();

        Queue::assertPushed(GenerateTemplateScreenshot::class, 2);
    }

    /**
     * An active tenant with an owner and a minimal catalog, usable as a preview donor.
     */
    private function donorTenant(): Tenant
    {
        $owner = User::factory()->create();
        $tenant = Tenant::factory()->subdomain('donortoko')->create(['owner_id' => $owner->id]);
        $tenant->users()->attach($owner, ['role' => 'owner', 'status' => 'active']);

        $category = Category::factory()->create(['tenant_id' => $tenant->id]);
        Product::factory()->create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        return $tenant;
    }
}
