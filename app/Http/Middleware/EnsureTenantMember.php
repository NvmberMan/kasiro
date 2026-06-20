<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app(TenantContext::class)->get();

        if ($tenant === null) {
            abort(403);
        }

        $user = $request->user();

        if ($user === null) {
            // Unauthenticated guests on tenant routes go to the apex login.
            // Using a redirect here (rather than the auth middleware alias) ensures
            // ResolveTenant runs first and can abort 404 for unknown subdomains
            // before any auth check happens — important for anonymous visitors.
            return redirect('http://'.config('tenancy.central_domain').'/login');
        }

        if (! $user->isMemberOf($tenant)) {
            abort(403);
        }

        return $next($request);
    }
}
