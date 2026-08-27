# Tareas de Implementación — F-07: Barra de Anuncios Administrable

> **Feature:** `15-announcement-bar` (F-07)  
> **Estrategia:** TDD Estricto (Red-Green-Refactor)

---

## Checklist de Tareas

### Fase 0: Dependencias y Configuración Base
- [x] **T0.1:** Instalar paquetes requeridos vía Composer:
  `vendor/bin/sail composer require spatie/laravel-translatable`
- [x] **T0.2:** Configuración de soporte translatable bilingüe en modelo y schemas Filament.

---

### Fase 1: Dominio, Modelo y Migración (TDD)
- [x] **T1.1 (RED):** Crear test de modelo `tests/Feature/Domain/AnnouncementTest.php` con casos para:
  - Creación con array de idiomas en `text` (`['es' => '...', 'en' => '...']`) y casteo de atributos.
  - Scope `active()` (exclusión por `is_active=false`, `starts_at` futuro, `ends_at` pasado).
  - Scope `ordered()` (orden por `sort_order` asc y fallback `id` desc).
  - Método `getLocalizedText()` según locale activo y fallback a `es`. _(cubre R1, R2, R3)_
- [x] **T1.2 (GREEN):** Crear migración `create_announcements_table` con columna JSON `text`, modelo `App\Models\Announcement` (con `HasTranslations`) y factory `AnnouncementFactory`. _(cubre R1, R2, R3)_
- [x] **T1.3:** Ejecutar y verificar tests verdes de dominio: `vendor/bin/sail artisan test --compact tests/Feature/Domain/AnnouncementTest.php`.

---

### Fase 2: Internacionalización (i18n)
- [x] **T2.1:** Crear `lang/es/announcements.php` y `lang/en/announcements.php` con todas las claves de formulario, tabla y storefront (`fields.text`, `close`, etc.). _(cubre R3, R7)_

---

### Fase 3: Filament Admin Resource (TDD)
- [x] **T3.1 (RED):** Crear test de Filament `tests/Feature/Filament/AnnouncementResourceTest.php`:
  - Acceso denegado a no administradores (403).
  - Acceso permitido a administradores con renderizado de tabla y selector de idioma.
  - Creación y validación de campo requerido `text`.
  - Validación de regla `ends_at >= starts_at`.
  - Edición y toggle activo directo en tabla. _(cubre R7, R8)_
- [x] **T3.2 (GREEN):** Implementar `App\Filament\Resources\Announcements\AnnouncementResource.php`, `Schemas/AnnouncementForm.php`, `Schemas/AnnouncementsTable.php` y páginas con trait `Translatable`. _(cubre R7, R8)_
- [x] **T3.3:** Ejecutar y verificar tests verdes de Filament: `vendor/bin/sail artisan test --compact tests/Feature/Filament/AnnouncementResourceTest.php`.

---

### Fase 4: Storefront y Componente Blade / Alpine (TDD)
- [x] **T4.1 (RED):** Crear test de integración storefront `tests/Feature/Storefront/AnnouncementBarTest.php`:
  - Renderiza el anuncio activo en el layout storefront.
  - Muestra texto en español cuando locale es `es`, y en inglés cuando locale es `en` (o fallback a `es` si `en` no existe).
  - Incluye enlace si `url` existe (con `target="_blank"` si es externa), o solo texto si `url` es nulo.
  - No renderiza nada si no hay anuncios activos o están fuera de vigencia. _(cubre R1, R2, R3, R4)_
- [x] **T4.2 (GREEN):** Crear componente `resources/views/components/announcement-bar.blade.php` con lógica Alpine.js (`x-cloak`, dismiss y persistencia en `localStorage`). _(cubre R3, R4, R5, R6)_
- [x] **T4.3 (GREEN):** Integrar `<x-announcement-bar />` en `resources/views/layouts/storefront.blade.php`. _(cubre R1, R6)_
- [x] **T4.4:** Ejecutar y verificar tests verdes de Storefront: `vendor/bin/sail artisan test --compact tests/Feature/Storefront/AnnouncementBarTest.php`.

---

### Fase 5: Estilo, Refactor y Definition of Done
- [x] **T5.1:** Ejecutar suite completa de tests de la feature:
  `vendor/bin/sail artisan test --compact --filter=Announcement`
- [x] **T5.2:** Ejecutar Pint para formateo limpio:
  `vendor/bin/sail bin pint --dirty --format agent`
- [x] **T5.3:** Actualizar estado en specs a `Completa`.


---

## Mapa de Trazabilidad (Criterios ↔ Tareas)

| Criterio | Tareas de Implementación | Tests Asociados |
|---|---|---|
| **R1 (Vigencia y Estado)** | T1.2, T4.3 | `AnnouncementTest`, `AnnouncementBarTest` |
| **R2 (Prioridad y Orden)** | T1.2, T4.3 | `AnnouncementTest` |
| **R3 (Localización y Fallback)** | T0.1, T0.2, T1.2, T2.1, T4.2 | `AnnouncementTest`, `AnnouncementBarTest` |
| **R4 (Enlaces e Interactividad)** | T4.2 | `AnnouncementBarTest` |
| **R5 (Persistencia de Cierre)** | T4.2 | `AnnouncementBarTest` |
| **R6 (Reaparición ante Nuevo ID)** | T4.2, T4.3 | `AnnouncementBarTest` |
| **R7 (Gestión Filament Translatable)** | T3.2 | `AnnouncementResourceTest` |
| **R8 (Control de Acceso 403)** | T3.2 | `AnnouncementResourceTest` |

---

## Definition of Done (DoD)

1. Rango completo de criterios de aceptación (**R1 a R8**) cubierto por tests automatizados passing (`AnnouncementTest`, `AnnouncementResourceTest`, `AnnouncementBarTest`).
2. Cero errores de formateo con Laravel Pint (`vendor/bin/sail bin pint --dirty --format agent`).
3. Tipado estricto (`declare(strict_types=1);`) en todas las clases y schemas PHP nuevos.
4. i18n 100% traducido en `lang/es/announcements.php` y `lang/en/announcements.php` sin strings hardcodeados.
5. Cobertura completa de estados (activo, inactivo, vigencia futura, vencida, con URL, sin URL, descarte en cliente).

