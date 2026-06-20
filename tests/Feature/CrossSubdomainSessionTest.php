<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossSubdomainSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_domain_covers_all_subdomains(): void
    {
        $this->assertSame('.kasiro.com', config('session.domain'));
    }

    public function test_session_same_site_is_lax(): void
    {
        $this->assertSame('lax', config('session.same_site'));
    }

    public function test_login_at_apex_sets_session_cookie_with_root_domain(): void
    {
        $user = User::factory()->create();

        $response = $this->post('http://kasiro.com/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();

        $sessionCookie = collect($response->headers->getCookies())
            ->first(fn ($cookie) => str_ends_with($cookie->getName(), '_session'));

        $this->assertNotNull($sessionCookie, 'No session cookie found in login response');
        $this->assertSame('.kasiro.com', $sessionCookie->getDomain());
    }

    public function test_session_from_apex_login_transfers_to_tenant_subdomain(): void
    {
        $user = User::factory()->create();
        Tenant::factory()->subdomain('warungbudi')->create(['name' => 'Warung Budi']);

        // Login at apex platform
        $this->post('http://kasiro.com/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();

        // The same session cookie (Domain=.kasiro.com) is carried to the subdomain
        $this->get('http://warungbudi.kasiro.com/');

        $this->assertAuthenticatedAs($user);
    }
}
