# F09 — Cuenta/perfil del comprador · Tasks

> **ID:** F09 · **Slug:** `09-account`
> **Requirements:** [`requirements.md`](requirements.md) · **Design:** [`design.md`](design.md)
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md) · **Calidad:** [`02-feature-quality.md`](../../_global/02-feature-quality.md)

**Alcance DoD:** perfil (datos+password+email re-verify), libreta de direcciones, mis pedidos (listado paid+ y detalle), mis reseñas (listado+editar+eliminar), ownership en las 4 áreas, i18n, tests.
**Fuera de DoD:** eliminación de cuenta, wishlist (F10), login social/2FA (ya excluidos en F08).

**Estado de implementación:** En progreso — Fase 1 (Domain) completa. Fases 2-6 pendientes.

Sin fase de esquema: no hay migraciones nuevas (fundación de dominio ya completa).

---

## 1. Domain: policy, Actions y scopes nuevos

- [x] 1.1 `app/Policies/AddressPolicy.php`: `view`/`update`/`delete` comparan `(int) $address->user_id === (int) $user->id`. _(cubre R16)_
- [x] 1.2 `app/DTOs/Account/UpdateProfileDTO.php`: `userId`, `name`, `email`, `?phone`, `readonly`. _(prerequisito de R1, R13)_
- [x] 1.3 `app/Actions/Account/UpdateProfileAction.php`: actualiza nombre/teléfono/email; si `email !== $user->email` → `markEmailAsUnverified()` + `sendEmailVerificationNotification()`; rechaza email duplicado de otra cuenta. _(cubre R1, R13)_
- [x] 1.4 `app/Actions/Account/UpdatePasswordAction.php`: valida `Hash::check` de la contraseña actual y política mínima de la nueva antes de `Hash::make`. _(cubre R2, R14)_
- [x] 1.5 `app/Enums/Orders/OrderStatusEnum.php`: método `accountHistoryStatuses(): array` → `[Paid, Processing, Shipped, Delivered]`. _(prerequisito de R7)_
- [x] 1.6 `app/Models/Order.php`: scope `scopeVisibleInAccountHistory(Builder $query, int $userId)` usando 1.5. _(cubre R7)_
- [x] 1.7 `app/Models/Review.php`: scope `scopeOwnedBy(Builder $query, int $userId)`. _(cubre R9)_

## 2. Storefront: componentes Livewire MFC + controller de solo lectura

- [ ] 2.1 `profile-page` (MFC, `layouts.storefront`): form de datos básicos → `UpdateProfileAction`; permite edición aunque el email esté sin verificar. _(cubre R1, R12, R13, R19)_
- [ ] 2.2 `profile-page`: sección de cambio de contraseña (contraseña actual + nueva + confirmación) → `UpdatePasswordAction`. _(cubre R2, R12, R14)_
- [ ] 2.3 `profile-addresses-page` (MFC): lista direcciones del usuario autenticado; alta/edición invocan `CreateAddressAction`/`UpdateAddressAction` (reusadas de F02, sin cambios); eliminar invoca `DeleteAddressAction` (reusada); autoriza cada operación con `AddressPolicy` (1.1). _(cubre R3, R4, R5, R6, R12, R15, R16, R18)_
- [ ] 2.4 `profile-orders-page` (MFC): listado paginado usando `scopeVisibleInAccountHistory` (1.6). _(cubre R7, R12)_
- [ ] 2.5 `app/Http/Controllers/Account/ProfileOrderDetailController.php` (invocable): autoriza con `OrderPolicy::view` (reusada de F04/F07, sin cambios), devuelve vista de solo lectura. _(cubre R8, R16)_
- [ ] 2.6 `resources/views/account/orders/show.blade.php`: ítems, montos, dirección de envío, estado — solo lectura, estilo `layouts.storefront`. _(cubre R8, R12)_
- [ ] 2.7 `profile-reviews-page` (MFC): listado usando `scopeOwnedBy` (1.7) con estado de moderación visible; editar invoca `UpdateReviewAction` (reusada de F07); eliminar invoca `DeleteReviewAction` (reusada de F07). _(cubre R9, R10, R11, R12, R16)_

## 3. Integración: rutas

- [ ] 3.1 `routes/web.php`: agregar `profile` (`/profile`), `profile.addresses` (`/profile/addresses`), `profile.orders` (`/profile/orders`), `profile.orders.show` (`/profile/orders/{order}`, `ProfileOrderDetailController`), `profile.reviews` (`/profile/reviews`) dentro del grupo `Route::middleware('auth')` ya existente en `routes/web.php`. _(cubre R1, R3, R7, R8, R9, R17)_

## 4. i18n

- [ ] 4.1 `lang/en/account.php` + `lang/es/account.php` (tuteo, sin voseo): labels y mensajes de perfil, contraseña, direcciones, pedidos y reseñas. _(cubre R12)_

## 5. Tests (PHPUnit)

- [ ] 5.1 `tests/Feature/Account/ProfilePageTest.php`: edición válida de nombre/teléfono; cambio de email marca no-verificado y reenvía; email duplicado rechazado; datos inválidos rechazados. _(cubre R1, R13)_
- [ ] 5.2 `tests/Feature/Account/PasswordUpdateTest.php`: cambio válido; contraseña actual incorrecta rechazada; nueva contraseña inválida o sin confirmar rechazada. _(cubre R2, R14)_
- [ ] 5.3 `tests/Feature/Account/AddressBookTest.php`: alta/edición válidas; marcar default reemplaza a la anterior; eliminar la única default no reasigna otra; datos inválidos rechazados; usuario no accede/edita/elimina dirección ajena. _(cubre R3, R4, R5, R6, R15, R16, R18)_
- [ ] 5.4 `tests/Feature/Account/MyOrdersTest.php`: listado incluye solo paid/processing/shipped/delivered; pendiente de pago excluido; usuario no ve pedidos ajenos en el listado. _(cubre R7, R16)_
- [ ] 5.5 `tests/Feature/Account/OrderDetailHttpTest.php`: detalle de pedido propio visible; 403 al pedir el detalle de un pedido ajeno. _(cubre R8, R16)_
- [ ] 5.6 `tests/Feature/Account/MyReviewsTest.php`: listado propio con estado de moderación; editar vuelve a pendiente; eliminar quita la reseña; usuario no edita/elimina reseña ajena. _(cubre R9, R10, R11, R16)_
- [ ] 5.7 `tests/Feature/Account/AccountAuthGateTest.php`: visitante sin sesión redirigido a login desde cada ruta `/profile/*`; comprador con email no verificado puede editar su perfil. _(cubre R17, R19)_
- [ ] 5.8 `tests/Unit/Models/AccountScopesTest.php`: `scopeVisibleInAccountHistory` y `scopeOwnedBy` con factories de estados mixtos (pending/paid/cancelled; otro usuario). _(cubre R7, R9 — prerequisito de 5.4, 5.6)_

## 6. Cierre de calidad

- [ ] 6.1 Tests del alcance F09 en verde vía Sail.
- [ ] 6.2 Pint en PHP tocado (`vendor/bin/sail bin pint --dirty --format agent`).
- [ ] 6.3 Estado F09 = **Completa** en `requirements.md` y roadmap al cerrar implementación.

---

## Mapa de trazabilidad

| Criterio | Tareas |
|----------|--------|
| R1 | 1.2, 1.3, 2.1, 3.1, 5.1 |
| R2 | 1.4, 2.2, 3.1, 5.2 |
| R3 | 2.3, 3.1, 5.3 |
| R4 | 2.3, 5.3 |
| R5 | 2.3, 5.3 |
| R6 | 2.3, 5.3 |
| R7 | 1.5, 1.6, 2.4, 3.1, 5.4, 5.8 |
| R8 | 2.5, 2.6, 3.1, 5.5 |
| R9 | 1.7, 2.7, 3.1, 5.6, 5.8 |
| R10 | 2.7, 5.6 |
| R11 | 2.7, 5.6 |
| R12 | 2.1, 2.2, 2.3, 2.4, 2.6, 2.7, 4.1 |
| R13 | 1.3, 2.1, 5.1 |
| R14 | 1.4, 2.2, 5.2 |
| R15 | 2.3, 5.3 |
| R16 | 1.1, 2.3, 2.5, 2.7, 3.1, 5.3, 5.4, 5.5, 5.6 |
| R17 | 3.1, 5.7 |
| R18 | 2.3, 5.3 |
| R19 | 2.1, 5.7 |

---

## Definition of Done (checklist tasks)

- [ ] Criterios **R1–R19** implementados y testeados.
- [ ] Ownership verificado en las 4 áreas (1.1, 2.3, 2.5, 2.7 + sus tests).
- [ ] Actions/policies reusadas de F02 y F07 sin modificaciones (`app/Actions/Addresses/*`, `app/Actions/Reviews/{Update,Delete}ReviewAction.php`, `OrderPolicy`, `ReviewPolicy`).
- [ ] `lang/{en,es}/account.php` completos, tono tuteo/neutro (sin voseo).
- [ ] PHPUnit del alcance en verde vía Sail; Pint OK.
- [ ] Specs + roadmap con estado **Completa** al cerrar implementación.
