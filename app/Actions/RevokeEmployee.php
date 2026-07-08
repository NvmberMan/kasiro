<?php

namespace App\Actions;

use App\Models\Tenant;
use App\Models\User;
use RuntimeException;

class RevokeEmployee
{
    public function handle(Tenant $tenant, User $employee): void
    {
        if ($employee->id === $tenant->owner_id) {
            throw new RuntimeException(__('Owner tidak dapat dicabut aksesnya.'));
        }

        $tenant->users()->updateExistingPivot($employee->id, ['status' => 'revoked']);
    }
}
