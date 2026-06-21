<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private function google2fa(): Google2FA
    {
        return app(Google2FA::class);
    }

    private function enabledUser(array $recoveryCodes = ['AAAAA-BBBBB', 'CCCCC-DDDDD']): array
    {
        $secret = $this->google2fa()->generateSecretKey();
        $user = User::factory()->create();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return [$user, $secret];
    }

    public function test_user_can_enable_and_confirm_two_factor(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('two-factor.enable'))->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertFalse($user->hasTwoFactorEnabled());

        $code = $this->google2fa()->getCurrentOtp($user->two_factor_secret);
        $this->actingAs($user)->post(route('two-factor.confirm'), ['code' => $code])->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertCount(8, $user->recoveryCodes());
    }

    public function test_confirm_rejects_an_invalid_code(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('two-factor.enable'));

        $this->actingAs($user)
            ->post(route('two-factor.confirm'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_login_with_two_factor_redirects_to_challenge_then_completes(): void
    {
        [$user, $secret] = $this->enabledUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('two-factor.login'));

        $code = $this->google2fa()->getCurrentOtp($secret);
        $response = $this->post(route('two-factor.login.store'), ['code' => $code]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_recovery_code_completes_login_and_is_consumed(): void
    {
        [$user] = $this->enabledUser(['AAAAA-BBBBB', 'CCCCC-DDDDD']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post(route('two-factor.login.store'), ['recovery_code' => 'AAAAA-BBBBB']);

        $this->assertAuthenticatedAs($user);
        $this->assertNotContains('AAAAA-BBBBB', $user->fresh()->recoveryCodes());
        $this->assertContains('CCCCC-DDDDD', $user->fresh()->recoveryCodes());
    }

    public function test_challenge_rejects_invalid_code(): void
    {
        [$user] = $this->enabledUser();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post(route('two-factor.login.store'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_user_can_disable_two_factor(): void
    {
        [$user] = $this->enabledUser();

        $this->actingAs($user)->delete(route('two-factor.disable'))->assertRedirect();

        $user->refresh();
        $this->assertFalse($user->hasTwoFactorEnabled());
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
    }
}
