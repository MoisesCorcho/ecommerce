<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountAuthGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_guest_is_redirected_to_login_from_every_account_route(): void
    {
        $order = Order::factory()->paid()->create();

        $this->get(route('profile'))->assertRedirect(route('login'));
        $this->get(route('profile.addresses'))->assertRedirect(route('login'));
        $this->get(route('profile.orders'))->assertRedirect(route('login'));
        $this->get(route('profile.orders.show', $order))->assertRedirect(route('login'));
        $this->get(route('profile.reviews'))->assertRedirect(route('login'));
    }

    public function test_unverified_user_can_still_edit_profile(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        Livewire::test('profile-page')
            ->assertOk()
            ->set('name', 'Updated Name')
            ->set('email', $user->email)
            ->set('phone', '')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->assertSame('Updated Name', $user->fresh()->name);
    }
}
