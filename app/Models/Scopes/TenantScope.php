<?php

namespace App\Models\Scopes;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Constrains every query on a tenant-scoped model to the active tenant.
 *
 * When no tenant is resolved for the current context (platform routes,
 * console, seeders, queued jobs) the scope is a no-op — callers that need
 * cross-tenant access in those contexts use Model::withoutTenantScope().
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);

        if ($context->has()) {
            $builder->where(
                $model->getTable().'.'.$model->getTenantColumn(),
                $context->id()
            );
        }
    }
}
