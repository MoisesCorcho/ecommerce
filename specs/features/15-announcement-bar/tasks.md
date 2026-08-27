# Tareas de Implementación — F-07: Barra de Anuncios Administrable

> **Feature:** `15-announcement-bar` (F-07)  
> **Estrategia:** TDD Estricto (Red-Green-Refactor)

---

## Checklist de Tareas

### Fase 1: Dominio, Modelo y Migración (TDD)
- [ ] **T1.1 (RED):** Crear test de modelo `tests/Feature/Domain/AnnouncementTest.php` con casos para:
  - Creación y casteo de atributos.
  - Scope `active()` (exclusión por `is_active=false`, `starts_at` futuro, `ends_at` pasado).
  - Scope `ordered()` (orden por `sort_order` asc y fallback `id` desc).
  - Método `getLocalizedText()` según locale activo. _(cubre R1, R2, R3)_
- [ ] **T1.2 (GREEN):** Crear migración `create_announcements_table`, modelo `App\Models\Announcement` y factory `AnnouncementFactory`. _(cubre R1, R2, R3)_
- [ ] **T1.3:** Ejecutar y verificar tests verdes de dominio: `vendor/bin/sail artisan test --compact tests/Feature/Domain/AnnouncementTest.php`.

---

### Fase 2: Internacionalización (i18n)
- [ ] **T2.1:** Crear `lang/es/announcements.php` y `lang/en/announcements.php` con todas las claves de formulario, tabla y storefront. _(cubre R3, R7)_

---

### Fase 3: Filament Admin Resource (TDD)
- [ ] **T3.1 (RED):** Crear test de Filament `tests/Feature/Filament/AnnouncementResourceTest.php`:
  - Acceso denegado a no administradores (403).
  - Acceso permitido a administradores con renderizado de tabla.
  - Creación y validación de campos (`text_es`, `text_en`).
  - Edición y toggle activo. _(cubre R7, R8)_
- [ ] **T3.2 (GREEN):** Implementar `App\Filament\Resources\Announcements\AnnouncementResource.php` con sus páginas de lista, creación y edición. _(cubre R7, R8)_
- [ ] **T3.3:** Ejecutar y verificar tests verdes de Filament: `vendor/bin/sail artisan test --compact tests/Feature/Filament/AnnouncementResourceTest.php`.

---

### Fase 4: Storefront y Componente Blade / Alpine (TDD)
- [ ] **T4.1 (RED):** Crear test de integración storefront `tests/Feature/Storefront/AnnouncementBarTest.php`:
  - Renderiza el anuncio activo en el layout storefront.
  - Muestra texto en español cuando locale es `es`, y en inglés cuando locale es `en`.
  - Incluye enlace si `url` existe, o solo texto si `url` es nulo.
  - No renderiza nada si no hay anuncios activos o están fuera de vigencia. _(cubre R1, R2, R3, R4)_
- [ ] **T4.2 (GREEN):** Crear componente `resources/views/components/announcement-bar.blade.php` con lógica Alpine.js para dismiss y persistencia en `localStorage`. _(cubre R3, R4, R5, R6)_
- [ ] **T4.3 (GREEN):** Integrar `<x-announcement-bar />` en `resources/views/layouts/storefront.blade.php`. _(cubre R1, R6)_
- [ ] **T4.4:** Ejecutar y verificar tests verdes de Storefront: `vendor/bin/sail artisan test --compact tests/Feature/Storefront/AnnouncementBarTest.php`.

---

### Fase 5: Estilo, Refactor y Definition of Done
- [ ] **T5.1:** Ejecutar suite completa de tests de la feature:
  `vendor/bin/sail artisan test --compact --filter=Announcement`
- [ ] **T5.2:** Ejecutar Pint para formateo limpio:
  `vendor/bin/sail bin pint --dirty --format agent`
- [ ] **T5.3:** Actualizar estado en roadmap y specs a `Completa`.

---

## Definition of Done (DoD)

1. Todos los criterios EARS (R1 a R8) cubiertos por tests automatizados passing.
2. Cero errores de Pint.
3. Tipado estricto (`declare(strict_types=1);`) en todas las clases PHP nuevas.
4. i18n 100% traducido en `es` y `en` sin strings hardcodeados.
