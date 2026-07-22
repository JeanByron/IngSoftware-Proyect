<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactor\TwoFactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RNF-19: Segundo Factor de Autenticación (2FA) por TOTP.
 */
class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    /** Un usuario con 2FA activo y confirmado. */
    private function userWithTwoFactor(string $secret): User
    {
        $user = User::factory()->create();
        $user->forceFill([
            'two_factor_secret'       => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $user;
    }

    // --- Activación / confirmación / desactivación ---

    public function test_enabling_generates_an_unconfirmed_secret(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('2fa.enable'))->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertFalse($user->hasTwoFactorEnabled());   // aún no confirmado
    }

    public function test_confirming_with_a_valid_code_enables_two_factor(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('2fa.enable'));
        $user->refresh();

        $code = TwoFactor::currentCode($user->two_factor_secret);

        $this->actingAs($user)->post(route('2fa.confirm'), ['code' => $code])
            ->assertSessionHasNoErrors();

        $this->assertTrue($user->refresh()->hasTwoFactorEnabled());
    }

    public function test_confirming_with_a_wrong_code_fails(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('2fa.enable'));
        $user->refresh();

        $real = TwoFactor::currentCode($user->two_factor_secret);
        $bad  = $real === '000000' ? '111111' : '000000';

        $this->actingAs($user)->post(route('2fa.confirm'), ['code' => $bad])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->refresh()->hasTwoFactorEnabled());
    }

    public function test_disabling_clears_two_factor(): void
    {
        $user = $this->userWithTwoFactor(TwoFactor::generateSecret());

        $this->actingAs($user)->delete(route('2fa.disable'))->assertRedirect();

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertFalse($user->hasTwoFactorEnabled());
    }

    // --- Login con y sin 2FA ---

    public function test_login_without_two_factor_authenticates_normally(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_two_factor_requires_the_challenge(): void
    {
        $user = $this->userWithTwoFactor(TwoFactor::generateSecret());

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('2fa.challenge'));

        // La contraseña sola NO autentica: aún es invitado.
        $this->assertGuest();
    }

    public function test_challenge_with_correct_code_logs_in(): void
    {
        $secret = TwoFactor::generateSecret();
        $user   = $this->userWithTwoFactor($secret);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->post(route('2fa.challenge'), ['code' => TwoFactor::currentCode($secret)])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_challenge_with_wrong_code_stays_out(): void
    {
        $secret = TwoFactor::generateSecret();
        $user   = $this->userWithTwoFactor($secret);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $real = TwoFactor::currentCode($secret);
        $bad  = $real === '000000' ? '111111' : '000000';

        $this->post(route('2fa.challenge'), ['code' => $bad])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }
}
