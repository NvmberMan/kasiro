<?php

namespace App\Actions;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class ArchiveTenant
{
    public function handle(Tenant $tenant): Tenant
    {
        return DB::transaction(function () use ($tenant): Tenant {
            $tenant->status      = Tenant::STATUS_ARCHIVED;
            $tenant->archived_at = now();
            $tenant->save();

            return $tenant;
        });
    }
}
