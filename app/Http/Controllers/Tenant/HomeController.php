<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Tenant landing/dashboard. Queries are tenant-scoped automatically via the
     * active TenantContext (BelongsToTenant global scope).
     */
    public function index(Request $request, TenantContext $context): View
    {
        $tenant = $context->get();
        $user = $request->user();
        $role = $user?->roleFor($tenant);

        $activeProducts = Product::query()->where('is_active', true)->count();
        $lowStock = Product::query()->where('is_active', true)->where('stock', '<=', 5)->count();

        // Ringkasan penjualan hanya untuk peran yang boleh melihat laporan (PRD §12).
        $canViewReports = (bool) $role?->canViewReports();
        $stats = null;
        $recent = collect();

        if ($canViewReports) {
            $stats = [
                'todaySales' => (float) Transaction::query()->whereDate('transacted_at', today())->sum('total'),
                'todayCount' => Transaction::query()->whereDate('transacted_at', today())->count(),
                'monthSales' => (float) Transaction::query()
                    ->whereYear('transacted_at', now()->year)
                    ->whereMonth('transacted_at', now()->month)
                    ->sum('total'),
            ];

            $recent = Transaction::query()
                ->with('cashier')
                ->latest('transacted_at')
                ->limit(5)
                ->get();
        }

        return view('tenant.home', [
            'tenant' => $tenant,
            'user' => $user,
            'role' => $role,
            'canViewReports' => $canViewReports,
            'activeProducts' => $activeProducts,
            'lowStock' => $lowStock,
            'stats' => $stats,
            'recent' => $recent,
        ]);
    }
}
