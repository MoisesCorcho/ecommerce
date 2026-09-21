# F21 — Diseño Técnico: Optimización SEO & Schema

> **Feature:** F21 · **Slug:** `21-seo-optimization`  
> **Patrones:** Action invokable (`GenerateSitemapAction`), Thin Controller (`SitemapController`), Blade Components para Schema JSON-LD, Lazy Cache.  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md) — Tipo primero, área después: `app/Actions/Seo/`, `app/Http/Controllers/Seo/`.

---

## 1. Arquitectura de Componentes y Responsabilidades

```
┌────────────────────────────────────────────────────────┐
│                   Cliente / Rastreador                 │
└───────────────┬────────────────────────┬───────────────┘
                │                        │
       GET /robots.txt           GET /sitemap.xml
                │                        │
        [public/robots.txt]    [SitemapController]
                                         │
                              [GenerateSitemapAction]
                                         │
                         ┌───────────────┴───────────────┐
                         ▼                               ▼
                 Cache::remember            Eloquent Queries (Only Read)
                 ('seo.sitemap', 3600)      - Product::publishedForStorefront()
                                            - Category::whereHas('products')
                                            - Post::published()
```

### Storefront Layout & View Pipeline
```
[resources/views/layouts/storefront.blade.php]
  ├── <head>
  │     ├── <title>{{ $title ?? config('seo.defaults.title') }}</title>
  │     ├── <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">
  │     ├── <meta name="description" content="{{ $metaDescription ?? ... }}">
  │     ├── OpenGraph Tags (og:title, og:image, og:url, og:site_name, og:type)
  │     ├── Twitter Card Tags (twitter:card, twitter:title, twitter:image)
  │     ├── @stack('meta') (Overrides específicos por vista)
  │     └── <x-seo.organization-schema /> (JSON-LD Organization + WebSite en Home)
  │
  └── [Views Públicas]
        ├── product-detail.blade.php:
        │     ├── Inyecta canonical limpia (sin query params)
        │     ├── Inyecta og:type 'product', og:image (foto principal)
        │     ├── <x-seo.product-schema :product="$product" :currency="$currency" />
        │     ├── <x-seo.breadcrumbs-schema :items="$breadcrumbItems" />
        │     └── Alt text semántico en <img>
        └── catalog-list.blade.php:
              ├── Título dinámico por categoría activa
              ├── H1 contextualizado ({Categoría} o "Tienda")
              └── Canonical limpia por categoría
```

---

## 2. Definición de Clases y Contratos

### A. Action: `App\Actions\Seo\GenerateSitemapAction`
* **Ubicación:** `app/Actions/Seo/GenerateSitemapAction.php`
* **Responsabilidad:** Caso de uso único. Compila el XML de sitemap consultando páginas estáticas y modelos de datos activos, almacenando el resultado en caché durante 3600 segundos.
* **Firma:**
  ```php
  declare(strict_types=1);

  namespace App\Actions\Seo;

  use App\Enums\Commerce\CurrencyEnum;
  use App\Models\Category;
  use App\Models\Post;
  use App\Models\Product;
  use Illuminate\Support\Facades\Cache;

  class GenerateSitemapAction
  {
      public function __invoke(): string
      {
          return Cache::remember('seo.sitemap', 3600, function (): string {
              // Recopilar URLs estáticas y dinámicas
              // Retornar XML válido estructurado
          });
      }
  }
  ```

### B. Controller: `App\Http\Controllers\Seo\SitemapController`
* **Ubicación:** `app/Http/Controllers/Seo/SitemapController.php`
* **Responsabilidad:** Controlador delgado invokable. Invoca la Action y devuelve la respuesta HTTP con las cabeceras XML apropiadas.
* **Firma:**
  ```php
  declare(strict_types=1);

  namespace App\Http\Controllers\Seo;

  use App\Actions\Seo\GenerateSitemapAction;
  use Illuminate\Http\Response;

  class SitemapController
  {
      public function __invoke(GenerateSitemapAction $action): Response
      {
          return response($action(), 200, [
              'Content-Type' => 'application/xml; charset=utf-8',
          ]);
      }
  }
  ```

### C. Configuración y Traducciones
* **`lang/es/seo.php` y `lang/en/seo.php`:**
  - `brand_name`: `LEEN`
  - `default_title`: `LEEN | Bolsos y Accesorios Artesanales Hechos a Mano`
  - `default_description`: `Bolsos y accesorios contemporáneos de lujo consciente, elaborados artesanalmente en Colombia. Diseños atemporales hechos para perdurar.`
  - `product_title_format`: `:name | LEEN — Accesorios Artesanales`
  - `category_title_format`: `:name | Colección LEEN`

---

## 3. Componentes Blade para Schema.org (JSON-LD)

### A. `<x-seo.organization-schema />`
Renderizado condicional en la página principal (`routeIs('home')`).
```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "LEEN",
  "url": "https://leenhoney.com",
  "logo": "https://leenhoney.com/images/logos/leen-brown.png",
  "sameAs": [
    "https://www.instagram.com/leen_____________________/",
    "https://www.tiktok.com/@leenhandbags"
  ],
  "contactPoint": {
    "@type": "ContactPoint",
    "telephone": "+57 300 123 4567",
    "contactType": "customer service",
    "availableLanguage": ["Spanish", "English"]
  }
}
</script>
```

### B. `<x-seo.product-schema :product="$product" :currency="$currency" />`
Renderizado en [product-detail.blade.php](file:///home/moises/programation_projects/test/marketplace/resources/views/components/product-detail/product-detail.blade.php).
Calcula la oferta con la variante activa o precio base:
```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "{{ $product->name }}",
  "description": "{{ $cleanDescription }}",
  "image": [
    @foreach ($product->images as $img)
      "{{ Storage::disk('public')->url($img->path) }}"{{ $loop->last ? '' : ',' }}
    @endforeach
  ],
  "brand": {
    "@type": "Brand",
    "name": "LEEN"
  },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "{{ $currency->value }}",
    "price": "{{ $offerPriceFormatted }}",
    "availability": "{{ $product->is_preorder ? 'https://schema.org/PreOrder' : 'https://schema.org/InStock' }}",
    "url": "{{ route('products.show', $product->slug) }}"
  }
}
</script>
```

---

## 4. Robots.txt Canónico

El archivo físico [public/robots.txt](file:///home/moises/programation_projects/test/marketplace/public/robots.txt) se actualizará a:
```text
User-agent: *
Disallow: /admin
Disallow: /admin/*
Disallow: /cart
Disallow: /checkout
Disallow: /profile
Disallow: /profile/*
Disallow: /orders/*
Disallow: /login
Disallow: /register
Disallow: /forgot-password
Disallow: /reset-password/*
Disallow: /api/*
Disallow: /verify-email/*

Sitemap: https://leenhoney.com/sitemap.xml
```

---

## 5. Pruebas y Matriz de Verificación (TDD)

1. **`tests/Feature/Seo/RobotsTxtTest.php`:**
   - Verifica respuesta 200 de `/robots.txt`.
   - Verifica directivas `Disallow` requeridas.
   - Verifica directiva `Sitemap`.
2. **`tests/Feature/Seo/SitemapTest.php`:**
   - Verifica respuesta 200 y Content-Type `application/xml`.
   - Verifica inclusión de URLs estáticas (`/`, `/products`, `/about-us`, `/blog`, `/contact`, `/faq`).
   - Verifica que un producto publicado aparezca en el XML.
   - Verifica que un producto inactivo NO aparezca en el XML.
   - Verifica que un post de blog publicado aparezca en el XML.
   - Verifica el funcionamiento de la caché de sitemap.
3. **`tests/Feature/Seo/StorefrontMetaTagsTest.php`:**
   - Verifica `<title>`, `<meta name="description">` y `<link rel="canonical">` en Home.
   - Verifica metadatos dinámicos y OpenGraph en `/products/{slug}`.
   - Verifica Schema JSON-LD de `Product` y `Organization`.
