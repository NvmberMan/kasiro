<?php

use App\Http\Middleware\EnsureProfileComplete;
use App\Http\Middleware\EnsureTenantContext;
use App\Http\Middleware\EnsureTenantMember;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Redirect insecure requests to HTTPS before anything else runs.
        $middleware->prepend(ForceHttps::class);

        // Force OAuth accounts without a password through profile completion.
        $middleware->web(append: [EnsureProfileComplete::class]);

        $middleware->redirectGuestsTo(function () {
            $scheme = config('app.force_https') ? 'https' : 'http';

            return $scheme.'://'.config('tenancy.central_domain').'/login';
        });

        $middleware->alias([
            'tenant' => ResolveTenant::class,
            'tenant.context' => EnsureTenantContext::class,
            'tenant.member' => EnsureTenantMember::class,
            'setlocale' => SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
