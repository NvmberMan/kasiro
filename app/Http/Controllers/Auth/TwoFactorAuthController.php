<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthController extends Controller
{
    public function __construct(private readonly Google2FA $google2fa) {}

    /**
     * Begin enabling 2FA: generate a secret (unconfirmed) and show the QR.
     */
    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'two_factor_secret' => $this->google2fa->generateSecretKey(),
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back();
    }

    /**
     * Confirm 2FA with a code from the authenticator app, then issue recovery codes.
     *
     * @throws ValidationException
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();

        if (! $user->two_factor_secret || $user->hasTwoFactorEnabled()) {
            return back();
        }

        $valid = $this->google2fa->verifyKey(
            (string) $user->two_factor_secret,
            trim((string) $request->input('code')),
        );

        if (! $valid) {
            throw ValidationException::withMessages([
                'code' => __('Kode autentikasi tidak valid.'),
            ]);
        }

        $user->forceFill([
            'two_factor_recovery_codes' => json_encode($this->generateRecoveryCodes()),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return back()->with('status', 'two-factor-enabled');
    }

    /**
     * Regenerate the recovery codes (gated by password confirmation).
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return back();
        }

        $user->forceFill([
            'two_factor_recovery_codes' => json_encode($this->generateRecoveryCodes()),
        ])->save();

        return back()->with('status', 'recovery-codes-generated');
    }

    /**
     * Disable 2FA entirely (gated by password confirmation).
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('status', 'two-factor-disabled');
    }

    /**
     * Generate a fresh set of one-time recovery codes.
     *
     * @return list<string>
     */
    protected function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(5).'-'.Str::random(5)))
            ->all();
    }
}
