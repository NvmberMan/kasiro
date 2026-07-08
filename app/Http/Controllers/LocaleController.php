<?php

namespace App\Http\Controllers;

use App\Support\Locale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch the platform (studio) language. Stored in the session for guests
     * and persisted to the account when the user is authenticated, then the
     * user is sent back to the page they came from.
     */
    public function update(Request $request, string $locale): RedirectResponse
    {
        $locale = Locale::normalize($locale);

        $request->session()->put(Locale::SESSION_KEY, $locale);

        if ($user = $request->user()) {
            $user->forceFill(['locale' => $locale])->save();
        }

        return redirect()->back();
    }
}
