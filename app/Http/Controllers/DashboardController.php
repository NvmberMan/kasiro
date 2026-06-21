<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function home(Request $request): View
    {
        $user = $request->user();

        // Toko aktif yang diikuti user (sebagai owner maupun anggota).
        $activeTenantIds = $user->tenants()
            ->wherePivot('status', MembershipStatus::Active->value)
            ->where('tenants.status', Tenant::STATUS_ACTIVE)
            ->pluck('tenants.id');

        $activeCount = $activeTenantIds->count();

        $archivedCount = $user->ownedTenants()
            ->where('status', Tenant::STATUS_ARCHIVED)
            ->count();

        $recent = $user->tenants()
            ->wherePivot('status', MembershipStatus::Active->value)
            ->where('tenants.status', Tenant::STATUS_ACTIVE)
            ->latest('tenants.created_at')
            ->take(3)
            ->get();

        // Ringkasan agregat lintas toko milik user. Memakai withoutTenantScope
        // lalu membatasi ke tenant user, jadi tidak ada kebocoran data tenant lain.
        $summary = [
            'products' => Product::withoutTenantScope()
                ->whereIn('tenant_id', $activeTenantIds)
                ->where('is_active', true)
                ->count(),
            'transactions' => Transaction::withoutTenantScope()
                ->whereIn('tenant_id', $activeTenantIds)
                ->count(),
            'revenue' => (float) Transaction::withoutTenantScope()
                ->whereIn('tenant_id', $activeTenantIds)
                ->sum('total'),
        ];

        return view('dashboard', compact('activeCount', 'archivedCount', 'recent', 'summary'));
    }

    public function myStores(Request $request): View
    {
        $tenants = $request->user()->tenants()
            ->wherePivot('status', MembershipStatus::Active->value)
            ->where('tenants.status', Tenant::STATUS_ACTIVE)
            ->orderBy('tenants.name')
            ->get();

        return view('dashboard.my-stores', compact('tenants'));
    }

    public function archive(Request $request): View
    {
        $tenants = $request->user()->ownedTenants()
            ->where('status', Tenant::STATUS_ARCHIVED)
            ->latest('archived_at')
            ->get();

        return view('dashboard.archive', compact('tenants'));
    }
}
