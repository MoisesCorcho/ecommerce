# Tareas de Implementación — F-03: Notificaciones Automáticas de Wishlist

> **Feature:** `18-wishlist-alerts` (F-03)  
> **Estrategia:** TDD Estricto (Red-Green-Refactor)

---

## Checklist de Tareas

### Fase 1: Base de Datos y Logs (TDD)
- [ ] **T1.1 (RED):** Crear test de persistencia de logs `tests/Feature/Domain/WishlistNotificationLogTest.php`. _(cubre R5)_
- [ ] **T1.2 (GREEN):** Crear migración `create_wishlist_notification_logs_table` y modelo `App\Models\WishlistNotificationLog`. _(cubre R5)_
- [ ] **T1.3:** Ejecutar tests verdes de log: `vendor/bin/sail artisan test --compact tests/Feature/Domain/WishlistNotificationLogTest.php`.

---

### Fase 2: Mailables y Plantillas de Correo (TDD)
- [ ] **T2.1 (RED):** Crear test de renderizado de mails `tests/Feature/Mail/WishlistMailsTest.php`. _(cubre R4)_
- [ ] **T2.2 (GREEN):** Crear mailables `WishlistPriceDropMail` y `WishlistLowStockMail` con sus respectivas vistas Blade responsive con branding Leen. _(cubre R4)_
- [ ] **T2.3:** Ejecutar tests verdes de mails: `vendor/bin/sail artisan test --compact tests/Feature/Mail/WishlistMailsTest.php`.

---

### Fase 3: Comando Artisan y Scheduler (TDD)
- [ ] **T3.1 (RED):** Crear test de comando `tests/Feature/Console/SendWishlistAlertsCommandTest.php`:
  - Detección de rebaja de precio.
  - Detección de stock crítico (< 3).
  - Rate-limit de 7 días (anti-spam). _(cubre R1, R2, R3)_
- [ ] **T3.2 (GREEN):** Implementar `App\Console\Commands\SendWishlistAlertsCommand.php` y registrar en `routes/console.php`. _(cubre R1, R2, R3)_
- [ ] **T3.3:** Ejecutar tests verdes de comando: `vendor/bin/sail artisan test --compact tests/Feature/Console/SendWishlistAlertsCommandTest.php`.

---

### Fase 4: Estilo, Refactor y Definition of Done
- [ ] **T4.1:** Ejecutar suite completa: `vendor/bin/sail artisan test --compact --filter=Wishlist`
- [ ] **T4.2:** Ejecutar Pint: `vendor/bin/sail bin pint --dirty --format agent`
- [ ] **T4.3:** Actualizar estado en roadmap a `Completa`.
