<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Automatic per-tenant data isolation (PRD §9 NFR-5, E8 — CRITICAL).
 *
 * Any model using this trait:
 *  - is globally scoped to the active tenant on every read (TenantScope);
 *  - has its tenant_id auto-filled from the active tenant on create, and
 *    cannot have it overridden by mass assignment / user input.
 *
 * Administrative / cross-tenant access is only possible through the explicit,
 * documented bypass Model::withoutTenantScope().
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model): void {
            $context = app(TenantContext::class);

            // Always derive tenant_id from the active context; never trust an
            // incoming value. Only set when a tenant is active so that
            // explicitly-scoped administrative writes remain possible.
            if ($context->has()) {
                $model->setAttribute($model->getTenantColumn(), $context->id());
            }
        });
    }

    /**
     * The foreign key column that ties this model to a tenant.
     */
    public function getTenantColumn(): string
    {
        return 'tenant_id';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, $this->getTenantColumn());
    }

    /**
     * Query builder with the tenant global scope removed.
     *
     * Use only in trusted administrative / cross-tenant contexts. Callers are
     * responsible for any tenant filtering they still need.
     */
    public static function withoutTenantScope(): Builder
    {
        return static::query()->withoutGlobalScope(TenantScope::class);
    }
}
