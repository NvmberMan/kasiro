<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Redirect any insecure request to its HTTPS equivalent.
     *
     * URL generation is already pinned to https via URL::forceScheme() in the
     * AppServiceProvider; this guards the one case that bypasses generation —
     * a user (or bookmark) hitting an http:// URL directly.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.force_https') && ! $request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
