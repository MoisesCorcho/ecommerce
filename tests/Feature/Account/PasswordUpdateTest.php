<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_valid_password_change_updates_hash(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPass123!')]);

        $this->actingAs($user);

        Livewire::test('profile-page')
            ->set('currentPassword', 'CurrentPass123!')
            ->set('newPassword', 'BrandNewPass123!')
            ->set('newPassword_confirmation', 'BrandNewPass123!')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('BrandNewPass123!', $user->fresh()->password));
    }

    public function test_incorrect_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPass123!')]);

        $this->actingAs($user);

        Livewire::test('profile-page')
            ->set('currentPassword', 'WrongPassword!')
            ->set('newPassword', 'BrandNewPass123!')
            ->set('newPassword_confirmation', 'BrandNewPass123!')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        $this->assertTrue(Hash::check('CurrentPass123!', $user->fresh()->password));
    }

    public function test_new_password_without_matching_confirmation_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('CurrentPass123!')]);

        $this->actingAs($user);

        Livewire::test('profile-page')
            ->set('currentPassword', 'CurrentPass123!')
            ->set('newPassword', 'BrandNewPass123!')
            ->set('newPassword_confirmation', 'DoesNotMatch!')
            ->call('updatePassword')
            ->assertHasErrors(['new_password']);

        $this->assertTrue(Hash::check('CurrentPass123!', $user->fresh()->password));
    }
}
