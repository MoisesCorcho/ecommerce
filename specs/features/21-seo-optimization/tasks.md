# F21 — Plan de Tareas: Optimización SEO & Schema

> **Feature:** F21 · **Slug:** `21-seo-optimization`  
> **Estrategia:** TDD Estricto (Test primero → Implementación → Refactor) y verificación con RDD.  
> **Regla de oro:** Cero commits hasta autorización explícita.

---

## Tareas de Implementación

### Fase 1: Internacionalización de Textos SEO & Configuración Base
- [x] **1.1** Crear archivos de traducción de metadatos:  
  - `lang/es/seo.php` (título base, descripciones de marca, plantillas de producto y categoría).  
  - `lang/en/seo.php` (traducciones correspondientes para inglés). _(cubre R4, R9)_

---

### Fase 2: TDD — Robots.txt & Rastreo
- [x] **2.1** Escribir test feature `tests/Feature/Seo/RobotsTxtTest.php` validando:
  - Código 200 en `/robots.txt`.
  - Presencia de directivas `Disallow: /admin`, `/cart`, `/checkout`, `/profile`, etc.
  - Presencia de la directiva `Sitemap: https://leenhoney.com/sitemap.xml`. _(cubre R1)_
- [x] **2.2** Actualizar el archivo físico `public/robots.txt` con las reglas de bloqueo y la URL canónica del sitemap. _(cubre R1)_
- [x] **2.3** Ejecutar `vendor/bin/sail artisan test --filter=RobotsTxtTest` y confirmar pase en verde.

---

### Fase 3: TDD — Generación Dinámica de Sitemap XML
- [x] **3.1** Escribir test feature `tests/Feature/Seo/SitemapTest.php` validando:
  - Solicitud `GET /sitemap.xml` responde 200 con `Content-Type: application/xml; charset=utf-8`.
  - Estructura XML válida con namespace `<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">`.
  - Inclusión de páginas estáticas principales (`/`, `/products`, `/about-us`, `/blog`, `/contact`, `/faq`).
  - Inclusión de productos publicados para storefront con `<loc>` y `<lastmod>`.
  - Exclusión de productos inactivos o no publicados.
  - Inclusión de posts publicados del blog con `<loc>` y `<lastmod>`.
  - Comportamiento de caché (`seo.sitemap`). _(cubre R2)_
- [x] **3.2** Implementar `App\Actions\Seo\GenerateSitemapAction`:
  - Recopilación de URLs y timestamps ISO 8601.
  - Construcción del payload XML con `Cache::remember('seo.sitemap', 3600, ...)`. _(cubre R2)_
- [x] **3.3** Implementar `App\Http\Controllers\Seo\SitemapController` invokable y registrar la ruta en `routes/web.php` antes del fallback. _(cubre R2)_
- [x] **3.4** Ejecutar `vendor/bin/sail artisan test --filter=SitemapTest` y confirmar pase en verde.

---

### Fase 4: TDD — Layout Storefront & Meta Tags Globales (Canonicals & OpenGraph)
- [x] **4.1** Escribir test feature `tests/Feature/Seo/StorefrontMetaTagsTest.php` validando:
  - La página de inicio `/` emite `<link rel="canonical" href="https://leenhoney.com">`.
  - La página de inicio emite `<title>` y `<meta name="description">` por defecto.
  - La página de inicio emite `og:site_name`, `og:title`, `og:type` y `twitter:card`.
  - La página de inicio emite `Schema.org/Organization` y `Schema.org/WebSite`. _(cubre R3, R5, R7)_
- [x] **4.2** Actualizar `resources/views/layouts/storefront.blade.php`:
  - Inyectar `<link rel="canonical">` defensivo.
  - Inyectar metadatos por defecto y tags OpenGraph / Twitter Cards.
  - Crear componente `resources/views/components/seo/organization-schema.blade.php` e incluirlo condicionalmente en el Home. _(cubre R3, R5, R7)_
- [x] **4.3** Ejecutar `vendor/bin/sail artisan test --filter=StorefrontMetaTagsTest` y validar pase.

---

### Fase 5: TDD — Detalle de Producto (Titles, Descriptions, JSON-LD & Alt Texts)
- [x] **5.1** Escribir test en `tests/Feature/Seo/ProductSeoTest.php` validando:
  - En `/products/{slug}`, el `<title>` se forma como `{Nombre} | LEEN — Accesorios Artesanales`.
  - La `<meta name="description">` limpia y trunca a 155 caracteres la descripción del producto.
  - La etiqueta canonical apunta exactamente a `route('products.show', $product->slug)`.
  - Se emiten los tags OpenGraph `og:type=product` y `og:image` con la foto principal.
  - Se renderiza el bloque JSON-LD con `Schema.org/Product` y `Schema.org/Offer`. _(cubre R4, R5, R6, R7)_
- [x] **5.2** Crear componente Blade `resources/views/components/seo/product-schema.blade.php` con Schema JSON-LD de Producto y Oferta. _(cubre R6)_
- [x] **5.3** Crear componente Blade `resources/views/components/seo/breadcrumbs-schema.blade.php` con Schema JSON-LD de BreadcrumbList. _(cubre R7)_
- [x] **5.4** Actualizar `resources/views/components/product-detail/product-detail.php` y `product-detail.blade.php`:
  - Inyectar título dinámico en `render()`.
  - Poner metadatos en `@push('meta')`.
  - Enriquecer los atributos `alt` de las imágenes de producto. _(cubre R4, R5, R6, R7, R8)_
- [x] **5.5** Ejecutar `vendor/bin/sail artisan test --filter=ProductSeoTest` y validar pase en verde.

---

### Fase 6: TDD — Catálogo & Colecciones
- [x] **6.1** Escribir test en `tests/Feature/Seo/CatalogSeoTest.php` validando:
  - En `/products?category={slug}`, el `<title>` se actualiza con el nombre de la categoría.
  - El encabezado `<h1>` renderiza el nombre de la categoría en lugar del genérico "Tienda". _(cubre R9)_
- [x] **6.2** Actualizar `resources/views/components/catalog-list/catalog-list.php` y `catalog-list.blade.php` para reflejar título y H1 contextualizado. _(cubre R9)_
- [x] **6.3** Ejecutar `vendor/bin/sail artisan test --filter=CatalogSeoTest` y validar pase en verde.

---

### Fase 7: Calidad, Formato & QA
- [x] **7.1** Ejecutar formateador de código Pint: `vendor/bin/sail bin pint --dirty --format agent`.
- [x] **7.2** Ejecutar suite completa de tests de SEO: `vendor/bin/sail artisan test --filter=Seo`.
- [x] **7.3** Generar checklist de QA exhaustivo para handoff.

---

## Definition of Done (DoD)
- [x] Todos los criterios R1 a R9 cubiertos por tests automatizados que pasan al 100%.
- [x] Cero migraciones de base de datos creadas o ejecutadas.
- [x] Formateo con Laravel Pint limpio (`--dirty`).
- [x] Cero commits en git (respetando la restricción de usuario).
