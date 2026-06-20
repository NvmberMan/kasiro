<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active tenant from the request host (PRD §11).
 *
 *  - apex / www / non-matching host  -> platform mode (no tenant context)
 *  - reserved subdomain              -> 404 (never a tenant)
 *  - unknown subdomain               -> 404
 *  - archived tenant                 -> "tenant tidak aktif" page (E3)
 *  - active tenant                   -> TenantContext::set()
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $this->extractSubdomain($request->getHost());

        // Platform mode: apex domain, www, or a host outside the central domain.
        if ($subdomain === null) {
            return $next($request);
        }

        $reserved = array_map('strtolower', (array) config('tenancy.reserved_subdomains', []));

        if (in_array($subdomain, $reserved, true)) {
            abort(404);
        }

        $tenant = $this->lookup($subdomain);

        if ($tenant === null) {
            Log::warning('Tenant resolution failed: unknown subdomain', ['subdomain' => $subdomain]);
            abort(404);
        }

        if ($tenant->isArchived()) {
            return response()->view('tenant.archived', ['tenant' => $tenant], 403);
        }

        app(TenantContext::class)->set($tenant);

        return $next($request);
    }

    /**
     * Derive the tenant subdomain label from a request host.
     *
     * Returns null for platform mode (apex, "www", or any host that is not a
     * subdomain of the configured central domain — e.g. 127.0.0.1).
     */
    protected function extractSubdomain(string $host): ?string
    {
        $host = strtolower($host);
        $central = strtolower((string) config('tenancy.central_domain', 'kasiro.com'));

        if ($host === $central) {
            return null;
        }

        $suffix = '.'.$central;

        if (! str_ends_with($host, $suffix)) {
            return null;
        }

        $label = substr($host, 0, -strlen($suffix));

        // Only a single-label subdomain identifies a tenant; deeper hosts
        // (e.g. "a.b.kasiro.com") and "www" are platform/none.
        if ($label === '' || $label === 'www' || str_contains($label, '.')) {
            return null;
        }

        return $label;
    }

    /**
     * Cached subdomain -> tenant lookup (NFR-1). Only positive hits are cached;
     * misses fall through to the database so newly created tenants resolve
     * immediately.
     */
    protected function lookup(string $subdomain): ?Tenant
    {
        $ttl = (int) config('tenancy.cache_ttl', 300);

        $tenant = Cache::remember(
            "tenant:subdomain:{$subdomain}",
            $ttl,
            fn () => Tenant::findBySubdomain($subdomain)
        );

        if ($tenant === null) {
            Cache::forget("tenant:subdomain:{$subdomain}");
        }

        return $tenant;
    }
}
