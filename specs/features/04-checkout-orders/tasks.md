# F04 — Checkout y órdenes · Tasks

> **ID:** F04 · **Slug:** `04-checkout-orders`  
> **Requirements:** [`requirements.md`](requirements.md) · **Design:** [`design.md`](design.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md) · **Calidad:** [`02-feature-quality.md`](../../_global/02-feature-quality.md)

**Alcance DoD:** dominio Orders + checkout mínimo + thank-you + signed URL guest + Filament pedidos + tests.  
**Fuera de DoD:** pagos F05; stock decrement; cupones; perfil mis pedidos UI; UI de marca completa.

**Estado de implementación:** completa (dominio + checkout mínimo + Filament + tests verdes).

---

## 1. Fundación config, i18n y errores

- [x] 1.1 Extender `config/ecommerce.php` con costo de envío estándar por moneda (COP/EUR). _(cubre R1, R3, D12, D13)_
- [x] 1.2 `lang/en/orders.php` + `lang/es/orders.php` (+ navigation group orders). _(cubre R2, R10, R13–R18, R21, R22)_
- [x] 1.3 Excepciones de dominio en `app/Exceptions/Orders/`. _(cubre R2, R13–R18, R10)_

## 2. Dominio — validación y creación

- [x] 2.1 DTOs en `app/DTOs/Orders/`. _(cubre R3, R4, R6, R7, R8)_
- [x] 2.2 Generador `order_number` `ORD-YYYYMMDD-XXXX`. _(cubre R3, D16)_
- [x] 2.3 `ValidateCartForCheckoutAction` (revalidar todas las líneas + totales preview). _(cubre R1, R2, R20)_
- [x] 2.4 `CreateOrderFromCartAction` (lock, revalidate, snapshots, clear cart, no stock). _(cubre R3, R4, R6, R7, R8, R13–R16, R18, R20)_
- [x] 2.5 `CancelOrderAction` (`pending→cancelled` only). _(cubre R10, R19, R20)_
- [x] 2.6 Policy `OrderPolicy` (view own / admin; cancel admin). _(cubre R9, R21, R22)_

## 3. Entrada checkout y confirmación

- [x] 3.1 Rutas checkout + thank-you (signed). _(cubre R1, R5, R12, R20, R22)_
- [x] 3.2 Checkout mínimo (Livewire y/o controller) con validación de borde. _(cubre R1, R3, R4, R8, R12, R17)_
- [x] 3.3 Redirect gracias con signed URL (guest) / ownership (user). _(cubre R5, R9, R21, R22)_
- [x] 3.4 Lectura de orden (thank-you y/o API mínima). _(cubre R9)_

## 4. Filament admin

- [x] 4.1 `OrderResource` list + view (snapshots, totales, estado). _(cubre R11)_
- [x] 4.2 Acción cancelar pending → `CancelOrderAction`. _(cubre R10, R11)_

## 5. Tests (PHPUnit)

- [x] 5.1 Validate checkout OK + preview totales. _(cubre R1)_
- [x] 5.2 Validate/create fallan: vacío, stock, no elegible. _(cubre R2, R13, R14, R15)_
- [x] 5.3 Create order user: pending, snapshots, cart vacío, stock intacto. _(cubre R3, R7, R20)_
- [x] 5.4 Create order guest + signed thank-you access. _(cubre R4, R5, R22)_
- [x] 5.5 One-shot address no crea Address. _(cubre R6)_
- [x] 5.6 customer_notes; discount/tax 0; shipping config. _(cubre R8, D12)_
- [x] 5.7 Doble create / cart vacío tras éxito. _(cubre R16)_
- [x] 5.8 Cancel pending OK; no-pending falla. _(cubre R10, R19)_
- [x] 5.9 User no ve orden ajena. _(cubre R21)_
- [x] 5.10 Happy path entrypoint checkout HTTP/Livewire. _(cubre R12, R17)_
- [x] 5.11 Filament: admin can list/view/cancel (si el proyecto ya testea Filament). _(cubre R11)_

## 6. Cierre de calidad

- [x] 6.1 Tests del alcance F04 en verde vía Sail.
- [x] 6.2 Pint en PHP tocado (`vendor/bin/sail bin pint --dirty --format agent`).
- [x] 6.3 Estado F04 = **Completa** en requirements + roadmap al cerrar implementación.

---

## Mapa de trazabilidad (DoD F04)

| Criterio | Tareas | DoD F04 |
|----------|--------|---------|
| R1 | 1.1, 2.3, 3.1–3.2, 5.1 | Sí |
| R2 | 1.2–1.3, 2.3, 5.2 | Sí |
| R3 | 2.1–2.4, 5.3 | Sí |
| R4 | 2.4, 3.3, 5.4 | Sí |
| R5 | 3.1–3.3, 5.4 | Sí |
| R6 | 2.4, 5.5 | Sí |
| R7 | 2.4, 5.3 | Sí |
| R8 | 2.1, 2.4, 5.6 | Sí |
| R9 | 2.6, 3.3–3.4 | Sí |
| R10 | 2.5, 4.2, 5.8 | Sí |
| R11 | 4.1–4.2, 5.11 | Sí |
| R12 | 3.2, 5.10 | Sí |
| R13 | 2.4, 5.2 | Sí |
| R14 | 2.4, 5.2 | Sí |
| R15 | 2.4, 5.2 | Sí |
| R16 | 2.4, 5.7 | Sí |
| R17 | 3.2, 5.10 | Sí |
| R18 | 2.4 | Sí |
| R19 | 2.5, 5.8 | Sí |
| R20 | 2.3–2.5, 5.3 | Sí |
| R21 | 2.6, 5.9 | Sí |
| R22 | 3.3, 5.4 | Sí |

---

## Definition of Done (F04)

- [x] Criterios **R1–R22** implementados y testeados.
- [x] Actions/DTOs/Exceptions en área **Orders**.
- [x] Sin pagos; sin decremento de stock; sin cupones.
- [x] Guest + user; signed URL guest; Filament list/view/cancel pending.
- [x] Checkout mínimo + página de gracias.
- [x] PHPUnit del alcance en verde vía Sail; Pint OK.
- [x] Specs + roadmap con estado **Completa** (al cerrar).
