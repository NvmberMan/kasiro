<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function home(Request $request): View
    {
        $user = $request->user();

        $activeCount = $user->tenants()
            ->wherePivot('status', MembershipStatus::Active->value)
            ->where('tenants.status', Tenant::STATUS_ACTIVE)
            ->count();

        $archivedCount = $user->ownedTenants()
            ->where('status', Tenant::STATUS_ARCHIVED)
            ->count();

        $recent = $user->tenants()
            ->wherePivot('status', MembershipStatus::Active->value)
            ->where('tenants.status', Tenant::STATUS_ACTIVE)
            ->latest('tenants.created_at')
            ->take(3)
            ->get();

        // Platform-wide activation funnel (visible to all authenticated users)
        $platformStats = [
            'total_users'        => User::count(),
            'total_tenants'      => Tenant::count(),
            'active_tenants'     => Tenant::where('status', Tenant::STATUS_ACTIVE)->count(),
            'activated_tenants'  => Tenant::whereHas('transactions')->count(),
        ];

        return view('dashboard', compact('activeCount', 'archivedCount', 'recent', 'platformStats'));
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
