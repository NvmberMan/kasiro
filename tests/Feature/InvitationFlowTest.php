<?php

namespace Tests\Feature;

use App\Actions\CreateInvitation;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner  = User::factory()->create();
        $this->tenant = Tenant::factory()->create(['owner_id' => $this->owner->id]);
        $this->tenant->users()->attach($this->owner, ['role' => 'owner', 'status' => 'active']);
    }

    private function tenantUrl(string $path): string
    {
        return "http://{$this->tenant->subdomain}.kasiro.my.id{$path}";
    }

    private function platformUrl(string $path): string
    {
        return "http://kasiro.my.id{$path}";
    }

    public function test_owner_can_create_invitation(): void
    {
        $this->actingAs($this->owner)
            ->post($this->tenantUrl('/invitations'), [
                'email' => 'kasir@example.com',
                'role'  => 'cashier',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tenant_invitations', [
            'tenant_id' => $this->tenant->id,
            'email'     => 'kasir@example.com',
            'role'      => 'cashier',
        ]);
    }

    public function test_cashier_cannot_create_invitation(): void
    {
        $cashier = User::factory()->create();
        $this->tenant->users()->attach($cashier, ['role' => 'cashier', 'status' => 'active']);

        $this->actingAs($cashier)
            ->post($this->tenantUrl('/invitations'), [
                'email' => 'someone@example.com',
                'role'  => 'cashier',
            ])
            ->assertForbidden();
    }

    public function test_existing_member_cannot_be_invited_again(): void
    {
        $existing = User::factory()->create(['email' => 'existing@example.com']);
        $this->tenant->users()->attach($existing, ['role' => 'cashier', 'status' => 'active']);

        $this->actingAs($this->owner)
            ->post($this->tenantUrl('/invitations'), [
                'email' => 'existing@example.com',
                'role'  => 'cashier',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_logged_in_user_can_accept_invitation(): void
    {
        $newUser = User::factory()->create();
        $plain   = app(CreateInvitation::class)->handle($this->tenant, $newUser->email, 'cashier', $this->owner);

        $this->actingAs($newUser)
            ->post($this->platformUrl("/invitations/{$plain}/accept"))
            ->assertRedirect();

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $this->tenant->id,
            'user_id'   => $newUser->id,
            'role'      => 'cashier',
            'status'    => 'active',
        ]);
    }

    public function test_invitation_is_marked_accepted_after_use(): void
    {
        $newUser = User::factory()->create();
        $plain   = app(CreateInvitation::class)->handle($this->tenant, $newUser->email, 'manager', $this->owner);

        $this->actingAs($newUser)
            ->post($this->platformUrl("/invitations/{$plain}/accept"));

        $this->assertNotNull(
            TenantInvitation::where('token', hash('sha256', $plain))->first()?->accepted_at
        );
    }

    public function test_expired_invitation_cannot_be_accepted(): void
    {
        $newUser = User::factory()->create();
        $plain   = app(CreateInvitation::class)->handle($this->tenant, $newUser->email, 'cashier', $this->owner);

        TenantInvitation::where('token', hash('sha256', $plain))
            ->update(['expires_at' => now()->subDay()]);

        $this->actingAs($newUser)
            ->post($this->platformUrl("/invitations/{$plain}/accept"))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('tenant_user', [
            'tenant_id' => $this->tenant->id,
            'user_id'   => $newUser->id,
        ]);
    }

    public function test_used_invitation_cannot_be_reused(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $plain = app(CreateInvitation::class)->handle($this->tenant, $user1->email, 'cashier', $this->owner);

        $this->actingAs($user1)->post($this->platformUrl("/invitations/{$plain}/accept"));

        $this->actingAs($user2)
            ->post($this->platformUrl("/invitations/{$plain}/accept"))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('tenant_user', [
            'tenant_id' => $this->tenant->id,
            'user_id'   => $user2->id,
        ]);
    }

    public function test_owner_can_cancel_pending_invitation(): void
    {
        $this->actingAs($this->owner)
            ->post($this->tenantUrl('/invitations'), [
                'email' => 'tobecancelled@example.com',
                'role'  => 'cashier',
            ]);

        $inv = TenantInvitation::where('email', 'tobecancelled@example.com')->first();

        $this->actingAs($this->owner)
            ->delete($this->tenantUrl("/invitations/{$inv->id}"))
            ->assertRedirect();

        $this->assertDatabaseMissing('tenant_invitations', ['id' => $inv->id]);
    }

    public function test_show_invitation_page_with_valid_token(): void
    {
        $newUser = User::factory()->create();
        $plain   = app(CreateInvitation::class)->handle($this->tenant, $newUser->email, 'cashier', $this->owner);

        $this->actingAs($newUser)
            ->get($this->platformUrl("/invitations/{$plain}"))
            ->assertOk()
            ->assertSee($this->tenant->name);
    }

    public function test_invalid_token_redirects_with_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get($this->platformUrl('/invitations/bogustoken123'))
            ->assertRedirect(route('dashboard'));
    }
}
