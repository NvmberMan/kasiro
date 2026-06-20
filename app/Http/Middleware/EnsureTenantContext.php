<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards tenant-only routes: rejects requests that reached a tenant route
 * without an active tenant resolved (defense-in-depth behind ResolveTenant).
 */
class EnsureTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(TenantContext::class)->has()) {
            abort(404);
        }

        return $next($request);
    }
}
