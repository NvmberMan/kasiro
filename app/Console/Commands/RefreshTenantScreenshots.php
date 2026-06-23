<?php

namespace App\Console\Commands;

use App\Jobs\GenerateTenantScreenshot;
use App\Models\Tenant;
use Illuminate\Console\Command;

class RefreshTenantScreenshots extends Command
{
    protected $signature = 'tenants:screenshots {--subdomain= : Only refresh this subdomain}';

    protected $description = 'Dispatch screenshot jobs for active tenants';

    public function handle(): int
    {
        $query = Tenant::active();

        if ($subdomain = $this->option('subdomain')) {
            $query->where('subdomain', $subdomain);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No matching tenants found.');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            GenerateTenantScreenshot::dispatch($tenant);
            $this->line("Queued: {$tenant->subdomain}");
        }

        $this->info("Dispatched {$tenants->count()} screenshot job(s).");

        return self::SUCCESS;
    }
}
