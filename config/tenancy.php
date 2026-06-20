<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Central (apex) domain
    |--------------------------------------------------------------------------
    |
    | The root domain that serves the platform itself (landing, auth, dashboard).
    | Tenants live on subdomains of this domain, e.g. "warungbudi.kasiro.com".
    | Resolution strips this suffix from the request host to derive the
    | tenant subdomain. Override per-environment via TENANCY_CENTRAL_DOMAIN.
    |
    */

    'central_domain' => env('TENANCY_CENTRAL_DOMAIN', 'kasiro.com'),

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

];
