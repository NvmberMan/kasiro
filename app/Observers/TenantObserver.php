<?php

namespace App\Observers;

use App\Jobs\GenerateTenantScreenshot;
use App\Models\Tenant;

class TenantObserver
{
    public function created(Tenant $tenant): void
    {
        $this->dispatch($tenant);
    }

    public function updated(Tenant $tenant): void
    {
        $visualFields = ['theme_config', 'subdomain', 'template_id', 'logo_path'];

        $restoredFromArchive = $tenant->wasChanged('status')
            && $tenant->status === Tenant::STATUS_ACTIVE;

        if ($tenant->wasChanged($visualFields) || $restoredFromArchive) {
            $this->dispatch($tenant);
        }
    }

    private function dispatch(Tenant $tenant): void
    {
        if (app()->environment('local')) {
            GenerateTenantScreenshot::dispatchSync($tenant);
        } else {
            GenerateTenantScreenshot::dispatch($tenant)->delay(now()->addSeconds(3));
        }
    }
}
