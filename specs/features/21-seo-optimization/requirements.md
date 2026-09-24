# F21 — Optimización SEO & Schema (Técnico, On-Page y Datos Estructurados)

> **Estado:** Completa  
> **ID:** F21 · **Slug:** `21-seo-optimization`  
> **Fase:** 7 · Contenido & Posicionamiento  
> **Prerequisitos:** F01 (Catálogo admin), F01-S (Storefront catálogo), F19 (Blog)  
> **Desbloquea:** Indexación orgánica en Google, enriquecimiento SERP (Rich Snippets) y distribución en redes sociales (OpenGraph)

---

## 1. Fuentes canónicas (no duplicar)

| Tema | Fuente |
|---|---|
| Arquitectura y convenciones de código | [`AGENTS.md`](../../../AGENTS.md) / `.ai/guidelines/project-conventions.md` |
| Especificación SEO audit & buenas prácticas | [`.agents/skills/seo-audit/SKILL.md`](../../../.agents/skills/seo-audit/SKILL.md) |
| Layout Storefront base | [`resources/views/layouts/storefront.blade.php`](../../../resources/views/layouts/storefront.blade.php) |
| Catálogo y Detalle de Producto | [`resources/views/components/product-detail/`](../../../resources/views/components/product-detail/) |
| Catálogo y Listado de Tienda | [`resources/views/components/catalog-list/`](../../../resources/views/components/catalog-list/) |
| Reglas de calidad EARS y trazabilidad | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Dominio canónico de producción | `https://leenhoney.com` |

---

## 2. User Stories

1. **Como** motor de búsqueda (Googlebot, Bingbot), **quiero** acceder a un `/robots.txt` que restrinja rutas privadas (`/admin/`, `/cart`, `/checkout`, `/profile`, `/orders/`, auth) e indique la ubicación de `/sitemap.xml`, **para** optimizar el rastreo y no desperdiciar el *crawl budget*.
2. **Como** motor de búsqueda, **quiero** consultar un sitemap dinámico y válido en `https://leenhoney.com/sitemap.xml` con todas las páginas estáticas, productos publicados, categorías activas y artículos de blog con sus fechas `<lastmod>`, **para** indexar rápidamente el catálogo completo.
3. **Como** visitante y buscador, **quiero** que cada página de la tienda tenga una etiqueta `<link rel="canonical">` que elimine parámetros de consulta superfluos (`?currency=`, `?sort=`, `?page=`), **para** evitar canibalización y penalizaciones por contenido duplicado.
4. **Como** buscador y usuario, **quiero** que las páginas de producto tengan títulos únicos (`<title>`) y meta descripciones semánticas de hasta 155 caracteres generadas a partir del nombre y la descripción existente en base de datos, **para** mejorar la visibilidad y el porcentaje de clics (CTR) en las SERP.
5. **Como** usuario que comparte un producto o artículo en redes sociales o mensajería (WhatsApp, Instagram, Twitter/X), **quiero** que se desplieguen tarjetas OpenGraph y Twitter Cards con título, descripción y fotografía principal en alta resolución, **para** atraer tráfico calificado.
6. **Como** Googlebot, **quiero** recibir datos estructurados JSON-LD en formato Schema.org (`Organization`, `WebSite`, `Product`, `Offer`, `BreadcrumbList`), **para** habilitar fragmentos enriquecidos con precios, disponibilidad y rutas de navegación.
7. **Como** visitante que usa lectores de pantalla o busca por imágenes, **quiero** que las imágenes de productos cuenten con atributos `alt` descriptivos y contextualizados con el nombre y material del producto, **para** mejorar la accesibilidad y el posicionamiento en Google Imágenes.

---

## 3. Criterios de Aceptación (EARS: R1 – R9)

### R1 (Crawlability: Directivas de `robots.txt` y Referencia a Sitemap)
*Cuando* cualquier cliente HTTP o rastreador solicite el archivo `/robots.txt`,  
*el sistema deberá* responder con código HTTP 200 y contener directivas de bloqueo explícitas (`Disallow`) para: `/admin/`, `/cart`, `/checkout`, `/profile`, `/orders/`, `/login`, `/register`, `/forgot-password`, `/reset-password`, `/api/` y `/verify-email/`,  
*y deberá* incluir la directiva absoluta `Sitemap: https://leenhoney.com/sitemap.xml` (o el `config('app.url')/sitemap.xml` configurado).

### R2 (Indexation: Generación Dinámica de `sitemap.xml` con Caché)
*Cuando* se solicite la ruta `GET /sitemap.xml`,  
*el sistema deberá* responder con encabezado `Content-Type: application/xml; charset=utf-8` y código HTTP 200,  
*y deberá* listar todas las URLs públicas canónicas absolutas:
- Páginas estáticas (`/`, `/products`, `/about-us`, `/blog`, `/contact`, `/faq`).
- Todos los productos activos publicados para storefront (`publishedForStorefront`).
- Todas las categorías activas que posean productos asociados.
- Todas las entradas de blog publicadas (`published`).  
*Cada entrada* deberá incluir `<loc>`, `<lastmod>` en formato W3C/ISO 8601 (`Y-m-d\TH:i:sP`) y `<changefreq>`,  
*y el contenido XML* deberá almacenarse en caché de Laravel durante al menos 3600 segundos (1 hora) para mitigar sobrecarga en base de datos.

### R3 (Canonicalization: Inyección de Etiquetas Canónicas en Storefront)
*Cuando* se renderice cualquier vista pública que herede del layout `layouts.storefront`,  
*el sistema deberá* emitir en la sección `<head>` una etiqueta `<link rel="canonical" href="{url_canonica}">`,  
*donde* `{url_canonica}` corresponda a la URL canónica limpia sin parámetros superfluos de consulta (como `?currency=`, `?sort=`, `?page=1`).  
*Si* la página no especifica una canonical explícita,  
*el sistema deberá* usar por defecto la URL actual limpia (`url()->current()`).

### R4 (On-Page SEO: Metadatos Dinámicos en Detalle de Producto)
*Cuando* se acceda al detalle de un producto publicado en `/products/{slug}`,  
*el sistema deberá* inyectar en la etiqueta `<title>` el formato:  
`{Nombre del Producto} | LEEN — Accesorios Artesanales`.  
*Asimismo, el sistema deberá* inyectar la etiqueta `<meta name="description">` extrayendo los primeros 155 caracteres de la descripción existente del producto (`strip_tags` + `Str::squish` + `Str::limit`),  
*y en caso* de que el producto carezca de descripción,  
*el sistema deberá* emitir una descripción de fallback estandarizada y localizada.

### R5 (Social Sharing: OpenGraph y Twitter Cards en Productos y Páginas)
*Cuando* se renderice el detalle de un producto o una página de la tienda,  
*el sistema deberá* emitir en el `<head>`:
- `og:site_name` con el valor `LEEN`.
- `og:type` (`product` para detalle de producto; `website` para el resto).
- `og:title` y `og:description` alineados con los metadatos de la página.
- `og:url` con la URL canónica del producto o página.
- `og:image` con la URL absoluta de la imagen principal del producto (`$product->images->firstWhere('is_primary', true)`) o el logo/banner institucional.
- `twitter:card` con valor `summary_large_image`.

### R6 (Structured Data: Schema.org JSON-LD de Producto y Oferta)
*Cuando* se cargue la vista de detalle de un producto `/products/{slug}`,  
*el sistema deberá* emitir un bloque `<script type="application/ld+json">` con la especificación `Schema.org/Product`, conteniendo:
- `"name"`: nombre del producto.
- `"description"`: descripción semántica.
- `"image"`: URLs de las imágenes del producto.
- `"brand"`: `{"@type": "Brand", "name": "LEEN"}`.
- `"offers"`: objeto `Schema.org/Offer` con `"price"` (monto formateado según unidad menor), `"priceCurrency"` (la moneda activa en storefront), `"availability"` (`InStock` o `PreOrder` según `is_preorder` y stock disponible) y `"url"`.

### R7 (Structured Data: Schema.org JSON-LD de Organización y Breadcrumbs)
*Cuando* se renderice la página principal `/`,  
*el sistema deberá* emitir un bloque `<script type="application/ld+json">` con `Schema.org/Organization` y `Schema.org/WebSite` con nombre de marca, URL canónica y redes sociales.  
*Cuando* se renderice el detalle de un producto o artículo de blog,  
*el sistema deberá* emitir un bloque `Schema.org/BreadcrumbList` que refleje la jerarquía de navegación (Inicio > Tienda > [Categoría] > Producto).

### R8 (Accessibility & Image SEO: Alt Text Semántico en Imágenes de Catálogo)
*Cuando* se rendericen imágenes de productos en el catálogo (`catalog-list`), cards de producto (`product-card`) o detalle (`product-detail`),  
*el sistema deberá* asignar al atributo `alt` un texto contextualizado que combine el nombre del producto, su vista/variante (`Vista principal` o `Detalle {N}`) y la mención de marca `LEEN`,  
*evitando* cadenas genéricas no informativas.

### R9 (Catalog SEO: Título y H1 Dinámico en Colecciones y Categorías)
*Cuando* un usuario navegue en la tienda filtrando por una categoría (`/products?category={slug}`),  
*el sistema deberá* actualizar el `<title>` de la página a `{Nombre de Categoría} | Colección LEEN`,  
*y deberá* renderizar el encabezado principal `<h1>` con el nombre de dicha categoría y un subtítulo semántico, en lugar del título genérico estático de la tienda.

---

## 4. Invariantes y No-Objetivos (Fuera de Alcance)

- **CERO migraciones de base de datos:** No se modificará ninguna tabla existente (`products`, `categories`, `product_images`). Todo el cálculo de metadatos se ejecuta en memoria y Blade.
- **Sin prefijos de ruta multilingüe en F21:** No se alterará la estructura de rutas a `/es/...` o `/en/...` para no romper URLs ni generar redirecciones 301 en producción.
- **Sin paquetes externos pesados:** No se instalarán paquetes sobredimensionados para sitemaps o metadatos; se implementará con Actions nativas de Laravel, Blade components y caché estándar conforme a `AGENTS.md`.
