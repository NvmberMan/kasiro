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
        // Preferred path: hand the job to the queue so a separate worker runs
        // Browsershot (headless Chrome) out-of-band. The web request returns
        // immediately and never competes with — or deadlocks against — the
        // job's own /__preview request. `composer dev` runs a queue:listen,
        // so this works in local too.
        if (config('tenancy.screenshot.queue', true)) {
            GenerateTenantScreenshot::dispatch($tenant)->delay(now()->addSeconds(3));

            return;
        }

        // Fallback for environments with no queue worker: run after the
        // response is flushed to the browser. Creation still feels instant, but
        // the serving worker stays busy until Chrome finishes. A failure here
        // only logs — it can never break the request, which already finished.
        GenerateTenantScreenshot::dispatchAfterResponse($tenant);
    }
}
