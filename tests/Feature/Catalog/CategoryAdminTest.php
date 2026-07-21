<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Actions\Categories\CreateCategoryAction;
use App\Actions\Categories\DeleteCategoryAction;
use App\Actions\Categories\UpdateCategoryAction;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryAdminTest extends TestCase
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

    public function test_admin_can_create_category_via_filament(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'Bolsos',
                'slug' => null,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas(Category::class, [
            'name' => 'Bolsos',
            'slug' => 'bolsos',
        ]);

        Livewire::test(ListCategories::class)
            ->assertCanSeeTableRecords(Category::query()->where('slug', 'bolsos')->get());
    }

    public function test_category_name_is_required(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_category_slug_syncs_live_from_name_until_manually_edited(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'Bolsos de mano',
                'sort_order' => 0,
            ])
            ->assertFormSet([
                'slug' => 'bolsos-de-mano',
            ])
            ->fillForm([
                'name' => 'Bolsos de viaje',
            ])
            ->assertFormSet([
                'slug' => 'bolsos-de-viaje',
            ])
            ->fillForm([
                'slug' => 'mi-slug',
            ])
            ->fillForm([
                'name' => 'Otra categoría',
            ])
            ->assertFormSet([
                'slug' => 'mi-slug',
            ]);
    }

    public function test_admin_can_update_category(): void
    {
        $this->actingAsAdmin();

        $category = Category::factory()->create([
            'name' => 'Original',
            'slug' => 'original',
        ]);

        Livewire::test(EditCategory::class, ['record' => $category->getKey()])
            ->fillForm([
                'name' => 'Actualizada',
                'slug' => 'actualizada',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Category::class, [
            'id' => $category->id,
            'name' => 'Actualizada',
            'slug' => 'actualizada',
        ]);
    }

    public function test_duplicate_slug_is_rejected_on_update(): void
    {
        $this->actingAsAdmin();

        Category::factory()->create(['slug' => 'tomada']);
        $category = Category::factory()->create(['slug' => 'propia']);

        $this->expectException(ValidationException::class);

        app(UpdateCategoryAction::class)($category, [
            'slug' => 'tomada',
        ]);
    }

    public function test_delete_category_nulls_product_association(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        app(DeleteCategoryAction::class)($category);

        $this->assertDatabaseMissing(Category::class, ['id' => $category->id]);
        $this->assertNull($product->fresh()->category_id);
    }

    public function test_create_category_action_generates_slug(): void
    {
        $category = app(CreateCategoryAction::class)([
            'name' => 'Cuero Premium',
            'slug' => null,
        ]);

        $this->assertSame('cuero-premium', $category->slug);
    }
}
