# Tareas de Implementación — F-04: Pop-up Promocional Administrable

> **Feature:** `16-promotional-popup` (F-04)  
> **Estrategia:** TDD Estricto (Red-Green-Refactor)

---

## Checklist de Tareas

### Fase 1: Dominio, Modelo y Migración (TDD)
- [x] **T1.1 (RED):** Crear test de modelo `tests/Feature/Domain/PromotionalPopupTest.php`:
  - Atributos, casts (`is_active`, `sort_order`, `delay_seconds`, fechas) y relación con `Coupon`.
  - Integración con Spatie Translatable (`HasTranslations`) para `title`, `subtitle` y `cta_text`.
  - Scopes `active()` (filtro por `is_active`, `starts_at` y `ends_at`) y `ordered()` (`sort_order ASC, id DESC`).
  - Métodos auxiliares (`getLocalizedTitle()`, `getLocalizedSubtitle()`, `getLocalizedCtaText()`, `hasValidCoupon()`). _(cubre R1, R4, R5)_
- [x] **T1.2 (GREEN):** Crear migración `create_promotional_popups_table`, modelo `App\Models\PromotionalPopup` con `HasTranslations` y factory `PromotionalPopupFactory`. _(cubre R1, R4, R5)_
- [x] **T1.3:** Ejecutar y verificar tests verdes de dominio: `vendor/bin/sail artisan test --compact tests/Feature/Domain/PromotionalPopupTest.php`.

---

### Fase 2: Internacionalización (i18n)
- [x] **T2.1:** Crear `lang/es/promotional_popups.php` y `lang/en/promotional_popups.php` con etiquetas de formularios, tablas, badges y mensajes de cliente. _(cubre R5, R6)_

---

### Fase 3: Filament Admin Resource (TDD)
- [x] **T3.1 (RED):** Crear test de Filament `tests/Feature/Filament/PromotionalPopupResourceTest.php`:
  - Acceso denegado a no-admins (403).
  - Acceso permitido a administradores (200).
  - Creación y edición con pestañas dinámicas por idioma (`LocaleEnum`), vinculación a cupones, campo `sort_order` y subida de imagen. _(cubre R6, R7)_
- [x] **T3.2 (GREEN):** Implementar `App\Filament\Resources\PromotionalPopups\PromotionalPopupResource.php` descompuesto en `Schemas/PromotionalPopupForm.php` y `Schemas/PromotionalPopupsTable.php` con sus páginas. _(cubre R6, R7)_
- [x] **T3.3:** Ejecutar y verificar tests verdes de Filament: `vendor/bin/sail artisan test --compact tests/Feature/Filament/PromotionalPopupResourceTest.php`.

---

### Fase 4: Storefront y Componente Modal Alpine.js (TDD)
- [x] **T4.1 (RED):** Crear test de Storefront `tests/Feature/Storefront/PromotionalPopupTest.php`:
  - Renderiza modal cuando hay un pop-up activo (con eager loading de `coupon`).
  - Renderiza cupón y botón de copiado si tiene `coupon_id` válido y no expirado.
  - Oculta bloque de cupón si el cupón está inactivo/expirado (`hasValidCoupon() === false`).
  - Respeta el idioma activo (`es`/`en`) con fallback a `es`.
  - No renderiza si no hay pop-ups activos o vigentes. _(cubre R1, R3, R4, R5)_
- [x] **T4.2 (GREEN):** Crear componente `resources/views/components/promotional-popup.blade.php` con Alpine.js (`delay_seconds`, copiado a portapapeles y descarte en `localStorage` con expiración de 7 días por timestamp). _(cubre R2, R3, R4)_
- [x] **T4.3 (GREEN):** Incluir `<x-promotional-popup />` en `resources/views/layouts/storefront.blade.php`. _(cubre R1, R2)_
- [x] **T4.4:** Ejecutar y verificar tests verdes de Storefront: `vendor/bin/sail artisan test --compact tests/Feature/Storefront/PromotionalPopupTest.php`.

---

### Fase 5: Calidad, Formateo y Generación de QA Checklist
- [x] **T5.1:** Ejecutar suite completa de la feature: `vendor/bin/sail artisan test --compact --filter=PromotionalPopup`.
- [x] **T5.2:** Ejecutar Pint: `vendor/bin/sail bin pint --dirty --format agent`.
- [x] **T5.3:** Generar Checklist de QA exhaustivo mediante la skill `feature-qa-checklist` (o `/qa-checklist`).
- [x] **T5.4:** Actualizar estado en roadmap a `Completa`.

