<?php

namespace App\Actions;

use App\Models\Tenant;
use App\Models\User;
use RuntimeException;

class ChangeEmployeeRole
{
    public function handle(Tenant $tenant, User $employee, string $newRole): void
    {
        if ($employee->id === $tenant->owner_id) {
            throw new RuntimeException(__('Role owner tidak dapat diubah.'));
        }

        $tenant->users()->updateExistingPivot($employee->id, ['role' => $newRole]);
    }
}
