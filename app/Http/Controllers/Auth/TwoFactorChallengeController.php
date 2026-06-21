<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    /**
     * Show the two-factor challenge screen for a user pending second-factor.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the authenticator code or a recovery code, then complete login.
     */
    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('login.id');

        if (! $userId || ! $user = User::find($userId)) {
            return redirect()->route('login');
        }

        $this->verify($request, $user);

        Auth::login($user, (bool) $request->session()->get('login.remember', false));

        $request->session()->forget(['login.id', 'login.remember']);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * @throws ValidationException
     */
    protected function verify(Request $request, User $user): void
    {
        $code = trim((string) $request->input('code'));
        $recovery = trim((string) $request->input('recovery_code'));

        if ($recovery !== '') {
            $valid = collect($user->recoveryCodes())
                ->contains(fn (string $stored) => hash_equals($stored, $recovery));

            if (! $valid) {
                throw ValidationException::withMessages([
                    'recovery_code' => __('Kode pemulihan tidak valid.'),
                ]);
            }

            $user->replaceRecoveryCode($recovery);

            return;
        }

        $isValid = $code !== '' && app(Google2FA::class)->verifyKey(
            (string) $user->two_factor_secret,
            $code,
        );

        if (! $isValid) {
            throw ValidationException::withMessages([
                'code' => __('Kode autentikasi tidak valid.'),
            ]);
        }
    }
}
