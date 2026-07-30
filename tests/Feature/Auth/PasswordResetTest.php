<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_request_does_not_reveal_whether_email_exists(): void
    {
        Notification::fake();

        $existing = User::factory()->create();

        $known = Livewire::test('forgot-password-page')
            ->set('email', $existing->email)
            ->call('sendResetLink')
            ->get('statusMessage');

        $unknown = Livewire::test('forgot-password-page')
            ->set('email', 'nobody@example.com')
            ->call('sendResetLink')
            ->get('statusMessage');

        $this->assertSame($known, $unknown);
        Notification::assertSentTo($existing, ResetPassword::class);
    }

    public function test_valid_token_resets_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPassword123!')]);
        $token = Password::createToken($user);

        Livewire::test('reset-password-page', ['token' => $token])
            ->set('email', $user->email)
            ->set('password', 'NewPassword123!')
            ->set('password_confirmation', 'NewPassword123!')
            ->call('resetPassword')
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPassword123!')]);

        $component = Livewire::test('reset-password-page', ['token' => 'not-a-real-token'])
            ->set('email', $user->email)
            ->set('password', 'NewPassword123!')
            ->set('password_confirmation', 'NewPassword123!')
            ->call('resetPassword');

        $this->assertNotNull($component->get('errorMessage'));
        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));
    }
}
