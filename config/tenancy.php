<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Central (apex) domain
    |--------------------------------------------------------------------------
    |
    | The root domain that serves the platform itself (landing, auth, dashboard).
    | Tenants live on subdomains of this domain, e.g. "warungbudi.kasiro.my.id".
    | Resolution strips this suffix from the request host to derive the
    | tenant subdomain. Override per-environment via TENANCY_CENTRAL_DOMAIN.
    |
    */

    'central_domain' => env('TENANCY_CENTRAL_DOMAIN', 'kasiro.my.id'),

    /*
    |--------------------------------------------------------------------------
    | Reserved subdomains
    |--------------------------------------------------------------------------
    |
    | Labels that may never be claimed by a tenant. Enforced both at tenant
    | resolution (a request to one of these is treated as platform/404) and
    | at tenant creation (ValidSubdomain rule). Keep lowercase.
    |
    */

    'reserved_subdomains' => [
        'www', 'app', 'api', 'admin', 'mail', 'smtp', 'imap', 'pop',
        'static', 'assets', 'cdn', 'ftp', 'ns1', 'ns2', 'webmail',
        'dashboard', 'support', 'help', 'status', 'blog', 'docs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant lookup cache TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | How long a subdomain -> tenant lookup is cached. Resolution must be cheap
    | (NFR-1, < ~10ms on cache hit). Invalidation on tenant settings changes is
    | wired in a later milestone (Pengaturan Tenant).
    |
    */

    'cache_ttl' => (int) env('TENANCY_CACHE_TTL', 300),

    /*
    |--------------------------------------------------------------------------
    | Subdomain format constraints
    |--------------------------------------------------------------------------
    */

    'subdomain' => [
        'min' => 3,
        'max' => 63, // DNS label limit
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant screenshot generation
    |--------------------------------------------------------------------------
    |
    | The preview screenshot is produced by Browsershot (headless Chrome), which
    | takes up to ~60s and makes an HTTP request back to this same app at
    | /__preview. Running that inline (dispatchAfterResponse) keeps the serving
    | PHP worker busy for the whole duration and competes with normal navigation.
    |
    | When 'queue' is true the job is pushed onto the queue so a separate worker
    | (php artisan queue:work / queue:listen) runs Chrome out-of-band — the web
    | request returns immediately. `composer dev` already starts a queue:listen,
    | so this is the recommended default.
    |
    | Set TENANT_SCREENSHOT_QUEUE=false ONLY if you run the app without any queue
    | worker; the job then falls back to running after the response (the old
    | blocking behaviour) so screenshots still get generated.
    |
    */

    'screenshot' => [
        'queue' => (bool) env('TENANT_SCREENSHOT_QUEUE', true),
    ],

];
