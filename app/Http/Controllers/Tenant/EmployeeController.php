<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\ChangeEmployeeRole;
use App\Actions\RevokeEmployee;
use App\Http\Controllers\Controller;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage-staff');

        $tenant = app(TenantContext::class)->get();

        $members = $tenant->users()
            ->withPivot('role', 'status')
            ->get();

        $pendingInvitations = TenantInvitation::where('tenant_id', $tenant->id)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->with('inviter')
            ->latest()
            ->get();

        return view('tenant.employees.index', compact('tenant', 'members', 'pendingInvitations'));
    }

    public function update(Request $request, ChangeEmployeeRole $action): RedirectResponse
    {
        Gate::authorize('manage-staff');

        $tenant = app(TenantContext::class)->get();
        $employee = User::findOrFail((int) $request->route('user'));

        $request->validate(['role' => ['required', 'in:manager,cashier']]);

        try {
            $action->handle($tenant, $employee, $request->input('role'));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['role' => $e->getMessage()]);
        }

        return redirect()
            ->route('tenant.employees.index', ['subdomain' => $tenant->subdomain])
            ->with('status', 'role-updated');
    }

    public function destroy(Request $request, RevokeEmployee $action): RedirectResponse
    {
        Gate::authorize('manage-staff');

        $tenant = app(TenantContext::class)->get();
        $employee = User::findOrFail((int) $request->route('user'));

        try {
            $action->handle($tenant, $employee);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['employee' => $e->getMessage()]);
        }

        return redirect()
            ->route('tenant.employees.index', ['subdomain' => $tenant->subdomain])
            ->with('status', 'employee-revoked');
    }
}
