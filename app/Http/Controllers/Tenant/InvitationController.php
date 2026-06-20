<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CreateInvitation;
use App\Http\Controllers\Controller;
use App\Models\TenantInvitation;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvitationController extends Controller
{
    public function store(Request $request, CreateInvitation $action): RedirectResponse
    {
        Gate::authorize('manage-staff');

        $tenant = app(TenantContext::class)->get();

        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role'  => ['required', 'in:manager,cashier'],
        ]);

        try {
            $plain = $action->handle(
                tenant: $tenant,
                email: $request->input('email'),
                role: $request->input('role'),
                inviter: $request->user(),
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }

        $link = route('invitations.show', ['token' => $plain]);

        return redirect()
            ->route('tenant.employees.index', ['subdomain' => $tenant->subdomain])
            ->with('invitation_link', $link);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Gate::authorize('manage-staff');

        $tenant = app(TenantContext::class)->get();
        $id = (int) $request->route('invitation');

        TenantInvitation::where('tenant_id', $tenant->id)
            ->whereNull('accepted_at')
            ->findOrFail($id)
            ->delete();

        return redirect()
            ->route('tenant.employees.index', ['subdomain' => $tenant->subdomain])
            ->with('status', 'invitation-cancelled');
    }
}
