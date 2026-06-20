<?php

namespace App\Policies;

use App\Enums\TenantRole;
use App\Models\Transaction;
use App\Models\User;
use App\Support\TenantContext;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewHistory($user);
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $this->canViewHistory($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    private function canViewHistory(User $user): bool
    {
        $tenant = app(TenantContext::class)->get();
        $role   = $tenant ? $user->roleFor($tenant) : null;

        return in_array($role, [TenantRole::Owner, TenantRole::Manager], true);
    }
}
