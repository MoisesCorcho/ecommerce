<?php

declare(strict_types=1);

namespace Tests\Feature\Accounts;

use App\Actions\Users\CreateUserAction;
use App\Actions\Users\DeleteUserAction;
use App\Actions\Users\UpdateUserAction;
use App\DTOs\Users\UpsertUserDTO;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        Config::set('ecommerce.admin_emails', ['admin@example.com']);

        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_admin_can_create_user_via_filament(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Comprador Uno',
                'email' => 'comprador@example.com',
                'phone' => '+573001112233',
                'password' => 'secret-pass',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas(User::class, [
            'name' => 'Comprador Uno',
            'email' => 'comprador@example.com',
            'phone' => '+573001112233',
        ]);

        $created = User::query()->where('email', 'comprador@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue(Hash::check('secret-pass', $created->password));

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords(User::query()->where('email', 'comprador@example.com')->get());
    }

    public function test_user_required_fields_are_validated_on_create(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => null,
                'email' => null,
                'password' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'required',
                'password' => 'required',
            ]);
    }

    public function test_duplicate_email_is_rejected_on_create(): void
    {
        $this->actingAsAdmin();

        User::factory()->create([
            'email' => 'tomado@example.com',
        ]);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Otro',
                'email' => 'tomado@example.com',
                'password' => 'secret-pass',
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    public function test_update_user_keeps_password_when_empty(): void
    {
        $this->actingAsAdmin();

        $user = User::factory()->create([
            'name' => 'Original',
            'email' => 'original@example.com',
            'password' => 'old-password',
        ]);

        $originalHash = $user->password;

        Livewire::test(EditUser::class, ['record' => $user->getKey()])
            ->fillForm([
                'name' => 'Actualizado',
                'email' => 'actualizado@example.com',
                'phone' => '+57999888777',
                'password' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $user->refresh();

        $this->assertSame('Actualizado', $user->name);
        $this->assertSame('actualizado@example.com', $user->email);
        $this->assertSame('+57999888777', $user->phone);
        $this->assertSame($originalHash, $user->password);
        $this->assertTrue(Hash::check('old-password', $user->password));
    }

    public function test_update_user_changes_password_when_provided(): void
    {
        $this->actingAsAdmin();

        $user = User::factory()->create([
            'password' => 'old-password',
        ]);

        Livewire::test(EditUser::class, ['record' => $user->getKey()])
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'new-password',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $user->refresh();

        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertFalse(Hash::check('old-password', $user->password));
    }

    public function test_soft_delete_hides_user_from_default_list(): void
    {
        $this->actingAsAdmin();

        $user = User::factory()->create([
            'name' => 'A borrar',
            'email' => 'borrar@example.com',
        ]);

        app(DeleteUserAction::class)($user);

        $this->assertSoftDeleted($user);

        Livewire::test(ListUsers::class)
            ->assertCanNotSeeTableRecords([$user]);
    }

    public function test_create_user_action_requires_password(): void
    {
        $this->expectException(ValidationException::class);

        app(CreateUserAction::class)(UpsertUserDTO::fromArray([
            'name' => 'Sin pass',
            'email' => 'sinpass@example.com',
            'password' => null,
        ]));
    }

    public function test_update_user_action_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'tomado@example.com']);
        $user = User::factory()->create(['email' => 'propia@example.com']);

        $this->expectException(ValidationException::class);

        app(UpdateUserAction::class)($user, UpsertUserDTO::fromArray([
            'name' => $user->name,
            'email' => 'tomado@example.com',
        ]));
    }

    public function test_delete_user_action_soft_deletes(): void
    {
        $user = User::factory()->create();

        app(DeleteUserAction::class)($user);

        $this->assertSoftDeleted($user);
    }
}
