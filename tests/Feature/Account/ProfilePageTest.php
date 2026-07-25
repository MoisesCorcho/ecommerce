<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_account_sub_nav_links_to_every_section(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('profile-page')
            ->assertSeeHtml(route('profile.addresses'))
            ->assertSeeHtml(route('profile.orders'))
            ->assertSeeHtml(route('profile.reviews'));
    }

    public function test_valid_edit_updates_name_and_phone(): void
    {
        $user = User::factory()->create(['name' => 'Old Name', 'phone' => '3000000000']);

        $this->actingAs($user);

        Livewire::test('profile-page')
            ->set('name', 'New Name')
            ->set('email', $user->email)
            ->set('phone', '3111111111')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('3111111111', $user->phone);
    }

    public function test_changing_email_marks_it_unverified_and_resends_verification(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);
        Notification::fake();

        $this->actingAs($user);

        Livewire::test('profile-page')
            ->set('name', $user->name)
            ->set('email', 'new@example.com')
            ->set('phone', $user->phone ?? '')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_email_already_used_by_another_account_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'mine@example.com']);
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($user);

        Livewire::test('profile-page')
            ->set('name', $user->name)
            ->set('email', 'taken@example.com')
            ->set('phone', '')
            ->call('updateProfile')
            ->assertHasErrors(['email']);

        $this->assertSame('mine@example.com', $user->fresh()->email);
    }

    public function test_invalid_data_is_rejected(): void
    {
        $user = User::factory()->create(['name' => 'Keep Me']);

        $this->actingAs($user);

        Livewire::test('profile-page')
            ->set('name', '')
            ->set('email', 'not-an-email')
            ->set('phone', '')
            ->call('updateProfile')
            ->assertHasErrors(['name', 'email']);

        $this->assertSame('Keep Me', $user->fresh()->name);
    }
}
