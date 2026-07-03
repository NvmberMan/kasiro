<?php

namespace App\Http\Controllers;

use App\Actions\AcceptInvitation;
use App\Models\TenantInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcceptInvitationController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $invitation = TenantInvitation::where('token', hash('sha256', $token))
            ->with('tenant')
            ->first();

        if (! $invitation || ! $invitation->isPending()) {
            return redirect()->route('dashboard')->withErrors(['invite' => 'Undangan tidak valid atau sudah kedaluwarsa.']);
        }

        return view('invitations.show', compact('invitation', 'token'));
    }

    public function accept(Request $request, string $token, AcceptInvitation $action): RedirectResponse
    {
        try {
            $invitation = $action->handle($token, $request->user());
        } catch (\RuntimeException $e) {
            return redirect()->route('dashboard')->withErrors(['invite' => $e->getMessage()]);
        }

        $subdomain = $invitation->tenant->subdomain;

        return redirect()
            ->away("http://{$subdomain}.kasiro.my.id/pos")
            ->with('status', 'invitation-accepted');
    }
}
