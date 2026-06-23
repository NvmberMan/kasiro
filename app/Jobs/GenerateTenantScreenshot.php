<?php

namespace App\Jobs;

use App\Http\Controllers\Tenant\PreviewController;
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

class GenerateTenantScreenshot implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    /**
     * Collapse duplicate screenshot jobs for the same tenant queued within this
     * window, so rapid setting saves don't pile up Chrome launches.
     */
    public int $uniqueFor = 120;

    public function __construct(public readonly Tenant $tenant) {}

    public function uniqueId(): string
    {
        return (string) $this->tenant->id;
    }

    public function handle(): void
    {
        $url = $this->tenantUrl();
        $filename = 'screenshots/'.$this->tenant->subdomain.'.jpg';
        $path = storage_path('app/public/'.$filename);

        if (! is_dir(storage_path('app/public/screenshots'))) {
            mkdir(storage_path('app/public/screenshots'), 0755, true);
        }

        $browsershot = Browsershot::url($url)
            ->windowSize(1280, 800)
            ->setScreenshotType('jpeg', 80)
            ->dismissDialogs()
            ->waitUntilNetworkIdle()
            ->timeout(30);

        // In local environments subdomains like demo.kasiro.com may not have
        // wildcard DNS. Override Chrome's resolver so *.{central_domain}
        // maps to 127.0.0.1 without touching the system hosts file.
        if (app()->environment('local')) {
            $central = config('tenancy.central_domain', 'kasiro.com');
            $browsershot->addChromiumArguments([
                'host-resolver-rules' => "MAP *.{$central} 127.0.0.1, MAP {$central} 127.0.0.1",
            ]);
        }

        $chromePath = $this->resolveChromeExecutable();
        if ($chromePath) {
            $browsershot->setChromePath($chromePath);
        }

        $nodePath = $this->resolveNodePath();
        if ($nodePath) {
            $browsershot->setNodeBinary($nodePath);
        }

        $npmPath = $this->resolveNpmPath();
        if ($npmPath) {
            $browsershot->setNpmBinary($npmPath);
        }

        $browsershot->save($path);

        $this->tenant->updateQuietly(['screenshot_path' => $filename]);
    }

    public function failed(Throwable $e): void
    {
        // Leave existing screenshot untouched on failure; it will retry.
        logger()->warning('TenantScreenshot failed for '.$this->tenant->subdomain.': '.$e->getMessage());
    }

    private function tenantUrl(): string
    {
        $central = config('tenancy.central_domain', 'kasiro.com');
        $scheme = config('app.force_https') ? 'https' : 'http';

        // Bust the tenant subdomain cache so the preview request gets fresh
        // data from DB. Critical for the dispatchSync (local) path where this
        // job runs before UpdateTenantSettings has a chance to call Cache::forget.
        Cache::forget("tenant:subdomain:{$this->tenant->subdomain}");

        // Mint a short-lived token the public preview route validates, so the
        // real (owner's) cashier screen renders without an interactive login.
        $token = Str::random(48);
        Cache::put(PreviewController::cacheKey($this->tenant->id), $token, now()->addMinutes(10));

        return $scheme.'://'.$this->tenant->subdomain.'.'.$central.'/__preview?token='.$token;
    }

    private function resolveChromeExecutable(): ?string
    {
        $paths = [
            config('browsershot.chrome_path'),
            'C:/Program Files/Google/Chrome/Application/chrome.exe',
            'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium-browser',
        ];

        foreach ($paths as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function resolveNodePath(): ?string
    {
        if ($configured = config('browsershot.node_path')) {
            return file_exists($configured) ? $configured : null;
        }

        $candidates = [
            'C:/Program Files/nodejs/node.exe',
            '/usr/bin/node',
            '/usr/local/bin/node',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function resolveNpmPath(): ?string
    {
        if ($configured = config('browsershot.npm_path')) {
            return file_exists($configured) ? $configured : null;
        }

        $candidates = [
            'C:/Program Files/nodejs/npm.cmd',
            '/usr/bin/npm',
            '/usr/local/bin/npm',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
