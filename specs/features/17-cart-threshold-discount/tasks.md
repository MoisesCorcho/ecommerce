# Tareas de Implementación — F-05: Regla de Descuento Progresivo en Carrito

> **Feature:** `17-cart-threshold-discount` (F-05)  
> **Estrategia:** TDD Estricto (Red-Green-Refactor)

---

## Checklist de Tareas

### Fase 1: Dominio, Pricing y DTOs (TDD)
- [x] **T1.1 (RED):** Crear test unitario `tests/Feature/Domain/CartThresholdDiscountTest.php`:
  - Evaluación de umbrales EUR (300 €), USD (320 $) y COP ($1.200.000).
  - Cálculo exacto del 10% en `CartPricingService`.
  - Cálculo de monto restante para alcanzar el beneficio. _(cubre R1, R2)_
- [x] **T1.2 (GREEN):** Actualizar `CurrencyEnum` con método `thresholdDiscountMinAmount()`, actualizar `CartViewDTO` e implementar la lógica en `CartPricingService`. _(cubre R1, R2)_
- [x] **T1.3:** Ejecutar y verificar tests verdes de pricing: `vendor/bin/sail artisan test --compact tests/Feature/Domain/CartThresholdDiscountTest.php`.

---

### Fase 2: Checkout, Órdenes e Inmutabilidad (TDD)
- [x] **T2.1 (RED):** Crear test de checkout con descuento por umbral `tests/Feature/Orders/OrderThresholdDiscountTest.php`. _(cubre R4, R5)_
- [x] **T2.2 (GREEN):** Adaptar `CreateOrderFromCartAction` y migración si aplica para congelar el descuento de umbral en `Order`. _(cubre R5)_
- [x] **T2.3:** Ejecutar y verificar tests verdes de órdenes: `vendor/bin/sail artisan test --compact tests/Feature/Orders/OrderThresholdDiscountTest.php`.

---

### Fase 3: Storefront & Livewire UI (TDD)
- [x] **T3.1 (RED):** Crear test de Livewire para página de carrito y checkout `tests/Feature/Cart/CartThresholdLivewireTest.php`. _(cubre R3)_
- [x] **T3.2 (GREEN):** Integrar banner de progreso y mensajes bilingües en `cart-page.blade.php` y `checkout-page.blade.php`. _(cubre R3)_
- [x] **T3.3:** Ejecutar y verificar tests verdes de UI: `vendor/bin/sail artisan test --compact tests/Feature/Cart/CartThresholdLivewireTest.php`.

---

### Fase 4: Estilo, Refactor y Definition of Done
- [x] **T4.1:** Ejecutar suite completa de carrito y órdenes: `vendor/bin/sail artisan test --compact`
- [x] **T4.2:** Ejecutar Pint: `vendor/bin/sail bin pint --dirty --format agent`
- [x] **T4.3:** Actualizar estado en roadmap a `Completa`.
