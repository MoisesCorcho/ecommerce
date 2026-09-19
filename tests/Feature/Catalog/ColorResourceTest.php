<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Filament\Resources\Colors\Pages\CreateColor;
use App\Filament\Resources\Colors\Pages\EditColor;
use App\Filament\Resources\Colors\Pages\ListColors;
use App\Models\Color;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ColorResourceTest extends TestCase
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

    public function test_admin_can_list_colors(): void
    {
        $this->actingAsAdmin();

        $colors = Color::factory()->count(3)->create();

        Livewire::test(ListColors::class)
            ->assertCanSeeTableRecords($colors);
    }

    public function test_admin_can_create_color_with_hex(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateColor::class)
            ->fillForm([
                'name' => 'Verde Botella',
                'slug' => 'verde-botella',
                'hex_code' => '#1B4D3E',
                'sort_order' => 5,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('colors', [
            'name' => 'Verde Botella',
            'slug' => 'verde-botella',
            'hex_code' => '#1B4D3E',
        ]);
    }

    public function test_admin_can_edit_color(): void
    {
        $this->actingAsAdmin();

        $color = Color::factory()->create([
            'name' => 'Terracota Original',
            'hex_code' => '#CC4E33',
        ]);

        Livewire::test(EditColor::class, ['record' => $color->getRouteKey()])
            ->fillForm([
                'name' => 'Terracota Modificado',
                'hex_code' => '#E05A3E',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('colors', [
            'id' => $color->id,
            'name' => 'Terracota Modificado',
            'hex_code' => '#E05A3E',
        ]);
    }
}
