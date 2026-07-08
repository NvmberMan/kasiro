<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active locale for the request.
 *
 * Registered explicitly on both the central-domain route group (studio
 * locale) and the tenant route groups (tenant locale, after `tenant.context`
 * resolves). It must NOT also be appended to the global `web` middleware
 * group: Laravel's route middleware pipeline dedupes by resolved class name
 * (see `SortedMiddleware::sortMiddleware()` -> `Router::uniqueMiddleware()`),
 * so a second occurrence of the same middleware class is silently dropped —
 * that previously made the tenant-aware pass never run, so tenant locale
 * overrides were always ignored.
 */
class SetLocale
{
    public function __construct(private TenantContext $tenantContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($this->resolve($request));

        return $next($request);
    }

    private function resolve(Request $request): string
    {
        // Inside a tenant: the tenant decides its own language. A null tenant
        // locale means "follow the owner's studio language" dynamically.
        if ($this->tenantContext->has()) {
            $tenant = $this->tenantContext->get();

            return Locale::normalize($tenant->locale ?? $tenant->owner?->locale);
        }

        // Platform / studio: logged-in preference wins, else session, else default.
        $user = $request->user();

        if ($user && Locale::isSupported($user->locale)) {
            return $user->locale;
        }

        return Locale::normalize($request->session()->get(Locale::SESSION_KEY));
    }
}
