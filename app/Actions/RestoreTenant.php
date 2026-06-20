<?php

namespace App\Actions;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class RestoreTenant
{
    public function handle(Tenant $tenant): Tenant
    {
        return DB::transaction(function () use ($tenant): Tenant {
            $tenant->status      = Tenant::STATUS_ACTIVE;
            $tenant->archived_at = null;
            $tenant->save();

            return $tenant;
        });
    }
}
