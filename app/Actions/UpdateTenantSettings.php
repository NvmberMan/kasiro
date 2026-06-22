<?php

namespace App\Actions;

use App\Models\Tenant;
use App\Support\ThemeConfig;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class UpdateTenantSettings
{
    public function handle(Tenant $tenant, array $data): Tenant
    {
        $oldSubdomain = $tenant->subdomain;

        $updates = [
            'name'        => $data['name'],
            'subdomain'   => $data['subdomain'],
            'tax_percent' => $data['tax_percent'] ?? 0,
        ];

        // Logo: replace old file only if a new one is uploaded
        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            if ($tenant->logo_path) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $updates['logo_path'] = $data['logo']->store('logos', 'public');
        }

        // Theme config: merge so unset keys keep their existing value
        $existing = $tenant->theme_config ?? [];
        $updates['theme_config'] = ThemeConfig::fromCustomInput(array_merge($existing, $data));

        $tenant->update($updates);

        // Bust the subdomain → tenant cache so the next request sees fresh data.
        Cache::forget("tenant:subdomain:{$oldSubdomain}");

        return $tenant->fresh();
    }
}
