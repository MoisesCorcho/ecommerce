# Tareas de Implementación — F-01: Módulo de Blog Completo

> **Feature:** `19-blog` (F-01)  
> **Estrategia:** TDD Estricto (Red-Green-Refactor)  
> **Fuentes Canónicas:** [`requirements.md`](requirements.md), [`design.md`](design.md), [`specs/ui-briefs/19-blog.md`](../../ui-briefs/19-blog.md)

---

## Checklist de Tareas

### Fase 1: Dominio, Enum, Modelos y Migraciones (TDD)
- [ ] **T1.1 (RED):** Crear test de dominio y modelos en `tests/Feature/Domain/BlogTest.php`:
  - Casts, enum `PostStatusEnum`, relaciones (`author`, `category`, `posts`).
  - Scopes `published()`, `active()`, `ordered()`.
  - Helpers bilingües con fallback a `es` cuando `en` está vacío.
  - Cálculo dinámico de tiempo de lectura (`readingTime()`).
  - Hook de purga de imagen física en `Storage::disk('public')` al eliminar un post. _(cubre R1, R2, R3, R4, R6, R7)_
- [ ] **T1.2 (GREEN):** Implementar enum `App\Enums\Blog\PostStatusEnum`, migraciones `create_post_categories_table` y `create_posts_table`, modelos `App\Models\PostCategory` y `App\Models\Post`, y factories `PostCategoryFactory` y `PostFactory`. _(cubre R1, R2, R3, R4, R6, R7)_
- [ ] **T1.3:** Ejecutar tests verdes de dominio: `vendor/bin/sail artisan test --compact tests/Feature/Domain/BlogTest.php`.

---

### Fase 2: Filament Admin Panel Descompuesto (TDD)
- [ ] **T2.1 (RED):** Crear tests de Filament en `tests/Feature/Filament/BlogResourceTest.php`:
  - Control de acceso (403 para usuarios no administradores).
  - CRUD de categorías en `PostCategoryResource` con `sort_order` y pestañas i18n.
  - CRUD de artículos en `PostResource` con `PostStatusEnum`, RichEditor bilingüe, slugging reactivo, subida de portada y campos SEO. _(cubre R7)_
- [ ] **T2.2 (GREEN):** Implementar recursos descompuestos en `app/Filament/Resources/Blog/`:
  - `PostCategoryResource` + Schemas (`PostCategoryForm`, `PostCategoriesTable`) + Pages.
  - `PostResource` + Schemas (`PostForm`, `PostsTable`) + Pages.
  - Claves de traducción para Filament y Enums en `lang/{es,en}/enums.php`, `navigation.php`. _(cubre R7)_
- [ ] **T2.3:** Ejecutar tests verdes de Filament: `vendor/bin/sail artisan test --compact tests/Feature/Filament/BlogResourceTest.php`.

---

### Fase 3: Storefront, Vistas Públicas & Design Tokens (TDD)
- [ ] **T3.1 (RED):** Crear test de integración storefront en `tests/Feature/Storefront/BlogStorefrontTest.php`:
  - Listado paginado en `/blog` con sólo artículos publicados.
  - Filtrado reactivo por categoría `/blog?category={slug}`.
  - Detalle en `/blog/{slug}` accesible para publicados y 404 para borradores ante no-admins.
  - Detalle en `/blog/{slug}` accesible para administradores aún en borrador.
  - Artículos relacionados (hasta 3 con fallback a posts recientes).
  - Inyección de metatags OpenGraph y SEO en HTML head.
  - Conmutación dinámica de idioma en títulos y contenidos. _(cubre R1, R2, R3, R4, R5, R8)_
- [ ] **T3.2 (GREEN):** Implementar vistas y componentes Livewire v4 MFC:
  - Componente `resources/views/components/blog-index/` (`blog-index.php` + `blog-index.blade.php`).
  - Componente `resources/views/components/blog-show/` (`blog-show.php` + `blog-show.blade.php`).
  - Registrar rutas en `routes/web.php` e integrar enlaces de navegación en `resources/views/layouts/storefront.blade.php`.
  - Crear archivos de traducción `lang/{es,en}/blog.php` y actualizar `storefront.php`, `navigation.php`.
  - Aplicar maquetación editorial premium, tokens de color (`Silk Cream`, `Soft Sand`, `Intense Cocoa`, `Soft Gold`) y tipografía (`Chillax`, `Montserrat`, `La Belle Aurore`) según `specs/ui-briefs/19-blog.md`. _(cubre R1, R2, R3, R4, R5, R8, R9)_
- [ ] **T3.3:** Ejecutar tests verdes de Storefront: `vendor/bin/sail artisan test --compact tests/Feature/Storefront/BlogStorefrontTest.php`.

---

### Fase 4: Estilo, Refactor y Definition of Done
- [ ] **T4.1:** Ejecutar suite completa del módulo: `vendor/bin/sail artisan test --compact --filter=Blog`
- [ ] **T4.2:** Ejecutar formateo de código con Pint: `vendor/bin/sail bin pint --dirty --format agent`
- [ ] **T4.3:** Generar checklist de verificación de QA con la skill `feature-qa-checklist` y actualizar estado en roadmap a `Completa`.
