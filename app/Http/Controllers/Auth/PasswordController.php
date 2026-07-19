<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Email a signed password-reset link to the authenticated user so they can
     * change an existing password only after clicking through their inbox.
     */
    public function sendChangeLink(Request $request): RedirectResponse
    {
        PasswordBroker::sendResetLink(['email' => $request->user()->email]);

        // Always report success regardless of broker result: the address is the
        // logged-in user's own, and we avoid leaking rate-limit/user state.
        return back()->with('status', 'password-change-link-sent');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $rules = [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];

        // Accounts created via Google have no password yet — there is nothing to
        // confirm, so only require the current password when one already exists.
        if ($request->user()->password !== null) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validateWithBag('updatePassword', $rules);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
