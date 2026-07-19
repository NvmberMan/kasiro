<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordChangeLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_request_a_password_change_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/profile')
            ->post(route('password.change-link'));

        $response->assertRedirect('/profile');
        $response->assertSessionHas('status', 'password-change-link-sent');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_link_can_be_opened_while_logged_in(): void
    {
        $user = User::factory()->create();

        // A logged-in user opening the change-password link must reach the form,
        // not be bounced away by guest-only middleware.
        $this->actingAs($user)
            ->get(route('password.reset', ['token' => 'some-token']))
            ->assertOk();
    }
}
