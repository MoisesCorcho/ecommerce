# Diseño Técnico — F-01: Módulo de Blog Completo

> **Feature:** `19-blog` (F-01)  
> **Alcance:** Modelos `PostCategory` y `Post`, Enum `PostStatusEnum`, Migraciones, Recursos Filament v5 descompuestos, Componentes Livewire v4 MFC `/blog` y `/blog/{slug}`, Integración Visual con Design Tokens, SEO y Tests TDD.  
> **Fuentes Canónicas:** [`specs/ui-briefs/19-blog.md`](../../ui-briefs/19-blog.md), [`specs/ui-briefs/00-design-tokens.md`](../../ui-briefs/00-design-tokens.md), `AGENTS.md`.

---

## 1. Arquitectura y Modelo de Datos

### 1.1 Backed Enums (`app/Enums/Blog/`)

`App\Enums\Blog\PostStatusEnum.php`:
```php
namespace App\Enums\Blog;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PostStatusEnum: string implements HasLabel, HasColor
{
    case Draft = 'draft';
    case Published = 'published';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => __('enums.post_status.draft'),
            self::Published => __('enums.post_status.published'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'success',
        };
    }
}
```

---

### 1.2 Migraciones de Base de Datos

```php
// create_post_categories_table
Schema::create('post_categories', function (Blueprint $table) {
    $table->id();
    $table->json('name'); // {"es": "Tendencias", "en": "Trends"}
    $table->string('slug', 180)->unique();
    $table->json('description')->nullable();
    $table->unsignedInteger('sort_order')->default(0)->index();
    $table->boolean('is_active')->default(true)->index();
    $table->timestamps();
});

// create_posts_table
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_category_id')->nullable()->constrained('post_categories')->nullOnDelete();
    $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
    $table->json('title'); // {"es": "Título en español", "en": "Title in english"}
    $table->string('slug', 255)->unique(); // Slug canónico único
    $table->json('excerpt')->nullable();
    $table->json('content'); // Rich text HTML por idioma
    $table->string('cover_image_path', 2048)->nullable();
    $table->json('meta_title')->nullable();
    $table->json('meta_description')->nullable();
    $table->string('status', 32)->default(PostStatusEnum::Draft->value)->index();
    $table->timestamp('published_at')->nullable()->index();
    $table->timestamps();
});
```

---

### 1.3 Modelos Eloquent (`app/Models/`)

- `App\Models\PostCategory`:
  - Traits: `HasFactory`, `Spatie\Translatable\HasTranslations`.
  - Atributos translatables: `public array $translatable = ['name', 'description'];`.
  - Casts: `'is_active' => 'boolean'`, `'sort_order' => 'integer'`.
  - Relaciones: `hasMany(Post::class)`.
  - Scopes: `scopeActive($query)`, `scopeOrdered($query)`.
  - Helper: `getLocalizedName(?string $locale = null): string`.

- `App\Models\Post`:
  - Traits: `HasFactory`, `Spatie\Translatable\HasTranslations`.
  - Atributos translatables: `public array $translatable = ['title', 'excerpt', 'content', 'meta_title', 'meta_description'];`.
  - Casts: `'status' => PostStatusEnum::class`, `'published_at' => 'datetime'`.
  - Relaciones: `belongsTo(PostCategory::class, 'post_category_id')`, `belongsTo(User::class, 'author_id')`.
  - Scopes:
    - `scopePublished(Builder $query)`: `where('status', PostStatusEnum::Published)->whereNotNull('published_at')->where('published_at', '<=', now())`.
  - Helpers bilingües con fallback:
    - `getLocalizedTitle(?string $locale = null): string`
    - `getLocalizedExcerpt(?string $locale = null): string`
    - `getLocalizedContent(?string $locale = null): string`
    - `getLocalizedMetaTitle(?string $locale = null): string`
    - `getLocalizedMetaDescription(?string $locale = null): string`
  - Helper de lectura:
    - `readingTime(?string $locale = null): int`: Calcula `max(1, (int) ceil(str_word_count(strip_tags($this->getLocalizedContent($locale))) / 200))`.
  - Hook de limpieza de medios:
    - `static::deleting`: Purga física de `cover_image_path` en `Storage::disk('public')` si existe.

---

## 2. Panel de Administración (Filament v5 Descompuesto)

Estructura bajo `app/Filament/Resources/Blog/`:
```text
app/Filament/Resources/Blog/
  ├── PostCategories/
  │     ├── Pages/
  │     │     ├── CreatePostCategory.php
  │     │     ├── EditPostCategory.php
  │     │     └── ListPostCategories.php
  │     ├── Schemas/
  │     │     ├── PostCategoryForm.php
  │     │     └── PostCategoriesTable.php
  │     └── PostCategoryResource.php
  └── Posts/
        ├── Pages/
        │     ├── CreatePost.php
        │     ├── EditPost.php
        │     └── ListPosts.php
        ├── Schemas/
        │     ├── PostForm.php
        │     └── PostsTable.php
        └── PostResource.php
```

- **Grupo de Navegación:** `Contenido` / `__('navigation.groups.content')`.
- **`PostCategoryResource`:**
  - Formulario con Tabs por `LocaleEnum` para nombre y descripción bilingüe.
  - Campos: `slug` (generado reactivamente), `sort_order` (numérico), `is_active` (toggle).
- **`PostResource`:**
  - Formulario con Tabs por `LocaleEnum`:
    - Tab Español (Primary badge): `title.es`, `excerpt.es`, `content.es` (RichEditor).
    - Tab Inglés: `title.en`, `excerpt.en`, `content.en` (RichEditor).
  - Sección Lateral / Configuración:
    - `post_category_id` (Select searchable/preload).
    - `slug` (generado reactivamente desde `title.es`).
    - `cover_image_path` (`FileUpload::make()->image()->disk('public')->directory('blog')`).
    - `status` (`Select::make()->options(PostStatusEnum::class)->default(PostStatusEnum::Draft)`).
    - `published_at` (`DateTimePicker::make()`).
    - Tabs SEO: `meta_title.{locale}`, `meta_description.{locale}`.
  - Asignación de autor: en `mutateFormDataBeforeCreate`, asignar `author_id = auth()->id()`.

---

## 3. Storefront: Rutas, Livewire v4 MFC y Diseño Visual

### 3.1 Rutas Públicas (`routes/web.php`)
```php
Route::livewire('/blog', 'blog-index')->name('blog.index');
Route::livewire('/blog/{slug}', 'blog-show')->name('blog.show');
```

---

### 3.2 Componentes Livewire v4 MFC

1. **`resources/views/components/blog-index/`**:
   - `blog-index.php`: Componente anónimo con `#[Layout('layouts.storefront')]`, `WithPagination`.
     - Propiedades URL: `#[Url] public ?string $category = null;`.
     - Método `with()`: Resuelve categorías activas ordenadas por `sort_order`, y query de posts `Post::published()->with(['category', 'author'])` filtrado opcionalmente por categoría, paginado a 9 o 12 posts.
   - `blog-index.blade.php`:
     - Hero del blog con `font-chillax` en `display-lg`, subtítulo en `font-labelle-aurore`.
     - Barra de pills de categorías navegables (`bg-intense-cocoa text-silk-cream` para activa, `bg-soft-sand` en hover).
     - Grid editorial de **3 columnas** (`grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter`).
     - Tarjetas `ArticleCard` con imagen 16:10 / 4:3 `rounded-none`, zoom hover 700ms, badge de categoría, fecha, tiempo de lectura, título en `font-chillax text-headline-sm` y extracto.
     - Paginación integrada con estética de marca.

2. **`resources/views/components/blog-show/`**:
   - `blog-show.php`: Componente anónimo con `#[Layout('layouts.storefront')]`.
     - Montaje: Resuelve `$this->post` por `slug`.
     - Seguridad de Borradores: Si el post no está publicado y el usuario autenticado no es admin (`!auth()->user()?->isAdmin()`), abortar con 404.
     - Artículos relacionados: Resuelve hasta 3 posts publicados de la misma categoría (excluyendo el actual). Si hay < 3, completa con los más recientes publicados.
   - `blog-show.blade.php`:
     - Breadcrumb en `font-label-caps`.
     - Cabecera del post: categoría en `text-soft-gold`, título en `font-chillax display-lg`, metadatos (autor, fecha, tiempo de lectura).
     - Portada principal a ancho de contenedor (`rounded-none`).
     - Cuerpo del artículo con tipografía prose (`font-sans text-body-lg text-intense-cocoa/85 leading-relaxed`, H2/H3 en `font-chillax`, blockquotes con borde `border-soft-gold` y fondo `bg-soft-sand/40`).
     - Sección de historias relacionadas (3 tarjetas).
     - Metatags OpenGraph y SEO inyectados en `@section('meta')` o layout.

---

### 3.3 Integración en Layout Storefront (`resources/views/layouts/storefront.blade.php`)

- Enlace "Blog" en navegación desktop: `<a href="{{ route('blog.index') }}">{{ __('storefront.nav.blog') }}</a>`.
- Enlace "Blog" en menú móvil desplegable: `<a href="{{ route('blog.index') }}">{{ __('storefront.nav.blog') }}</a>`.
- Enlace "Blog" en footer institucional: `<a href="{{ route('blog.index') }}">{{ __('storefront.footer.blog') }}</a>`.

---

## 4. Estrategia de Testing (TDD Estricto)

1. **Dominio (`tests/Feature/Domain/BlogTest.php`):**
   - Atributos, relaciones y casts de `PostCategory` y `Post` con factories.
   - Cast de `PostStatusEnum` y scopes `published()`, `active()`, `ordered()`.
   - Helpers bilingües con fallback a `es` cuando `en` está ausente.
   - Cálculo dinámico de `readingTime()`.
   - Limpieza de imagen en disco con `Storage::fake('public')` al eliminar un post.

2. **Filament Admin (`tests/Feature/Filament/BlogResourceTest.php`):**
   - Control de acceso (403 para visitantes/usuarios no admin).
   - CRUD de `PostCategoryResource` con orden y estado.
   - CRUD de `PostResource` con tabs bilingües, `PostStatusEnum`, RichEditor y subida de portada.

3. **Storefront & Livewire (`tests/Feature/Storefront/BlogStorefrontTest.php`):**
   - Ruta `/blog` lista sólo posts publicados y paginados.
   - Filtro por categoría `/blog?category={slug}` mantiene paginación.
   - Detalle `/blog/{slug}` accesible para publicados y retorna 404 para borradores ante usuarios no-admin.
   - Detalle `/blog/{slug}` accesible para borradores si el usuario es administrador.
   - Inyección de metatags SEO OpenGraph en HTML.
   - Artículos relacionados muestran hasta 3 posts con fallback si la categoría tiene < 3.
   - Conmutación de idioma (`/locale`) actualiza títulos y contenidos dinámicamente.

---

## 5. Puntos de Extensión Futura (Por Cotizar)

- **Servicio de Traducción Automática:** Integración futura con OpenAI/DeepL mediante el contrato `App\Contracts\Localization\TranslationServiceInterface`.
- **Comentarios y Moderación:** Tablas y componentes de discusión pública.
- **Biografías de Autores:** Páginas públicas `/blog/autores/{slug}`.
