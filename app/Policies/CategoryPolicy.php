<?php

namespace App\Policies;

use App\Enums\TenantRole;
use App\Models\Category;
use App\Models\User;
use App\Support\TenantContext;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Category $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Category $category): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->canManage($user);
    }

    private function canManage(User $user): bool
    {
        $tenant = app(TenantContext::class)->get();
        $role   = $tenant ? $user->roleFor($tenant) : null;

        return in_array($role, [TenantRole::Owner, TenantRole::Manager], true);
    }
}
