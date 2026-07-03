<?php

namespace App\Jobs;

use App\Http\Controllers\Tenant\PreviewController;
use App\Jobs\Concerns\ResolvesBrowserBinaries;
use App\Models\Template;
use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Throwable;

/**
 * Capture a POS preview screenshot for a template. The preview is rendered on a
 * "donor" tenant's subdomain (which supplies a populated catalog) but styled with
 * the template's own branding, so the resulting image showcases the template.
 */
class GenerateTemplateScreenshot implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue, Queueable, ResolvesBrowserBinaries, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public int $uniqueFor = 120;

    public function __construct(
        public readonly Template $template,
        public readonly Tenant $donor,
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->template->id;
    }

    public function handle(): void
    {
        // No headless Chrome during automated tests (see GenerateTenantScreenshot).
        if (app()->runningUnitTests()) {
            return;
        }

        $url = $this->previewUrl();
        $filename = 'screenshots/template-'.$this->template->slug.'.jpg';
        $path = storage_path('app/public/'.$filename);

        if (! is_dir(storage_path('app/public/screenshots'))) {
            mkdir(storage_path('app/public/screenshots'), 0755, true);
        }

        $browsershot = Browsershot::url($url)
            ->windowSize(1280, 800)
            ->setScreenshotType('jpeg', 80)
            ->dismissDialogs()
            // Local tenant subdomains are served over HTTPS with a self-signed
            // cert (Laragon); without this Chrome blocks the load.
            ->ignoreHttpsErrors()
            ->waitUntilNetworkIdle()
            ->timeout(30);

        // Map *.{central_domain} to localhost so Chrome reaches the local server
        // without wildcard DNS or hosts-file edits.
        if (app()->environment('local')) {
            $central = config('tenancy.central_domain', 'kasiro.my.id');
            $browsershot->addChromiumArguments([
                'host-resolver-rules' => "MAP *.{$central} 127.0.0.1, MAP {$central} 127.0.0.1",
            ]);
        }

        if ($chromePath = $this->resolveChromeExecutable()) {
            $browsershot->setChromePath($chromePath);
        }

        if ($nodePath = $this->resolveNodePath()) {
            $browsershot->setNodeBinary($nodePath);
        }

        if ($npmPath = $this->resolveNpmPath()) {
            $browsershot->setNpmBinary($npmPath);
        }

        $browsershot->save($path);

        $this->template->updateQuietly(['screenshot_path' => $filename]);
    }

    public function failed(Throwable $e): void
    {
        logger()->warning('TemplateScreenshot failed for '.$this->template->slug.': '.$e->getMessage());
    }

    private function previewUrl(): string
    {
        $central = config('tenancy.central_domain', 'kasiro.my.id');
        $scheme = config('app.force_https') ? 'https' : 'http';

        // Ensure the donor subdomain resolves to fresh DB data.
        Cache::forget("tenant:subdomain:{$this->donor->subdomain}");

        $token = Str::random(48);
        Cache::put(PreviewController::templateCacheKey($this->template->id), $token, now()->addMinutes(10));

        return $scheme.'://'.$this->donor->subdomain.'.'.$central
            .'/__template-preview/'.$this->template->id.'?token='.$token;
    }
}
