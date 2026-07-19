<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_oauth_user_without_password_is_redirected_to_complete_profile(): void
    {
        $user = User::factory()->create(['password' => null, 'google_id' => 'g-1']);

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('profile.complete'));
        $this->actingAs($user)->get('/profile')->assertRedirect(route('profile.complete'));
    }

    public function test_user_with_password_is_not_redirected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    public function test_complete_profile_page_renders_for_incomplete_account(): void
    {
        $user = User::factory()->create(['password' => null, 'google_id' => 'g-1']);

        $this->actingAs($user)->get(route('profile.complete'))->assertOk();
    }

    public function test_complete_profile_page_redirects_when_already_complete(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.complete'))->assertRedirect(route('dashboard'));
    }

    public function test_setting_a_password_completes_the_profile(): void
    {
        $user = User::factory()->create(['password' => null, 'google_id' => 'g-1', 'name' => 'Old Name']);

        $response = $this->actingAs($user)->post(route('profile.complete.store'), [
            'name' => 'New Name',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertSame('New Name', $user->name);

        // No longer funneled to the completion page.
        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    public function test_completion_requires_a_confirmed_password(): void
    {
        $user = User::factory()->create(['password' => null, 'google_id' => 'g-1']);

        $this->actingAs($user)
            ->from(route('profile.complete'))
            ->post(route('profile.complete.store'), [
                'name' => 'New Name',
                'password' => 'new-password',
                'password_confirmation' => 'mismatch',
            ])
            ->assertSessionHasErrors('password');

        $this->assertNull($user->fresh()->password);
    }
}
