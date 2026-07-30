<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleAndAdminBackfillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
        $this->seed(RoleAndAdminBackfillSeeder::class);
    }

    public function test_valid_registration_creates_customer_and_logs_in(): void
    {
        Livewire::test('register-page')
            ->set('name', 'Grace Hopper')
            ->set('email', 'grace@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('terms', true)
            ->call('register')
            ->assertRedirect(route('home'));

        $user = User::query()->where('email', 'grace@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('customer'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue(Auth::check());
        $this->assertSame($user->id, Auth::id());
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'grace@example.com']);

        Livewire::test('register-page')
            ->set('name', 'Grace Hopper')
            ->set('email', 'grace@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('terms', true)
            ->call('register')
            ->assertHasErrors(['email']);

        $this->assertSame(1, User::query()->where('email', 'grace@example.com')->count());
    }

    public function test_password_confirmation_mismatch_is_rejected(): void
    {
        Livewire::test('register-page')
            ->set('name', 'Grace Hopper')
            ->set('email', 'grace2@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'different')
            ->set('terms', true)
            ->call('register')
            ->assertHasErrors(['password']);

        $this->assertSame(0, User::query()->where('email', 'grace2@example.com')->count());
    }

    public function test_without_accepting_terms_is_rejected(): void
    {
        Livewire::test('register-page')
            ->set('name', 'Grace Hopper')
            ->set('email', 'grace3@example.com')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('terms', false)
            ->call('register')
            ->assertHasErrors(['terms']);

        $this->assertSame(0, User::query()->where('email', 'grace3@example.com')->count());
    }
}
