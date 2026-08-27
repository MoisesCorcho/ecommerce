# Tareas de Implementación — F-04: Pop-up Promocional Administrable

> **Feature:** `16-promotional-popup` (F-04)  
> **Estrategia:** TDD Estricto (Red-Green-Refactor)

---

## Checklist de Tareas

### Fase 1: Dominio, Modelo y Migración (TDD)
- [ ] **T1.1 (RED):** Crear test de modelo `tests/Feature/Domain/PromotionalPopupTest.php`:
  - Atributos, casts y relación con `Coupon`.
  - Scope `active()` (filtro por `is_active`, `starts_at` y `ends_at`).
  - Métodos localizados (`getLocalizedTitle()`, `getLocalizedSubtitle()`). _(cubre R1, R4, R5)_
- [ ] **T1.2 (GREEN):** Crear migración `create_promotional_popups_table`, modelo `App\Models\PromotionalPopup` y factory. _(cubre R1, R4, R5)_
- [ ] **T1.3:** Ejecutar y verificar tests verdes de dominio: `vendor/bin/sail artisan test --compact tests/Feature/Domain/PromotionalPopupTest.php`.

---

### Fase 2: Internacionalización (i18n)
- [ ] **T2.1:** Crear `lang/es/promotional_popups.php` y `lang/en/promotional_popups.php`. _(cubre R5, R6)_

---

### Fase 3: Filament Admin Resource (TDD)
- [ ] **T3.1 (RED):** Crear test de Filament `tests/Feature/Filament/PromotionalPopupResourceTest.php`:
  - Acceso denegado a no-admins (403).
  - Acceso permitido a administradores.
  - Creación y edición con vinculación a cupones y subida de imagen. _(cubre R6, R7)_
- [ ] **T3.2 (GREEN):** Implementar `App\Filament\Resources\PromotionalPopups\PromotionalPopupResource.php`. _(cubre R6, R7)_
- [ ] **T3.3:** Ejecutar y verificar tests verdes de Filament: `vendor/bin/sail artisan test --compact tests/Feature/Filament/PromotionalPopupResourceTest.php`.

---

### Fase 4: Storefront y Componente Modal Alpine.js (TDD)
- [ ] **T4.1 (RED):** Crear test de Storefront `tests/Feature/Storefront/PromotionalPopupTest.php`:
  - Renderiza modal cuando hay un pop-up activo.
  - Renderiza cupón y botón de copiado si tiene `coupon_id`.
  - Respeta el idioma activo (`es`/`en`).
  - No renderiza si no hay pop-ups activos. _(cubre R1, R3, R4, R5)_
- [ ] **T4.2 (GREEN):** Crear componente `resources/views/components/promotional-popup.blade.php` con Alpine.js (`delay_seconds`, copiado a portapapeles y `localStorage`). _(cubre R2, R3, R4)_
- [ ] **T4.3 (GREEN):** Incluir `<x-promotional-popup />` en `resources/views/layouts/storefront.blade.php`. _(cubre R1, R2)_
- [ ] **T4.4:** Ejecutar y verificar tests verdes de Storefront: `vendor/bin/sail artisan test --compact tests/Feature/Storefront/PromotionalPopupTest.php`.

---

### Fase 5: Estilo, Refactor y Definition of Done
- [ ] **T5.1:** Ejecutar suite completa: `vendor/bin/sail artisan test --compact --filter=PromotionalPopup`
- [ ] **T5.2:** Ejecutar Pint: `vendor/bin/sail bin pint --dirty --format agent`
- [ ] **T5.3:** Actualizar estado en roadmap a `Completa`.
