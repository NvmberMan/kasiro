<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Funnels authenticated accounts that still need onboarding (OAuth users with no
 * password) to the "complete profile" page until they finish it. Runs on the web
 * middleware group; it is a no-op for guests and for accounts already complete.
 */
class EnsureProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->needsProfileCompletion() && ! $this->isAllowed($request)) {
            return redirect()->route('profile.complete');
        }

        return $next($request);
    }

    /**
     * Routes the user must still reach while their profile is incomplete, to avoid
     * a redirect loop and to let them finish or leave.
     */
    protected function isAllowed(Request $request): bool
    {
        return $request->routeIs(
            'profile.complete',
            'profile.complete.store',
            // Both routes set the missing password, which resolves completion.
            'password.update',
            'logout',
            'locale.update',
        );
    }
}
