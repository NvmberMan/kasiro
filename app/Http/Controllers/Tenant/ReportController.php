<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view-reports');

        $tenant = app(TenantContext::class)->get();

        // Today's summary
        $todayTotal = Transaction::whereDate('transacted_at', today())->sum('total');
        $todayCount = Transaction::whereDate('transacted_at', today())->count();

        // This month's summary
        $monthTotal = Transaction::whereYear('transacted_at', now()->year)
            ->whereMonth('transacted_at', now()->month)
            ->sum('total');
        $monthCount = Transaction::whereYear('transacted_at', now()->year)
            ->whereMonth('transacted_at', now()->month)
            ->count();

        // Daily sales for last 30 days
        $dailySales = Transaction::selectRaw('DATE(transacted_at) as date, COUNT(*) as tx_count, SUM(total) as revenue')
            ->where('transacted_at', '>=', now()->subDays(29)->startOfDay())
            ->groupByRaw('DATE(transacted_at)')
            ->orderBy('date', 'desc')
            ->get();

        // Top 10 products by qty sold (all time for this tenant)
        $topProducts = TransactionItem::selectRaw('product_id, SUM(qty) as total_qty, SUM(subtotal) as total_revenue')
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        return view('tenant.reports.index', compact(
            'tenant',
            'todayTotal', 'todayCount',
            'monthTotal', 'monthCount',
            'dailySales', 'topProducts',
        ));
    }
}
