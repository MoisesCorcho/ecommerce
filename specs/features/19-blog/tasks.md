# Tareas de Implementación — F-01: Módulo de Blog Completo

> **Feature:** `19-blog` (F-01)  
> **Estrategia:** TDD Estricto (Red-Green-Refactor)

---

## Checklist de Tareas

### Fase 1: Dominio, Modelos y Migraciones (TDD)
- [ ] **T1.1 (RED):** Crear test de modelos `tests/Feature/Domain/BlogTest.php`:
  - Atributos, relaciones y casts de `PostCategory` y `Post`.
  - Scope `published()` (exclusión de `draft` y `published_at` futuro).
  - Métodos bilingües y `readingTime()`. _(cubre R1, R2, R3)_
- [ ] **T1.2 (GREEN):** Crear migraciones `create_post_categories_table` y `create_posts_table`, modelos `App\Models\PostCategory` y `App\Models\Post`, y factories. _(cubre R1, R2, R3)_
- [ ] **T1.3:** Ejecutar tests verdes de dominio: `vendor/bin/sail artisan test --compact tests/Feature/Domain/BlogTest.php`.

---

### Fase 2: Filament Admin Panel (TDD)
- [ ] **T2.1 (RED):** Crear tests de Filament `tests/Feature/Filament/BlogResourceTest.php`:
  - Acceso denegado a no-admins (403).
  - CRUD de categorías en `PostCategoryResource`.
  - CRUD de artículos con RichEditor, slugging y subida de imagen en `PostResource`. _(cubre R6)_
- [ ] **T2.2 (GREEN):** Implementar `PostCategoryResource` y `PostResource` en Filament v5. _(cubre R6)_
- [ ] **T2.3:** Ejecutar tests verdes de Filament: `vendor/bin/sail artisan test --compact tests/Feature/Filament/BlogResourceTest.php`.

---

### Fase 3: Storefront & Vistas Públicas (TDD)
- [ ] **T3.1 (RED):** Crear test de integración storefront `tests/Feature/Storefront/BlogStorefrontTest.php`:
  - Listado paginado en `/blog`.
  - Filtrado por categoría.
  - Detalle en `/blog/{slug}` con artículos relacionados y 404 para borradores.
  - Renderizado bilingüe y metatags SEO. _(cubre R1, R2, R3, R4, R5, R7)_
- [ ] **T3.2 (GREEN):** Implementar controlador/Livewire para `/blog` y `/blog/{slug}`, con componentes Blade responsive y tags OpenGraph. _(cubre R1, R2, R3, R4, R5, R7)_
- [ ] **T3.3:** Ejecutar tests verdes de Storefront: `vendor/bin/sail artisan test --compact tests/Feature/Storefront/BlogStorefrontTest.php`.

---

### Fase 4: Estilo, Refactor y Definition of Done
- [ ] **T4.1:** Ejecutar suite completa: `vendor/bin/sail artisan test --compact --filter=Blog`
- [ ] **T4.2:** Ejecutar Pint: `vendor/bin/sail bin pint --dirty --format agent`
- [ ] **T4.3:** Actualizar estado en roadmap a `Completa`.
