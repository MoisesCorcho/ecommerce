<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AddressBookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_valid_address_can_be_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('profile-addresses-page')
            ->call('createNew')
            ->set('fullName', 'Ada Lovelace')
            ->set('phone', '+573001234567')
            ->set('addressLine1', 'Calle 123')
            ->set('city', 'Bogotá')
            ->set('state', 'Bogotá D.C.')
            ->set('country', 'CO')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'full_name' => 'Ada Lovelace',
        ]);
    }

    public function test_valid_edit_updates_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create(['city' => 'Cali']);

        $this->actingAs($user);

        Livewire::test('profile-addresses-page')
            ->call('edit', $address->id)
            ->set('city', 'Medellín')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Medellín', $address->fresh()->city);
    }

    public function test_marking_default_clears_previous_default(): void
    {
        $user = User::factory()->create();
        $oldDefault = Address::factory()->for($user)->default()->create();
        $newDefault = Address::factory()->for($user)->create(['is_default' => false]);

        $this->actingAs($user);

        Livewire::test('profile-addresses-page')
            ->call('makeDefault', $newDefault->id);

        $this->assertTrue($newDefault->fresh()->is_default);
        $this->assertFalse($oldDefault->fresh()->is_default);
    }

    public function test_deleting_the_only_default_does_not_reassign_another(): void
    {
        $user = User::factory()->create();
        $default = Address::factory()->for($user)->default()->create();
        $other = Address::factory()->for($user)->create(['is_default' => false]);

        $this->actingAs($user);

        Livewire::test('profile-addresses-page')
            ->call('delete', $default->id);

        $this->assertDatabaseMissing('addresses', ['id' => $default->id]);
        $this->assertFalse($other->fresh()->is_default);
    }

    public function test_invalid_address_data_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('profile-addresses-page')
            ->call('createNew')
            ->set('fullName', '')
            ->set('phone', '')
            ->set('addressLine1', '')
            ->set('city', '')
            ->set('state', '')
            ->call('save')
            ->assertHasErrors(['full_name', 'phone', 'address_line_1', 'city', 'state']);

        $this->assertSame(0, Address::query()->count());
    }

    public function test_user_cannot_edit_or_delete_foreign_address(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $address = Address::factory()->for($owner)->create();

        $this->actingAs($stranger);

        Livewire::test('profile-addresses-page')
            ->call('edit', $address->id)
            ->assertForbidden();

        Livewire::test('profile-addresses-page')
            ->call('delete', $address->id)
            ->assertForbidden();

        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }
}
