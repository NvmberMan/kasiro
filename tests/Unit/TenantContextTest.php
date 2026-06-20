<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Support\TenantContext;
use PHPUnit\Framework\TestCase;

class TenantContextTest extends TestCase
{
    public function test_starts_empty(): void
    {
        $context = new TenantContext;

        $this->assertFalse($context->has());
        $this->assertNull($context->get());
        $this->assertNull($context->id());
    }

    public function test_set_and_get(): void
    {
        $tenant = new Tenant(['name' => 'Acme']);
        $tenant->id = 42;

        $context = new TenantContext;
        $context->set($tenant);

        $this->assertTrue($context->has());
        $this->assertSame($tenant, $context->get());
        $this->assertSame(42, $context->id());
    }

    public function test_forget_clears_context(): void
    {
        $tenant = new Tenant;
        $tenant->id = 7;

        $context = new TenantContext;
        $context->set($tenant);
        $context->forget();

        $this->assertFalse($context->has());
        $this->assertNull($context->get());
        $this->assertNull($context->id());
    }
}
