<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoriesGridTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');
    }

    public function test_categories_grid_renders_section_heading_and_category_names_when_categories_exist(): void
    {
        $category = Category::factory()->create([
            'name' => 'Handbags',
            'slug' => 'handbags',
            'sort_order' => 1,
        ]);

        Livewire::test('categories-grid')
            ->assertOk()
            ->assertSee(__('storefront.home.categories'), false)
            ->assertSee($category->name, false);
    }

    public function test_categories_grid_renders_links_to_products_filtered_by_category(): void
    {
        $category = Category::factory()->create([
            'name' => 'Totes',
            'slug' => 'totes',
            'sort_order' => 2,
        ]);

        $expectedUrl = route('products.index', ['category' => $category->slug]);

        Livewire::test('categories-grid')
            ->assertOk()
            ->assertSee($expectedUrl, false);
    }

    public function test_categories_grid_orders_categories_by_sort_order_then_name(): void
    {
        Category::factory()->create(['name' => 'Zebra', 'slug' => 'zebra', 'sort_order' => 1]);
        Category::factory()->create(['name' => 'Alpha', 'slug' => 'alpha', 'sort_order' => 1]);
        Category::factory()->create(['name' => 'Middle', 'slug' => 'middle', 'sort_order' => 0]);

        $response = Livewire::test('categories-grid')->assertOk();

        $middlePos = strpos($response->html(), 'Middle');
        $alphaPos = strpos($response->html(), 'Alpha');
        $zebraPos = strpos($response->html(), 'Zebra');

        $this->assertNotFalse($middlePos);
        $this->assertNotFalse($alphaPos);
        $this->assertNotFalse($zebraPos);
        // sort_order 0 first (Middle), then sort_order 1 ordered by name (Alpha before Zebra)
        $this->assertLessThan($alphaPos, $middlePos);
        $this->assertLessThan($zebraPos, $alphaPos);
    }

    public function test_categories_grid_section_hidden_when_no_categories_exist(): void
    {
        // No categories in the database — section must be hidden (R16).
        Livewire::test('categories-grid')
            ->assertOk()
            ->assertDontSee(__('storefront.home.categories'), false);
    }

    public function test_categories_grid_renders_label_caps_styling_on_category_names(): void
    {
        Category::factory()->create(['name' => 'Clutches', 'slug' => 'clutches', 'sort_order' => 1]);

        Livewire::test('categories-grid')
            ->assertOk()
            ->assertSeeHtml('text-label-caps');
    }

    public function test_categories_grid_renders_responsive_grid(): void
    {
        Category::factory()->count(3)->create();

        Livewire::test('categories-grid')
            ->assertOk()
            ->assertSeeHtml('grid')
            ->assertSeeHtml('lg:grid-cols');
    }
}
