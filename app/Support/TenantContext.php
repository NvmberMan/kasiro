<?php

namespace App\Support;

use App\Models\Tenant;

/**
 * Holds the tenant resolved for the current request.
 *
 * Bound as a singleton in the container (one instance per request lifecycle),
 * so it must be resolved via the container — never instantiated ad-hoc — to
 * guarantee every consumer sees the same active tenant. The BelongsToTenant
 * global scope reads from this context to isolate data.
 */
class TenantContext
{
    protected ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function has(): bool
    {
        return $this->tenant !== null;
    }

    public function forget(): void
    {
        $this->tenant = null;
    }
}
