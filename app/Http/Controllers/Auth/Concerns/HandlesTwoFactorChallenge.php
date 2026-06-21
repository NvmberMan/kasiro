<?php

namespace App\Http\Controllers\Auth\Concerns;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

trait HandlesTwoFactorChallenge
{
    /**
     * If the user has 2FA enabled, undo the credential login and redirect to the
     * challenge screen, stashing the pending user in the session. Returns null
     * when 2FA is not enabled so the caller can continue the normal login flow.
     */
    protected function requiresTwoFactor(User $user, bool $remember): ?RedirectResponse
    {
        if (! $user->hasTwoFactorEnabled()) {
            return null;
        }

        Auth::guard('web')->logout();

        session()->put([
            'login.id' => $user->id,
            'login.remember' => $remember,
        ]);

        return redirect()->route('two-factor.login');
    }
}
