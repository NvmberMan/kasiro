<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;


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
        if ($this->tenantContext->has()) {
            $tenant = $this->tenantContext->get();

            return Locale::normalize($tenant->locale ?? $tenant->owner?->locale);
        }

        $user = $request->user();

        if ($user && Locale::isSupported($user->locale)) {
            return $user->locale;
        }

        return Locale::normalize($request->session()->get(Locale::SESSION_KEY));
    }
}
