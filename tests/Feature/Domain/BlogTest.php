<?php

declare(strict_types=1);

namespace Tests\Feature\Domain;

use App\Enums\Blog\PostStatusEnum;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_status_enum_has_expected_values_labels_and_colors(): void
    {
        $this->assertSame('draft', PostStatusEnum::Draft->value);
        $this->assertSame('published', PostStatusEnum::Published->value);

        App::setLocale('es');
        $this->assertSame('Borrador', PostStatusEnum::Draft->getLabel());
        $this->assertSame('Publicado', PostStatusEnum::Published->getLabel());

        $this->assertSame('gray', PostStatusEnum::Draft->getColor());
        $this->assertSame('success', PostStatusEnum::Published->getColor());
    }

    public function test_it_creates_post_category_with_translatable_attributes_and_casts(): void
    {
        $category = PostCategory::create([
            'name' => [
                'es' => 'Tendencias',
                'en' => 'Trends',
            ],
            'slug' => 'tendencias',
            'description' => [
                'es' => 'Artículos sobre tendencias de moda.',
                'en' => 'Articles about fashion trends.',
            ],
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('post_categories', [
            'id' => $category->id,
            'slug' => 'tendencias',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $this->assertIsInt($category->sort_order);
        $this->assertIsBool($category->is_active);
        $this->assertSame('Tendencias', $category->getTranslation('name', 'es'));
        $this->assertSame('Trends', $category->getTranslation('name', 'en'));
        $this->assertSame('Tendencias', $category->getLocalizedName('es'));
        $this->assertSame('Trends', $category->getLocalizedName('en'));
    }

    public function test_post_category_scopes_active_and_ordered(): void
    {
        $cat1 = PostCategory::create([
            'name' => ['es' => 'Categoría B'],
            'slug' => 'cat-b',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $cat2 = PostCategory::create([
            'name' => ['es' => 'Categoría A'],
            'slug' => 'cat-a',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $inactiveCat = PostCategory::create([
            'name' => ['es' => 'Inactiva'],
            'slug' => 'inactiva',
            'sort_order' => 0,
            'is_active' => false,
        ]);

        $activeCategories = PostCategory::query()->active()->ordered()->get();

        $this->assertCount(2, $activeCategories);
        $this->assertSame($cat2->id, $activeCategories[0]->id);
        $this->assertSame($cat1->id, $activeCategories[1]->id);
        $this->assertFalse($activeCategories->contains($inactiveCat));
    }

    public function test_it_creates_post_with_translatable_fields_relations_and_casts(): void
    {
        $user = User::factory()->create();
        $category = PostCategory::create([
            'name' => ['es' => 'Estilo de Vida'],
            'slug' => 'estilo-de-vida',
        ]);

        $post = Post::create([
            'post_category_id' => $category->id,
            'author_id' => $user->id,
            'title' => [
                'es' => 'El Arte del Cuero Artesanal',
                'en' => 'The Art of Handcrafted Leather',
            ],
            'slug' => 'el-arte-del-cuero-artesanal',
            'excerpt' => [
                'es' => 'Descubre el proceso detrás de cada una de nuestras piezas.',
                'en' => 'Discover the process behind each of our pieces.',
            ],
            'content' => [
                'es' => '<p>Contenido completo en español con detalles de confección.</p>',
                'en' => '<p>Full content in english with crafting details.</p>',
            ],
            'cover_image_path' => 'blog/arte-cuero.jpg',
            'meta_title' => [
                'es' => 'El Arte del Cuero | Leen',
                'en' => 'The Art of Leather | Leen',
            ],
            'meta_description' => [
                'es' => 'Historia y artesanía en Leen.',
                'en' => 'History and craftsmanship at Leen.',
            ],
            'status' => PostStatusEnum::Published,
            'published_at' => now(),
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'post_category_id' => $category->id,
            'author_id' => $user->id,
            'slug' => 'el-arte-del-cuero-artesanal',
            'status' => 'published',
        ]);

        $this->assertInstanceOf(PostStatusEnum::class, $post->status);
        $this->assertSame(PostStatusEnum::Published, $post->status);
        $this->assertInstanceOf(Carbon::class, $post->published_at);
        $this->assertTrue($post->category->is($category));
        $this->assertTrue($post->author->is($user));
    }

    public function test_scope_published_filters_drafts_and_future_scheduled_posts(): void
    {
        $published = Post::create([
            'title' => ['es' => 'Post Publicado'],
            'slug' => 'post-publicado',
            'content' => ['es' => 'Contenido'],
            'status' => PostStatusEnum::Published,
            'published_at' => now()->subHour(),
        ]);

        $draft = Post::create([
            'title' => ['es' => 'Post Borrador'],
            'slug' => 'post-borrador',
            'content' => ['es' => 'Contenido'],
            'status' => PostStatusEnum::Draft,
            'published_at' => now()->subHour(),
        ]);

        $futurePublished = Post::create([
            'title' => ['es' => 'Post Futuro'],
            'slug' => 'post-futuro',
            'content' => ['es' => 'Contenido'],
            'status' => PostStatusEnum::Published,
            'published_at' => now()->addDay(),
        ]);

        $publishedWithoutDate = Post::create([
            'title' => ['es' => 'Post Sin Fecha'],
            'slug' => 'post-sin-fecha',
            'content' => ['es' => 'Contenido'],
            'status' => PostStatusEnum::Published,
            'published_at' => null,
        ]);

        $results = Post::query()->published()->get();

        $this->assertTrue($results->contains($published));
        $this->assertFalse($results->contains($draft));
        $this->assertFalse($results->contains($futurePublished));
        $this->assertFalse($results->contains($publishedWithoutDate));
    }

    public function test_post_localized_helpers_return_translation_with_fallback_to_es(): void
    {
        $bilingual = Post::create([
            'title' => ['es' => 'Título ES', 'en' => 'Title EN'],
            'slug' => 'post-bilingue',
            'excerpt' => ['es' => 'Extracto ES', 'en' => 'Excerpt EN'],
            'content' => ['es' => '<p>Cuerpo ES</p>', 'en' => '<p>Body EN</p>'],
            'meta_title' => ['es' => 'SEO Title ES', 'en' => 'SEO Title EN'],
            'meta_description' => ['es' => 'SEO Desc ES', 'en' => 'SEO Desc EN'],
            'status' => PostStatusEnum::Published,
            'published_at' => now(),
        ]);

        $spanishOnly = Post::create([
            'title' => ['es' => 'Solo Español'],
            'slug' => 'solo-espanol',
            'excerpt' => ['es' => 'Extracto solo ES'],
            'content' => ['es' => '<p>Contenido solo ES</p>'],
            'status' => PostStatusEnum::Published,
            'published_at' => now(),
        ]);

        App::setLocale('en');

        $this->assertSame('Title EN', $bilingual->getLocalizedTitle());
        $this->assertSame('Excerpt EN', $bilingual->getLocalizedExcerpt());
        $this->assertSame('<p>Body EN</p>', $bilingual->getLocalizedContent());
        $this->assertSame('SEO Title EN', $bilingual->getLocalizedMetaTitle());
        $this->assertSame('SEO Desc EN', $bilingual->getLocalizedMetaDescription());

        // Fallback to es
        $this->assertSame('Solo Español', $spanishOnly->getLocalizedTitle());
        $this->assertSame('Extracto solo ES', $spanishOnly->getLocalizedExcerpt());
        $this->assertSame('<p>Contenido solo ES</p>', $spanishOnly->getLocalizedContent());
        $this->assertSame('Solo Español | Leen', $spanishOnly->getLocalizedMetaTitle());
        $this->assertSame('Extracto solo ES', $spanishOnly->getLocalizedMetaDescription());
    }

    public function test_it_calculates_estimated_reading_time(): void
    {
        // 400 words = 2 minutes (200 words/min)
        $words400 = implode(' ', array_fill(0, 400, 'palabra'));
        $post = Post::create([
            'title' => ['es' => 'Test lectura'],
            'slug' => 'test-lectura',
            'content' => ['es' => "<p>{$words400}</p>"],
            'status' => PostStatusEnum::Published,
            'published_at' => now(),
        ]);

        $this->assertSame(2, $post->readingTime('es'));

        // Short post (<200 words) returns at least 1 min
        $words50 = implode(' ', array_fill(0, 50, 'palabra'));
        $shortPost = Post::create([
            'title' => ['es' => 'Test corto'],
            'slug' => 'test-corto',
            'content' => ['es' => "<p>{$words50}</p>"],
            'status' => PostStatusEnum::Published,
            'published_at' => now(),
        ]);

        $this->assertSame(1, $shortPost->readingTime('es'));
    }

    public function test_it_deletes_cover_image_from_disk_when_post_is_deleted(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('blog/portada-test.jpg', 'fake image content');
        $this->assertTrue(Storage::disk('public')->exists('blog/portada-test.jpg'));

        $post = Post::create([
            'title' => ['es' => 'Post con foto'],
            'slug' => 'post-con-foto',
            'content' => ['es' => 'Contenido'],
            'cover_image_path' => 'blog/portada-test.jpg',
            'status' => PostStatusEnum::Published,
            'published_at' => now(),
        ]);

        $post->delete();

        $this->assertFalse(Storage::disk('public')->exists('blog/portada-test.jpg'));
    }
}
