<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\Blog\PostStatusEnum;
use App\Filament\Resources\Blog\PostCategories\Pages\CreatePostCategory;
use App\Filament\Resources\Blog\PostCategories\Pages\EditPostCategory;
use App\Filament\Resources\Blog\PostCategories\Pages\ListPostCategories;
use App\Filament\Resources\Blog\Posts\Pages\CreatePost;
use App\Filament\Resources\Blog\Posts\Pages\EditPost;
use App\Filament\Resources\Blog\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BlogResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('es');
    }

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

    public function test_guest_or_non_admin_cannot_access_blog_resources(): void
    {
        $this->get(ListPostCategories::getUrl())->assertRedirect();
        $this->get(ListPosts::getUrl())->assertRedirect();

        $regularUser = User::factory()->create(['email' => 'customer@example.com']);
        $this->actingAs($regularUser)
            ->get(ListPostCategories::getUrl())
            ->assertForbidden();

        $this->actingAs($regularUser)
            ->get(ListPosts::getUrl())
            ->assertForbidden();
    }

    public function test_admin_can_list_and_create_post_category(): void
    {
        $this->actingAsAdmin();

        $existing = PostCategory::factory()->create([
            'name' => ['es' => 'Tendencias de Verano'],
        ]);

        Livewire::test(ListPostCategories::class)
            ->assertCanSeeTableRecords([$existing]);

        Livewire::test(CreatePostCategory::class)
            ->set('data.name.es', 'Moda Sostenible')
            ->set('data.name.en', 'Sustainable Fashion')
            ->set('data.slug', 'moda-sostenible')
            ->set('data.description.es', 'Artículos sobre producción ética.')
            ->set('data.description.en', 'Articles on ethical craftsmanship.')
            ->set('data.sort_order', 2)
            ->set('data.is_active', true)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas('post_categories', [
            'slug' => 'moda-sostenible',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $created = PostCategory::where('slug', 'moda-sostenible')->first();
        $this->assertNotNull($created);
        $this->assertSame('Moda Sostenible', $created->getTranslation('name', 'es'));
        $this->assertSame('Sustainable Fashion', $created->getTranslation('name', 'en'));
    }

    public function test_admin_can_edit_post_category(): void
    {
        $this->actingAsAdmin();

        $category = PostCategory::factory()->create([
            'name' => ['es' => 'Nombre Viejo'],
            'sort_order' => 1,
        ]);

        Livewire::test(EditPostCategory::class, ['record' => $category->getRouteKey()])
            ->set('data.name.es', 'Nombre Nuevo')
            ->set('data.sort_order', 3)
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $fresh = $category->fresh();
        $this->assertSame('Nombre Nuevo', $fresh->getTranslation('name', 'es'));
        $this->assertSame(3, $fresh->sort_order);
    }

    public function test_admin_can_list_and_create_post(): void
    {
        $admin = $this->actingAsAdmin();
        $category = PostCategory::factory()->create(['name' => ['es' => 'Guías']]);

        $post = Post::factory()->create([
            'title' => ['es' => 'Cómo cuidar el cuero'],
            'post_category_id' => $category->id,
            'author_id' => $admin->id,
        ]);

        Livewire::test(ListPosts::class)
            ->assertCanSeeTableRecords([$post]);

        Livewire::test(CreatePost::class)
            ->set('data.title.es', 'La Historia de Leen')
            ->set('data.title.en', 'The Story of Leen')
            ->set('data.slug', 'la-historia-de-leen')
            ->set('data.excerpt.es', 'Extracto de la historia.')
            ->set('data.excerpt.en', 'Excerpt of the story.')
            ->set('data.content.es', '<p>Contenido completo en español.</p>')
            ->set('data.content.en', '<p>Full content in english.</p>')
            ->set('data.post_category_id', $category->id)
            ->set('data.status', PostStatusEnum::Published->value)
            ->set('data.published_at', now()->format('Y-m-d H:i:s'))
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'slug' => 'la-historia-de-leen',
            'post_category_id' => $category->id,
            'author_id' => $admin->id,
            'status' => 'published',
        ]);

        $created = Post::where('slug', 'la-historia-de-leen')->first();
        $this->assertNotNull($created);
        $this->assertSame('La Historia de Leen', $created->getTranslation('title', 'es'));
        $this->assertSame('The Story of Leen', $created->getTranslation('title', 'en'));
    }

    public function test_admin_can_edit_post(): void
    {
        $this->actingAsAdmin();

        $post = Post::factory()->create([
            'title' => ['es' => 'Título Inicial'],
            'status' => PostStatusEnum::Draft,
        ]);

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->set('data.title.es', 'Título Modificado')
            ->set('data.status', PostStatusEnum::Published->value)
            ->set('data.published_at', now()->format('Y-m-d H:i:s'))
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $fresh = $post->fresh();
        $this->assertSame('Título Modificado', $fresh->getTranslation('title', 'es'));
        $this->assertSame(PostStatusEnum::Published, $fresh->status);
    }

    public function test_admin_can_delete_post(): void
    {
        $this->actingAsAdmin();

        $post = Post::factory()->create([
            'title' => ['es' => 'Post a eliminar'],
        ]);

        Livewire::test(ListPosts::class)
            ->callAction(TestAction::make('delete')->table($post));

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }
}
