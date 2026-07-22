# F03 — Carrito · Tasks

> **ID:** F03 · **Slug:** `03-cart`  
> **Requirements:** [`requirements.md`](requirements.md) · **Design:** [`design.md`](design.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md) · **Calidad:** [`02-feature-quality.md`](../../_global/02-feature-quality.md)

**Alcance DoD:** dominio Cart (Actions/DTOs/excepciones/pricing) + entrypoint HTTP/Livewire mínimo + tests.  
**Fuera de DoD:** Filament de carritos; storefront de marca; checkout; cupones; reserva de stock.

**Estado de implementación:** no iniciado (specs listas; código pendiente de autorización explícita).

---

## 1. Fundación de mensajes y errores

- [ ] 1.1 `lang/en/cart.php` + `lang/es/cart.php` con keys de errores/labels de carrito. _(cubre R11, R12, R13, R14, R15)_
- [ ] 1.2 Excepciones de dominio en `app/Exceptions/Cart/` (elegibilidad, stock, qty, moneda, acceso). _(cubre R11–R16)_

## 2. Dominio — resolución y mutaciones

- [ ] 2.1 DTOs en `app/DTOs/Cart/` (`ResolveCart`, `AddCartItem`, `UpdateCartItemQuantity`, `ChangeCartCurrency` según design). _(cubre R1–R5, R9)_
- [ ] 2.2 `GetOrCreateCartAction` (guest por session_id, user por user_id, default COP). _(cubre R1, R2)_
- [ ] 2.3 `AddCartItemAction` (elegibilidad, stock, max 99, upsert sumando). _(cubre R3, R11, R12, R13, R15, R17)_
- [ ] 2.4 `UpdateCartItemQuantityAction` (1..N update; 0 remove). _(cubre R4, R5, R12, R13, R15)_
- [ ] 2.5 `RemoveCartItemAction` + `ClearCartAction` (conservar cabecera cart). _(cubre R6)_
- [ ] 2.6 Ownership / acceso en mutaciones (session o user dueño). _(cubre R16)_

## 3. Dominio — precio, moneda, merge

- [ ] 3.1 `CartPricingService` o view Action: unit/line/total enteros en moneda del cart. _(cubre R8, D13)_
- [ ] 3.2 `ChangeCartCurrencyAction` (todas las líneas con precio o vacío; si no → block). _(cubre R9, R14)_
- [ ] 3.3 `MergeGuestCartIntoUserCartAction` (suma qty, caps, limpia guest). _(cubre R7)_
- [ ] 3.4 Hook de merge en login o request autenticada (mínimo testeable). _(cubre R7)_

## 4. Entrada mínima

- [ ] 4.1 Elegir Livewire **o** controller+routes (design §5); cablear resolve + mutaciones + lectura. _(cubre R10)_
- [ ] 4.2 Validación de borde (qty, currency, variant id) antes del Action. _(cubre R10, R15)_

## 5. Tests (PHPUnit)

- [ ] 5.1 Guest get-or-create reutiliza session. _(cubre R1)_
- [ ] 5.2 User get-or-create un cart activo. _(cubre R2)_
- [ ] 5.3 Add nueva línea + add misma variante suma. _(cubre R3)_
- [ ] 5.4 Update qty; qty 0 elimina. _(cubre R4, R5)_
- [ ] 5.5 Remove línea; clear vacía ítems. _(cubre R6)_
- [ ] 5.6 Merge guest→user suma y guest deja de ser canónico. _(cubre R7)_
- [ ] 5.7 Pricing totales enteros en COP y EUR. _(cubre R8)_
- [ ] 5.8 Cambio moneda OK; bloqueado si falta precio. _(cubre R9, R14)_
- [ ] 5.9 Variante no elegible rechazada. _(cubre R11)_
- [ ] 5.10 Stock insuficiente y qty > 99 rechazados. _(cubre R12, R13)_
- [ ] 5.11 Qty negativa rechazada; stock no decrementa al add. _(cubre R15, R17)_
- [ ] 5.12 Mutación de carrito ajeno denegada. _(cubre R16)_
- [ ] 5.13 Happy path entrypoint mínimo (HTTP o Livewire). _(cubre R10)_

## 6. Cierre de calidad

- [ ] 6.1 Tests del alcance F03 en verde vía Sail.
- [ ] 6.2 Pint en PHP tocado (`vendor/bin/sail bin pint --dirty --format agent`).
- [ ] 6.3 Estado F03 = **Completa** en requirements + roadmap (solo al cerrar implementación).

---

## Mapa de trazabilidad (DoD F03)

| Criterio | Tareas | DoD F03 |
|----------|--------|---------|
| R1 | 2.2, 5.1 | Sí |
| R2 | 2.2, 5.2 | Sí |
| R3 | 2.1, 2.3, 5.3 | Sí |
| R4 | 2.4, 5.4 | Sí |
| R5 | 2.4, 5.4 | Sí |
| R6 | 2.5, 5.5 | Sí |
| R7 | 3.3, 3.4, 5.6 | Sí |
| R8 | 3.1, 5.7 | Sí |
| R9 | 3.2, 5.8 | Sí |
| R10 | 4.1, 4.2, 5.13 | Sí |
| R11 | 1.x, 2.3, 5.9 | Sí |
| R12 | 2.3, 2.4, 5.10 | Sí |
| R13 | 2.3, 2.4, 5.10 | Sí |
| R14 | 3.2, 5.8 | Sí |
| R15 | 2.3, 2.4, 5.11 | Sí |
| R16 | 2.6, 5.12 | Sí |
| R17 | 2.3, 5.11 | Sí |

---

## Definition of Done (F03)

- [ ] Criterios **R1–R17** implementados y testeados.
- [ ] Actions/DTOs (y Service de pricing si aplica) en área **Cart**.
- [ ] Sin Filament de carritos; sin UI de marca de catálogo como DoD.
- [ ] Sin reserva de stock; sin snapshot de precio en `cart_items`.
- [ ] Guest + user + merge sumando; techo `min(stock, 99)`; qty 0 = remove.
- [ ] Cambio de moneda con bloqueo si falta precio.
- [ ] Entrypoint mínimo cableado.
- [ ] PHPUnit del alcance en verde vía Sail; Pint OK.
- [ ] Specs + roadmap con estado **Completa** (al cerrar).
