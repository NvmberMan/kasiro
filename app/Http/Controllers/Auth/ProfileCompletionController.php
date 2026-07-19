<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Onboarding for accounts created via OAuth (Google), which arrive without a
 * password. Users are held here by EnsureProfileComplete until they set one.
 */
class ProfileCompletionController extends Controller
{
    public function show(Request $request): RedirectResponse|View
    {
        if (! $request->user()->needsProfileCompletion()) {
            return redirect()->route('dashboard');
        }

        return view('auth.complete-profile', ['user' => $request->user()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->needsProfileCompletion()) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->forceFill([
            'name' => $validated['name'],
            'password' => Hash::make($validated['password']),
        ])->save();

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('status', 'profile-completed');
    }
}
