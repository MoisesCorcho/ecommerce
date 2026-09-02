<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Enums\Blog\PostStatusEnum;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BlogStorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        app()->setLocale('es');
    }

    public function test_blog_index_route_is_accessible_and_lists_published_posts(): void
    {
        $published = Post::factory()->create([
            'title' => ['es' => 'Artículo Publicado de Prueba'],
            'excerpt' => ['es' => 'Extracto visible en el catálogo.'],
            'status' => PostStatusEnum::Published,
            'published_at' => now()->subHour(),
        ]);

        $draft = Post::factory()->draft()->create([
            'title' => ['es' => 'Artículo en Borrador Oculto'],
        ]);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('Artículo Publicado de Prueba', false)
            ->assertSee('Extracto visible en el catálogo.', false)
            ->assertDontSee('Artículo en Borrador Oculto', false)
            ->assertSeeHtml('aria-label="Breadcrumb"')
            ->assertSeeHtml('min-h-[4.25rem]')
            ->assertSeeHtml('mt-auto pt-6 border-t');
    }

    public function test_blog_index_filters_by_category(): void
    {
        $category1 = PostCategory::factory()->create([
            'name' => ['es' => 'Tendencias'],
            'slug' => 'tendencias',
            'sort_order' => 1,
        ]);

        $category2 = PostCategory::factory()->create([
            'name' => ['es' => 'Cuidados'],
            'slug' => 'cuidados',
            'sort_order' => 2,
        ]);

        $post1 = Post::factory()->create([
            'title' => ['es' => 'Post de Tendencias'],
            'post_category_id' => $category1->id,
            'status' => PostStatusEnum::Published,
            'published_at' => now()->subDay(),
        ]);

        $post2 = Post::factory()->create([
            'title' => ['es' => 'Post de Cuidados'],
            'post_category_id' => $category2->id,
            'status' => PostStatusEnum::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('blog.index', ['category' => 'tendencias']))
            ->assertOk()
            ->assertSee('Post de Tendencias', false)
            ->assertDontSee('Post de Cuidados', false)
            ->assertSeeHtml('md:flex-wrap md:justify-center md:items-center')
            ->assertSeeHtml('aria-label="'.__('blog.storefront.previous_categories').'"')
            ->assertSeeHtml('aria-label="'.__('blog.storefront.next_categories').'"');
    }

    public function test_blog_index_displays_show_more_button_when_categories_exceed_desktop_limit(): void
    {
        for ($i = 1; $i <= 9; $i++) {
            $cat = PostCategory::factory()->create([
                'name' => ['es' => "Categoría {$i}"],
                'slug' => "categoria-{$i}",
                'sort_order' => $i,
            ]);

            Post::factory()->create([
                'title' => ['es' => "Post en Categoría {$i}"],
                'post_category_id' => $cat->id,
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDay(),
            ]);
        }

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee(__('blog.storefront.show_more'), false)
            ->assertSeeHtml('(+2)');
    }

    public function test_blog_post_detail_is_accessible_for_published_post(): void
    {
        $category = PostCategory::factory()->create(['name' => ['es' => 'Moda Consciente']]);
        $author = User::factory()->create(['name' => 'Moisés Corcho']);

        $post = Post::factory()->create([
            'title' => ['es' => 'El Alma de una Cartera Leen'],
            'slug' => 'el-alma-de-una-cartera-leen',
            'content' => ['es' => '<p>Párrafo principal con detalles artesanales de lujo.</p>'],
            'post_category_id' => $category->id,
            'author_id' => $author->id,
            'status' => PostStatusEnum::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('blog.show', ['slug' => 'el-alma-de-una-cartera-leen']))
            ->assertOk()
            ->assertSee('El Alma de una Cartera Leen', false)
            ->assertSee('Párrafo principal con detalles artesanales de lujo.', false)
            ->assertSee('Moda Consciente', false)
            ->assertSeeHtml('aria-label="Breadcrumb"')
            ->assertSeeHtml('prose-editorial')
            ->assertSeeHtml('aria-label="WhatsApp"')
            ->assertSee('17.472 14.382', false);
    }

    public function test_blog_post_detail_returns_404_for_draft_post_to_guest_and_customer(): void
    {
        $draft = Post::factory()->draft()->create([
            'slug' => 'articulo-borrador',
        ]);

        // Guest visitor
        $this->get(route('blog.show', ['slug' => 'articulo-borrador']))
            ->assertNotFound();

        // Customer logged in
        $customer = User::factory()->create(['email' => 'customer@example.com']);
        $this->actingAs($customer)
            ->get(route('blog.show', ['slug' => 'articulo-borrador']))
            ->assertNotFound();
    }

    public function test_blog_post_detail_allows_admin_to_preview_draft_post(): void
    {
        Config::set('ecommerce.admin_emails', ['admin@example.com']);
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');

        $draft = Post::factory()->draft()->create([
            'title' => ['es' => 'Borrador Exclusivo Admin'],
            'slug' => 'borrador-exclusivo-admin',
            'content' => ['es' => '<p>Contenido preview para administradores.</p>'],
        ]);

        $this->actingAs($admin)
            ->get(route('blog.show', ['slug' => 'borrador-exclusivo-admin']))
            ->assertOk()
            ->assertSee('Borrador Exclusivo Admin', false)
            ->assertSee('Contenido preview para administradores.', false)
            ->assertSee(__('blog.storefront.preview_notice'), false);
    }

    public function test_blog_post_detail_displays_related_posts_with_fallback(): void
    {
        $category = PostCategory::factory()->create(['name' => ['es' => 'Artesanía']]);
        $otherCategory = PostCategory::factory()->create(['name' => ['es' => 'General']]);

        $currentPost = Post::factory()->create([
            'title' => ['es' => 'Post Actual'],
            'slug' => 'post-actual',
            'post_category_id' => $category->id,
            'status' => PostStatusEnum::Published,
            'published_at' => now()->subDays(5),
        ]);

        $sameCategoryPost = Post::factory()->create([
            'title' => ['es' => 'Relacionado Misma Categoría'],
            'slug' => 'relacionado-misma-categoria',
            'post_category_id' => $category->id,
            'status' => PostStatusEnum::Published,
            'published_at' => now()->subDays(4),
        ]);

        $fallbackPost = Post::factory()->create([
            'title' => ['es' => 'Post Reciente de Otra Categoría'],
            'slug' => 'post-reciente-otra-categoria',
            'post_category_id' => $otherCategory->id,
            'status' => PostStatusEnum::Published,
            'published_at' => now()->subDays(3),
        ]);

        $this->get(route('blog.show', ['slug' => 'post-actual']))
            ->assertOk()
            ->assertSee('Relacionado Misma Categoría', false)
            ->assertSee('Post Reciente de Otra Categoría', false)
            ->assertSeeHtml('min-h-[4.25rem]')
            ->assertSeeHtml('mt-auto pt-6 border-t');
    }

    public function test_blog_switches_language_when_locale_changes(): void
    {
        $post = Post::factory()->create([
            'title' => [
                'es' => 'Título en Español',
                'en' => 'Title in English',
            ],
            'content' => [
                'es' => '<p>Cuerpo en español</p>',
                'en' => '<p>Body in english</p>',
            ],
            'slug' => 'post-bilingue-test',
            'status' => PostStatusEnum::Published,
            'published_at' => now()->subDay(),
        ]);

        // Spanish request
        app()->setLocale('es');
        $this->get(route('blog.show', ['slug' => 'post-bilingue-test']))
            ->assertOk()
            ->assertSee('Título en Español', false)
            ->assertSee('Cuerpo en español', false);

        // English request
        app()->setLocale('en');
        $this->get(route('blog.show', ['slug' => 'post-bilingue-test']))
            ->assertOk()
            ->assertSee('Title in English', false)
            ->assertSee('Body in english', false);
    }

    public function test_storefront_layout_renders_blog_links_in_header_and_footer(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeHtml('href="'.route('blog.index').'"')
            ->assertSee(__('storefront.nav.blog'), false)
            ->assertSee(__('storefront.footer.blog'), false);
    }
}
