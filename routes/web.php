<?php

use App\Http\Controllers\ProfileController;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Route;

$central = config('tenancy.central_domain');

/*
|--------------------------------------------------------------------------
| Platform routes (apex: kasiro.com)
|--------------------------------------------------------------------------
| The platform itself — landing, centralized auth, dashboard. No tenant
| context. Breeze auth routes (routes/auth.php) are scoped to the apex so
| login/register never leak onto tenant subdomains.
*/
Route::domain($central)->group(function () {
    Route::get('/', function () {
        // Real landing page is Milestone 4; keep the M1 placeholder for now.
        return 'Kasiro platform';
    })->name('platform.home');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';
});

/*
|--------------------------------------------------------------------------
| Tenant routes (*.kasiro.com)
|--------------------------------------------------------------------------
| Resolved at runtime from the host by ResolveTenant; EnsureTenantContext
| guards against any tenant route running without an active tenant. Auth +
| membership guards (auth, tenant.member) are layered in M2 Tasks 5–6.
*/
Route::domain('{subdomain}.'.$central)
    ->middleware(['tenant', 'tenant.context'])
    ->group(function () {
        Route::get('/', function () {
            $tenant = app(TenantContext::class)->get();

            return response()->json([
                'tenant' => $tenant->name,
                'subdomain' => $tenant->subdomain,
            ]);
        })->name('tenant.home');
    });
