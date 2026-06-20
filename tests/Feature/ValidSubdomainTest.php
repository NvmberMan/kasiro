<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Rules\ValidSubdomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidSubdomainTest extends TestCase
{
    use RefreshDatabase;

    private function passes(string $value, ?int $ignore = null): bool
    {
        return Validator::make(
            ['subdomain' => $value],
            ['subdomain' => [new ValidSubdomain($ignore)]]
        )->passes();
    }

    public function test_accepts_valid_subdomains(): void
    {
        $this->assertTrue($this->passes('warungbudi'));
        $this->assertTrue($this->passes('toko-kopi-1'));
        $this->assertTrue($this->passes('abc'));
    }

    public function test_rejects_invalid_format(): void
    {
        $this->assertFalse($this->passes('Warung_Budi'));   // uppercase + underscore
        $this->assertFalse($this->passes('-leading'));       // leading hyphen
        $this->assertFalse($this->passes('trailing-'));      // trailing hyphen
        $this->assertFalse($this->passes('has space'));      // space
        $this->assertFalse($this->passes('ab'));             // too short (< 3)
    }

    public function test_rejects_reserved_subdomains(): void
    {
        $this->assertFalse($this->passes('www'));
        $this->assertFalse($this->passes('admin'));
        $this->assertFalse($this->passes('api'));
    }

    public function test_rejects_duplicate_subdomain(): void
    {
        Tenant::factory()->subdomain('taken')->create();

        $this->assertFalse($this->passes('taken'));
    }

    public function test_ignores_given_tenant_id_for_uniqueness(): void
    {
        $tenant = Tenant::factory()->subdomain('mystore')->create();

        // Same subdomain is allowed when editing the owning tenant.
        $this->assertTrue($this->passes('mystore', $tenant->id));
    }
}
