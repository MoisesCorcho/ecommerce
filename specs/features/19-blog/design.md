# Diseño Técnico — F-01: Módulo de Blog Completo

> **Feature:** `19-blog` (F-01)  
> **Alcance:** Modelos `PostCategory` y `Post`, Migraciones, Recursos Filament v5, Componentes Livewire/Blade `/blog` y `/blog/{slug}`, SEO y Tests.

---

## 1. Arquitectura y Modelo de Datos (Patrón JSON Translatable)

### 1.1 Migraciones con Columnas JSON

```php
// create_post_categories_table
Schema::create('post_categories', function (Blueprint $table) {
    $table->id();
    $table->json('name'); // {"es": "Tendencias", "en": "Trends"}
    $table->string('slug', 180)->unique();
    $table->json('description')->nullable();
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
    $table->string('status', 32)->default('draft')->index(); // 'draft' | 'published'
    $table->timestamp('published_at')->nullable()->index();
    $table->timestamps();
});
```

### 1.2 Modelos con `HasTranslations`

- `App\Models\PostCategory`:
  - `use HasTranslations;`
  - `public array $translatable = ['name', 'description'];`
  - `hasMany(Post::class)`
  - Scope `active()`
- `App\Models\Post`:
  - `use HasTranslations;`
  - `public array $translatable = ['title', 'excerpt', 'content', 'meta_title', 'meta_description'];`
  - `belongsTo(PostCategory::class)`
  - `belongsTo(User::class, 'author_id')`
  - Scope `published()`: `status = 'published'` y `published_at <= now()`
  - Helper `readingTime()`: Calcula minutos estimados de lectura a partir de las palabras del idioma activo.

---

## 2. Panel de Administración (Filament v5)

- **Grupo de Navegación:** `Contenido` / `__('navigation.groups.content')`.
- `App\Filament\Resources\Blog\PostCategoryResource`: Gestión simple de categorías.
- `App\Filament\Resources\Blog\PostResource`:
  - Campos bilingües con Tabs (Español / Inglés).
  - `RichEditor::make('content_es')` y `RichEditor::make('content_en')` con soporte de formato.
  - `FileUpload::make('cover_image_path')->disk('public')->directory('blog')`.
  - Generación automática de `slug` reactivo desde `title_es`.

---

## 3. Storefront: Rutas y Componentes

- **Rutas Públicas:**
  - `GET /blog` $\rightarrow$ `resources/views/components/blog/blog-index.blade.php` (o componente Livewire para filtrado instantáneo por categoría y buscador).
  - `GET /blog/{slug}` $\rightarrow$ `resources/views/components/blog/blog-post.blade.php`.
- **Diseño Visual:**
  - Tipografía `var(--font-chillax)` en encabezados, `Montserrat` en párrafos.
  - Artículos relacionados al final.
  - Metadatos OpenGraph inyectados en `@section('meta')` o layout.

---

## 4. Estrategia de Testing (TDD)

1. **Dominio (`tests/Feature/Domain/BlogTest.php`):**
   - Creación de categorías y artículos con factories.
   - Scope `published()` filtra borradores y artículos con `published_at` futuro.
   - Cálculo estimado de tiempo de lectura (`readingTime()`).
2. **Filament (`tests/Feature/Filament/PostResourceTest.php`):**
   - Control de acceso admin.
   - Creación y edición con campos bilingües y subida de portada.
3. **Storefront (`tests/Feature/Storefront/BlogStorefrontTest.php`):**
   - Ruta `/blog` lista solo artículos publicados con paginación.
   - Filtrado por categoría en `/blog?category=tendencias`.
   - Detalle `/blog/{slug}` accesible y retorna 404 si el artículo está en borrador.
   - Cambio de idioma dinámico en el contenido del artículo.

---

## 5. Puntos de Extensión Futura (Por Cotizar)

- **Servicio de Traducción Automática:** Cuando se apruebe la cotización del módulo de auto-traducción, se creará el contrato `App\Contracts\Localization\TranslationServiceInterface` con implementación para DeepL/Gemini, conectándolo a una acción personalizada en `PostResource` de Filament sin alterar el esquema de base de datos ni los modelos.
