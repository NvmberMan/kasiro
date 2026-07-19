<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Permanently deletes tenants that have been archived longer than the retention
 * window (config `tenancy.archive_retention_days`, default 30 days). Related
 * data is removed via FK cascade, mirroring the manual "force delete" flow.
 */
class PurgeArchivedTenants extends Command
{
    protected $signature = 'tenants:purge-archived {--dry-run : List what would be deleted without deleting}';

    protected $description = 'Permanently delete tenants archived beyond the retention window';

    public function handle(): int
    {
        $days = (int) config('tenancy.archive_retention_days', 30);
        $cutoff = now()->subDays($days);

        $tenants = Tenant::query()
            ->where('status', Tenant::STATUS_ARCHIVED)
            ->whereNotNull('archived_at')
            ->where('archived_at', '<=', $cutoff)
            ->get();

        if ($tenants->isEmpty()) {
            $this->info("No archived tenants older than {$days} days.");

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            if ($this->option('dry-run')) {
                $this->line("Would delete: {$tenant->subdomain} (archived {$tenant->archived_at->diffForHumans()})");

                continue;
            }

            DB::transaction(fn () => $tenant->delete());
            $this->line("Deleted: {$tenant->subdomain}");
        }

        $verb = $this->option('dry-run') ? 'Matched' : 'Purged';
        $this->info("{$verb} {$tenants->count()} archived tenant(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
