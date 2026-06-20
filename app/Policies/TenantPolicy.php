<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function update(User $user, Tenant $tenant): bool
    {
        return $user->roleFor($tenant)?->canManageTenantSettings() ?? false;
    }

    public function manageStaff(User $user, Tenant $tenant): bool
    {
        return $user->roleFor($tenant)?->canManageStaff() ?? false;
    }

    public function manageBilling(User $user, Tenant $tenant): bool
    {
        return $user->roleFor($tenant)?->canManageBilling() ?? false;
    }
}
