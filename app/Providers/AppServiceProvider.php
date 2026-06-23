<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Observers\ProductObserver;
use App\Observers\TenantObserver;
use App\Policies\TenantPolicy;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One tenant context per request lifecycle. Resolved via the container
        // everywhere (TenantScope, BelongsToTenant, controllers) so all readers
        // observe the same active tenant.
        $this->app->singleton(TenantContext::class);
    }

    public function boot(): void
    {
        // Pin every generated URL (routes, redirects, assets) to https so links
        // never downgrade the connection. The ForceHttps middleware handles the
        // remaining case of a request that arrives over http directly.
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        Tenant::observe(TenantObserver::class);
        Product::observe(ProductObserver::class);

        Gate::policy(Tenant::class, TenantPolicy::class);

        // Permission matrix (PRD §12).
        // All gate callbacks resolve the active tenant from TenantContext and the
        // caller's role from the tenant_user pivot. A null role means the user is
        // not an active member, so the gate denies by returning false.

        Gate::define('access-pos', function (User $user) {
            $tenant = app(TenantContext::class)->get();

            return $tenant && $user->isMemberOf($tenant);
        });

        Gate::define('manage-products', function (User $user) {
            $tenant = app(TenantContext::class)->get();
            $role = $tenant ? $user->roleFor($tenant) : null;

            return $role && $role->canManageProducts();
        });

        Gate::define('manage-categories', function (User $user) {
            $tenant = app(TenantContext::class)->get();
            $role = $tenant ? $user->roleFor($tenant) : null;

            return $role && $role->canManageCategories();
        });

        Gate::define('view-reports', function (User $user) {
            $tenant = app(TenantContext::class)->get();
            $role = $tenant ? $user->roleFor($tenant) : null;

            return $role && $role->canViewReports();
        });

        Gate::define('manage-staff', function (User $user) {
            $tenant = app(TenantContext::class)->get();
            $role = $tenant ? $user->roleFor($tenant) : null;

            return $role && $role->canManageStaff();
        });

        Gate::define('manage-tenant-settings', function (User $user) {
            $tenant = app(TenantContext::class)->get();
            $role = $tenant ? $user->roleFor($tenant) : null;

            return $role && $role->canManageTenantSettings();
        });

        Gate::define('manage-billing', function (User $user) {
            $tenant = app(TenantContext::class)->get();
            $role = $tenant ? $user->roleFor($tenant) : null;

            return $role && $role->canManageBilling();
        });
    }
}
