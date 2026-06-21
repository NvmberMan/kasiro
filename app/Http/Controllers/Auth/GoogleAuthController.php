<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\HandlesTwoFactorChallenge;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

class GoogleAuthController extends Controller
{
    use HandlesTwoFactorChallenge;

    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function redirect(): SymfonyRedirect
    {
        $this->ensureConfigured();

        return Socialite::driver('google')
            ->redirectUrl(route('auth.google.callback'))
            ->redirect();
    }

    /**
     * Handle the OAuth callback: find, link, or create the user, then log in.
     */
    public function callback(): RedirectResponse
    {
        $this->ensureConfigured();

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('auth.google.callback'))
                ->user();
        } catch (\Throwable) {
            return redirect()->route('login')->withErrors([
                'email' => __('Gagal masuk dengan Google. Silakan coba lagi.'),
            ]);
        }

        $user = $this->findOrCreateUser($googleUser);

        if ($redirect = $this->requiresTwoFactor($user, true)) {
            return $redirect;
        }

        Auth::login($user, true);
        session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Resolve the local user for a Google identity, linking by email when possible.
     */
    protected function findOrCreateUser(\Laravel\Socialite\Contracts\User $googleUser): User
    {
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            return $user;
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'avatar' => $user->avatar ?: $googleUser->getAvatar(),
            ])->save();

            return $user;
        }

        $user = User::create([
            'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Pengguna',
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'password' => null,
        ]);

        // Google has already verified the email address.
        $user->markEmailAsVerified();

        return $user;
    }

    /**
     * Hide the feature entirely when credentials are not configured.
     */
    protected function ensureConfigured(): void
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            abort(404);
        }
    }
}
