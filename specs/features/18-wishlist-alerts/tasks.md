# Tareas de Implementación — F18: Notificaciones Automáticas de Wishlist

> **Feature:** `18-wishlist-alerts` (F18 / F-03)  
> **Estrategia:** TDD Estricto (Red-Green-Refactor)

---

## Checklist de Tareas

### Fase 1: Esquema de Base de Datos, Enums y Modelos (TDD)
- [x] **T1.1 (RED):** Escribir pruebas unitarias y de persistencia para el enum y logs en `tests/Feature/Domain/WishlistNotificationLogTest.php`. _(cubre R3)_
- [x] **T1.2 (GREEN):** Crear enum `app/Enums/Wishlist/WishlistNotificationTypeEnum.php`, migraciones `add_price_and_currency_to_wishlists_table` y `create_wishlist_notification_logs_table`, y modelo `app/Models/WishlistNotificationLog.php`. Actualizar fillables y casts en `app/Models/Wishlist.php`. _(cubre R3)_
- [x] **T1.3 (VERIFY):** Ejecutar tests de persistencia y enums: `vendor/bin/sail artisan test --compact tests/Feature/Domain/WishlistNotificationLogTest.php`.

---

### Fase 2: Snapshot de Precio y Moneda en Wishlist (TDD)
- [x] **T2.1 (RED):** Extender `tests/Feature/Wishlist/ToggleWishlistActionTest.php` para verificar la captura de `price_when_added` y `currency_when_added` al guardar un favorito. _(cubre R5)_
- [x] **T2.2 (GREEN):** Actualizar `app/Actions/Wishlist/ToggleWishlistAction.php` para resolver la moneda activa (`CurrencyEnum`) y almacenar el precio de la variante en dicha moneda. _(cubre R5)_
- [x] **T2.3 (VERIFY):** Ejecutar tests de toggle: `vendor/bin/sail artisan test --compact tests/Feature/Wishlist/ToggleWishlistActionTest.php`.

---

### Fase 3: Mailables, Plantillas Blade e i18n (TDD)
- [x] **T3.1 (RED):** Escribir pruebas de renderizado y localización en `tests/Feature/Mail/WishlistMailsTest.php` para `WishlistPriceDropMail` y `WishlistLowStockMail` (asuntos, precios formateados con `CurrencyEnum::format()`, imágenes, CTAs y traducciones ES/EN). _(cubre R4)_
- [x] **T3.2 (GREEN):** Crear `lang/es/wishlist.php`, `lang/en/wishlist.php`, mailables `app/Mail/Wishlist/WishlistPriceDropMail.php` y `app/Mail/Wishlist/WishlistLowStockMail.php`, y vistas Blade Markdown en `resources/views/mail/wishlist/price-drop.blade.php` y `low-stock.blade.php`. _(cubre R4)_
- [x] **T3.3 (VERIFY):** Ejecutar tests de mails: `vendor/bin/sail artisan test --compact tests/Feature/Mail/WishlistMailsTest.php`.

---

### Fase 4: Action de Dominio `SendWishlistAlertsAction` (TDD)
- [x] **T4.1 (RED):** Crear suite exhaustiva `tests/Feature/Wishlist/SendWishlistAlertsActionTest.php` cubriendo:
  - Disparo de rebaja de precio (`price < price_when_added` u oferta activa). _(cubre R1)_
  - Disparo de stock crítico ($1 \le \text{stock} \le 3$). _(cubre R2)_
  - Registro de log en BD (`WishlistNotificationLog`). _(cubre R3)_
  - Cooldown de 7 días por tupla `(user, variant, type)`. _(cubre R6)_
  - Exclusión de variantes inactivas, despublicadas o en preventa (`is_preorder = true`). _(cubre R7)_
  - Exclusión de stock = 0 (agotado). _(cubre R2, R7)_
  - Exclusión de usuarios sin email verificado o eliminados. _(cubre R8)_
  - Límite de volumen de máximo 3 correos por usuario por corrida. _(cubre R9)_
- [x] **T4.2 (GREEN):** Crear DTO `app/DTOs/Wishlist/WishlistAlertResultDTO.php` e implementar `app/Actions/Wishlist/SendWishlistAlertsAction.php` y configuración en `config/ecommerce.php`. _(cubre R1, R2, R3, R6, R7, R8, R9)_
- [x] **T4.3 (VERIFY):** Ejecutar tests de la Action: `vendor/bin/sail artisan test --compact tests/Feature/Wishlist/SendWishlistAlertsActionTest.php`.

---

### Fase 5: Comando Artisan y Scheduler (TDD)
- [x] **T5.1 (RED):** Crear `tests/Feature/Console/SendWishlistAlertsCommandTest.php` verificando la ejecución del comando CLI `app:send-wishlist-alerts`, su salida por terminal y el registro en el scheduler. _(cubre R1, R2, R3)_
- [x] **T5.2 (GREEN):** Implementar `app/Console/Commands/SendWishlistAlertsCommand.php` como wrapper delgado que invoca `SendWishlistAlertsAction` y registrar la programación diaria en `routes/console.php`. _(cubre R1, R2, R3)_
- [x] **T5.3 (VERIFY):** Ejecutar tests del comando: `vendor/bin/sail artisan test --compact tests/Feature/Console/SendWishlistAlertsCommandTest.php`.

---

### Fase 6: Suite Completa, Pint y Definition of Done
- [x] **T6.1:** Ejecutar suite completa de Wishlist y Alertas: `vendor/bin/sail artisan test --compact --filter=Wishlist`.
- [x] **T6.2:** Ejecutar formateador de código Pint: `vendor/bin/sail bin pint --dirty --format agent`.
- [x] **T6.3:** Actualizar roadmap a `Completa` en `specs/_global/01-product-and-roadmap.md`.

---

## Mapa de Trazabilidad

| Criterio EARS | Tareas de Implementación | Suites de Prueba Asociadas |
|---------------|--------------------------|----------------------------|
| **R1** (Rebaja de precio) | T4.1, T4.2, T5.1, T5.2 | `SendWishlistAlertsActionTest`, `SendWishlistAlertsCommandTest` |
| **R2** (Stock crítico) | T4.1, T4.2, T5.1, T5.2 | `SendWishlistAlertsActionTest`, `SendWishlistAlertsCommandTest` |
| **R3** (Log en BD) | T1.1, T1.2, T4.1, T4.2, T5.1, T5.2 | `WishlistNotificationLogTest`, `SendWishlistAlertsActionTest` |
| **R4** (Plantillas Leen & i18n) | T3.1, T3.2 | `WishlistMailsTest` |
| **R5** (Snapshot precio/moneda) | T2.1, T2.2 | `ToggleWishlistActionTest` |
| **R6** (Anti-spam / Cooldown 7d) | T4.1, T4.2 | `SendWishlistAlertsActionTest` |
| **R7** (Exclusión preventa/inactivo) | T4.1, T4.2 | `SendWishlistAlertsActionTest` |
| **R8** (Exclusión no verificados) | T4.1, T4.2 | `SendWishlistAlertsActionTest` |
| **R9** (Límite volumen max 3) | T4.1, T4.2 | `SendWishlistAlertsActionTest` |

---

## Definition of Done (DoD)

- [x] Todos los criterios R1–R9 cubiertos por pruebas automatizadas en PHPUnit.
- [x] TDD estricto ejecutado: cada fase contó con pruebas en fallo (RED) antes de su implementación (GREEN).
- [x] Cero deuda técnica de multi-moneda: precios y monedas tipados estrictamente en `CurrencyEnum`.
- [x] Dominio aislado en `SendWishlistAlertsAction`; comando Artisan delgado.
- [x] Plantillas de correo responsive con branding oficial de Leen e i18n completa en `es` y `en`.
- [x] Pint ejecutado sin advertencias ni violaciones de estilo (`vendor/bin/sail bin pint --dirty --format agent`).
- [x] Suite de pruebas completa del módulo en verde.


