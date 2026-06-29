<?php

use App\Http\Controllers\AcceptInvitationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\CreateTenantController;
use App\Http\Controllers\Tenant\EmployeeController;
use App\Http\Controllers\Tenant\HomeController as TenantHomeController;
use App\Http\Controllers\Tenant\InvitationController;
use App\Http\Controllers\Tenant\PosController;
use App\Http\Controllers\Tenant\PreviewController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\SettingsController;
use App\Http\Controllers\Tenant\TenantArchiveController;
use App\Http\Controllers\Tenant\TransactionController;
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
    Route::get('/', [LandingController::class, 'index'])->name('platform.home');

    Route::get('/dashboard', [DashboardController::class, 'home'])
        ->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/my-stores', [DashboardController::class, 'myStores'])->name('my-stores');
        Route::get('/archive', [DashboardController::class, 'archive'])->name('archive');
        Route::post('/tenants/{tenant}/archive', [TenantArchiveController::class, 'store'])->name('tenants.archive');
        Route::delete('/tenants/{tenant}/archive', [TenantArchiveController::class, 'destroy'])->name('tenants.restore');
        Route::delete('/tenants/{tenant}', [TenantArchiveController::class, 'forceDelete'])->name('tenants.destroy');

        Route::get('/tenants/create', [CreateTenantController::class, 'chooseFlow'])->name('tenants.choose');
        Route::get('/tenants/create/custom', [CreateTenantController::class, 'createCustom'])->name('tenants.create.custom');
        Route::post('/tenants/create/custom', [CreateTenantController::class, 'storeCustom'])->name('tenants.store.custom');
        Route::get('/tenants/create/template', [CreateTenantController::class, 'createFromTemplate'])->name('tenants.create.template');
        Route::post('/tenants/create/template', [CreateTenantController::class, 'storeFromTemplate'])->name('tenants.store.template');
        Route::get('/tenants/{tenant}/created', [CreateTenantController::class, 'created'])->name('tenants.created');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/invitations/{token}', [AcceptInvitationController::class, 'show'])->name('invitations.show');
        Route::post('/invitations/{token}/accept', [AcceptInvitationController::class, 'accept'])->name('invitations.accept');
    });
});

/*
|--------------------------------------------------------------------------
| Tenant routes (*.kasiro.com)
|--------------------------------------------------------------------------
| Resolved at runtime from the host by ResolveTenant; EnsureTenantContext
| guards against any tenant route running without an active tenant. Auth +
| membership guards (auth, tenant.member) are layered in M2 Tasks 5–6.
*/
// Public preview — no auth required, only resolves tenant + context.
Route::domain('{subdomain}.'.$central)
    ->middleware(['tenant', 'tenant.context'])
    ->group(function () {
        Route::get('/__preview', [PreviewController::class, 'index'])->name('tenant.preview');
    });

Route::domain('{subdomain}.'.$central)
    ->middleware(['tenant', 'tenant.member', 'tenant.context'])
    ->group(function () {
        Route::get('/', [TenantHomeController::class, 'index'])->name('tenant.home');

        Route::get('/pos', [PosController::class, 'index'])->name('tenant.pos');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('tenant.pos.checkout');

        Route::resource('categories', CategoryController::class)
            ->except(['show'])
            ->names('tenant.categories');

        Route::resource('products', ProductController::class)
            ->except(['show'])
            ->names('tenant.products');

        Route::get('/transactions', [TransactionController::class, 'index'])->name('tenant.transactions');

        Route::get('/employees', [EmployeeController::class, 'index'])->name('tenant.employees.index');
        Route::patch('/employees/{user}', [EmployeeController::class, 'update'])->name('tenant.employees.update');
        Route::delete('/employees/{user}', [EmployeeController::class, 'destroy'])->name('tenant.employees.destroy');
        Route::post('/invitations', [InvitationController::class, 'store'])->name('tenant.invitations.store');
        Route::delete('/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('tenant.invitations.destroy');

        Route::get('/reports', [ReportController::class, 'index'])->name('tenant.reports');
        Route::get('/settings', [SettingsController::class, 'edit'])->name('tenant.settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('tenant.settings.update');
    });
