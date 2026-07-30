<?php

declare(strict_types=1);

namespace Tests\Feature\Accounts;

use App\Actions\Addresses\CreateAddressAction;
use App\Actions\Addresses\DeleteAddressAction;
use App\Actions\Addresses\UpdateAddressAction;
use App\DTOs\Addresses\UpsertAddressDTO;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\RelationManagers\AddressesRelationManager;
use App\Models\Address;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AddressAdminTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        Config::set('ecommerce.admin_emails', ['admin@example.com']);

        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
        Role::findOrCreate('admin', 'web');
        $user->assignRole('admin');

        $this->actingAs($user);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function validAddressPayload(array $overrides = []): array
    {
        return array_merge([
            'label' => 'Casa',
            'full_name' => 'Ana Pérez',
            'phone' => '+573001112233',
            'address_line_1' => 'Calle 10 #20-30',
            'address_line_2' => 'Apto 401',
            'city' => 'Medellín',
            'state' => 'Antioquia',
            'country' => 'CO',
            'postal_code' => '050001',
            'is_default' => false,
        ], $overrides);
    }

    public function test_admin_can_create_edit_and_delete_address_via_relation_manager(): void
    {
        $this->actingAsAdmin();

        $owner = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        // Create address: mount action, fill data, then execute
        $component = Livewire::test(AddressesRelationManager::class, [
            'ownerRecord' => $owner,
            'pageClass' => EditUser::class,
        ]);

        $component->mountAction(TestAction::make(CreateAction::class)->table());

        foreach ($this->validAddressPayload() as $key => $value) {
            $component->set("mountedActions.0.data.{$key}", $value);
        }

        $component
            ->callMountedAction()
            ->assertNotified()
            ->assertHasNoActionErrors();

        $address = Address::query()->where('user_id', $owner->id)->first();
        $this->assertNotNull($address);
        $this->assertSame('Ana Pérez', $address->full_name);
        $this->assertSame('Medellín', $address->city);

        $editComponent = Livewire::test(AddressesRelationManager::class, [
            'ownerRecord' => $owner,
            'pageClass' => EditUser::class,
        ]);

        $editComponent->mountAction(TestAction::make(EditAction::class)->table($address));

        foreach ($this->validAddressPayload([
            'full_name' => 'Ana Actualizada',
            'city' => 'Bogotá',
        ]) as $key => $value) {
            $editComponent->set("mountedActions.0.data.{$key}", $value);
        }

        $editComponent
            ->callMountedAction()
            ->assertNotified()
            ->assertHasNoActionErrors();

        $address->refresh();
        $this->assertSame('Ana Actualizada', $address->full_name);
        $this->assertSame('Bogotá', $address->city);

        Livewire::test(AddressesRelationManager::class, [
            'ownerRecord' => $owner,
            'pageClass' => EditUser::class,
        ])
            ->callAction(TestAction::make(DeleteAction::class)->table($address))
            ->assertNotified();

        $this->assertDatabaseMissing(Address::class, ['id' => $address->id]);
    }

    public function test_marking_default_unsets_previous_default_for_same_user(): void
    {
        $owner = User::factory()->create();

        $first = app(CreateAddressAction::class)(UpsertAddressDTO::fromArray($this->validAddressPayload([
            'user_id' => $owner->id,
            'full_name' => 'Primera',
            'is_default' => true,
        ])));

        $second = app(CreateAddressAction::class)(UpsertAddressDTO::fromArray($this->validAddressPayload([
            'user_id' => $owner->id,
            'full_name' => 'Segunda',
            'is_default' => true,
        ])));

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);

        app(UpdateAddressAction::class)($first, UpsertAddressDTO::fromArray($this->validAddressPayload([
            'user_id' => $owner->id,
            'full_name' => 'Primera',
            'is_default' => true,
        ])));

        $this->assertTrue($first->fresh()->is_default);
        $this->assertFalse($second->fresh()->is_default);
    }

    public function test_invalid_country_is_rejected(): void
    {
        $owner = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateAddressAction::class)(UpsertAddressDTO::fromArray($this->validAddressPayload([
            'user_id' => $owner->id,
            'country' => 'COL',
        ])));
    }

    public function test_required_address_fields_are_validated_by_action(): void
    {
        $owner = User::factory()->create();

        try {
            app(CreateAddressAction::class)(UpsertAddressDTO::fromArray([
                'user_id' => $owner->id,
                'full_name' => '',
                'phone' => '',
                'address_line_1' => '',
                'city' => '',
                'state' => '',
                'country' => '',
            ]));
            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('full_name', $exception->errors());
            $this->assertArrayHasKey('phone', $exception->errors());
            $this->assertArrayHasKey('address_line_1', $exception->errors());
            $this->assertArrayHasKey('city', $exception->errors());
            $this->assertArrayHasKey('state', $exception->errors());
            $this->assertArrayHasKey('country', $exception->errors());
        }
    }

    public function test_delete_address_action_removes_record(): void
    {
        $address = Address::factory()->create();

        app(DeleteAddressAction::class)($address);

        $this->assertDatabaseMissing(Address::class, ['id' => $address->id]);
    }

    public function test_invalid_country_rejected_via_relation_manager_form(): void
    {
        $this->actingAsAdmin();

        $owner = User::factory()->create();

        $component = Livewire::test(AddressesRelationManager::class, [
            'ownerRecord' => $owner,
            'pageClass' => EditUser::class,
        ]);

        $component->mountAction(TestAction::make(CreateAction::class)->table());

        foreach ($this->validAddressPayload([
            'country' => 'COL',
        ]) as $key => $value) {
            $component->set("mountedActions.0.data.{$key}", $value);
        }

        $component
            ->callMountedAction()
            ->assertHasActionErrors(['country']);
    }
}
