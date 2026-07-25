<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleAndAdminBackfillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
        $this->seed(RoleAndAdminBackfillSeeder::class);
    }

    public function test_register_is_rate_limited_per_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            Livewire::test('register-page')
                ->set('name', 'Grace Hopper')
                ->set('email', "grace{$i}@example.com")
                ->set('password', 'Password123!')
                ->set('password_confirmation', 'Password123!')
                ->set('terms', true)
                ->call('register');
        }

        $component = Livewire::test('register-page')
            ->set('name', 'Grace Hopper')
            ->set('email', 'grace-blocked@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('terms', true)
            ->call('register');

        $this->assertStringContainsString('Too many', (string) $component->get('errorMessage'));
        $this->assertSame(0, User::query()->where('email', 'grace-blocked@example.com')->count());
    }

    public function test_password_reset_request_is_rate_limited_per_ip(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            Livewire::test('forgot-password-page')
                ->set('email', $user->email)
                ->call('sendResetLink');
        }

        $component = Livewire::test('forgot-password-page')
            ->set('email', $user->email)
            ->call('sendResetLink');

        $this->assertStringContainsString('Too many', (string) $component->get('errorMessage'));
    }

    public function test_verify_email_resend_is_rate_limited_per_user(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        for ($i = 0; $i < 3; $i++) {
            Livewire::test('verify-email-notice')->call('resend');
        }

        $component = Livewire::test('verify-email-notice')->call('resend');

        $this->assertStringContainsString('Too many', (string) $component->get('errorMessage'));
    }

    public function test_register_rejects_password_below_policy_minimum(): void
    {
        Livewire::test('register-page')
            ->set('name', 'Grace Hopper')
            ->set('email', 'short-password@example.com')
            ->set('password', 'short1')
            ->set('password_confirmation', 'short1')
            ->set('terms', true)
            ->call('register')
            ->assertHasErrors(['password']);

        $this->assertSame(0, User::query()->where('email', 'short-password@example.com')->count());
    }
}
