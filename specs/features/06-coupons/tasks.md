# F06 — Cupones · Tasks

> **ID:** F06 · **Slug:** `06-coupons`  
> **Requirements:** [`requirements.md`](requirements.md) · **Design:** [`design.md`](design.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md) · **Calidad:** [`02-feature-quality.md`](../../_global/02-feature-quality.md)

**Alcance DoD:** dominio Coupons + pricing + Filament cupones + enganche preview/create/cancel order + snapshot code + tests.  
**Fuera de DoD:** UI de marca; cupón en carrito; multi-cupón; free shipping; pivots catálogo; skip-pay total 0; emails.

**Estado de implementación:** Completa — secciones 1–6 (dominio, Filament, tests, cierre de calidad).

---

## 1. Fundación schema, i18n y errores

- [x] 1.1 Migración: `coupon_redemptions.code` (string, longitud razonable). _(cubre D4, D41, R4, R5)_
- [x] 1.2 Actualizar model `CouponRedemption` fillable/casts + factory con `code`. _(cubre D41)_
- [x] 1.3 `lang/en/coupons.php` + `lang/es/coupons.php` (fields, errors, labels). _(cubre R14, D45)_
- [x] 1.4 Completar labels enum cupón en `lang/*/enums.php` / navigation si faltan. _(cubre R1, R12)_
- [x] 1.5 Excepciones de dominio en `app/Exceptions/Coupons/` (reason o tipadas). _(cubre R14–R20)_

## 2. Pricing y admin domain

- [x] 2.1 DTOs `app/DTOs/Coupons/` (`UpsertCouponDTO`, quote DTO si aplica). _(cubre D48, R1, R2)_
- [x] 2.2 `CouponPricingService`: resolve code, validar reglas, calcular discount (floor %, min subtotal, monedas, min order, limits lectura). _(cubre R3, R6–R8, R11, R15–R17, R22, D10–D21)_
- [x] 2.3 `CreateCouponAction` / `UpdateCouponAction` (normalizar code; fixed⇒currency; percentage 1–100; inmutables type/value/currency si hay redenciones). _(cubre R1, R2, R13, R18, R19, D26–D30)_

## 3. Enganche checkout / órdenes

- [x] 3.1 Extender DTOs Orders: `?couponCode` en validate/create. _(cubre D7, R3, R4, R23)_
- [x] 3.2 `ValidateCartForCheckoutAction` (o equivalente preview): código opcional → `discount` en preview **sin** writes. _(cubre R3, D6)_
- [x] 3.3 `CreateOrderFromCartAction`: lock cupón; quote; set `coupon_id`/`discount`/`total`; crear redención con `code` + `used_count++` en TX; sin cupón → discount 0. _(cubre R4, R5, R6, R7, R11, R21, R23, D35)_
- [x] 3.4 `CancelOrderAction`: liberar redención + decrementar `used_count` al `pending→cancelled`. _(cubre R9, D36)_
- [x] 3.5 Entrypoint checkout (HTTP/Livewire): aceptar `coupon_code` / `couponCode` en preview y confirm; mapear errores storefront genéricos. _(cubre R3, R14, R15, D44, D45)_

## 4. Filament

- [x] 4.1 `CouponResource` form/table/filters create/edit; sin hard-delete operativo. _(cubre R1, R2, R13, R18, R19, D42)_
- [x] 4.2 Redenciones visibles (RelationManager o infolist) en cupón. _(cubre R12, D42)_
- [x] 4.3 Order view/infolist: `discount` + código snapshot (redemption). _(cubre R12, D43)_

## 5. Tests (PHPUnit)

- [x] 5.1 Pricing: percentage COP/EUR floor; fixed match; fixed currency mismatch. _(cubre R6, R7)_
- [x] 5.2 Pricing: min order; inactive; expired/not started; cap a subtotal; shipping no se anula. _(cubre R8, R15, R22, D15, D16)_
- [x] 5.3 Preview con código válido: discount > 0, sin redemption ni used_count++. _(cubre R3)_
- [x] 5.4 Create order user + guest con cupón: order fields + redemption code + used_count. _(cubre R4, R5)_
- [x] 5.5 Create sin código: discount 0, sin redemption. _(cubre R23)_
- [x] 5.6 Usage limit global agotado + race básica con lock. _(cubre R16, D34)_
- [x] 5.7 Usage limit per-user (user blocked; guest no usa per-user). _(cubre R17, D33)_
- [x] 5.8 Confirm con cupón inválido: no order, no clear cart, no consume. _(cubre R15, R20)_
- [x] 5.9 Cancel pending libera redención y used_count. _(cubre R9)_
- [x] 5.10 Refund path (si aplicable con test F05 existente o unitario de policy F06): redención permanece. _(cubre R10, D38)_
- [x] 5.11 Admin: create percentage/fixed; reject fixed sin currency; immutable fields con redenciones. _(cubre R1, R2, R18, R19)_
- [x] 5.12 Filament list/create cupón esencial (convención del repo). _(cubre R1, R12)_
- [x] 5.13 Happy path entrypoint checkout con couponCode (HTTP o Livewire). _(cubre R3, R4, D44)_

## 6. Cierre de calidad

- [x] 6.1 Tests del alcance F06 en verde vía Sail.
- [x] 6.2 Pint en PHP tocado (`vendor/bin/sail bin pint --dirty --format agent`).
- [x] 6.3 Estado F06 = **Completa** en requirements + roadmap al cerrar implementación.

---

## Mapa de trazabilidad (DoD F06)

| Criterio | Tareas | DoD F06 |
|----------|--------|---------|
| R1 | 2.3, 4.1, 5.11, 5.12 | Sí |
| R2 | 2.3, 4.1, 5.11 | Sí |
| R3 | 2.2, 3.2, 3.5, 5.3, 5.13 | Sí |
| R4 | 1.1–1.2, 3.3, 5.4 | Sí |
| R5 | 3.3, 5.4 | Sí |
| R6 | 2.2, 5.1 | Sí |
| R7 | 2.2, 5.1 | Sí |
| R8 | 2.2, 5.2 | Sí |
| R9 | 3.4, 5.9 | Sí |
| R10 | 5.10 | Sí |
| R11 | 2.2, 3.3 | Sí |
| R12 | 4.2, 4.3, 5.12 | Sí |
| R13 | 2.3, 4.1 | Sí |
| R14 | 1.3, 1.5, 3.5 | Sí |
| R15 | 2.2, 5.2, 5.8 | Sí |
| R16 | 2.2, 3.3, 5.6 | Sí |
| R17 | 2.2, 5.7 | Sí |
| R18 | 2.3, 5.11 | Sí |
| R19 | 2.3, 5.11 | Sí |
| R20 | 3.3, 5.8 | Sí |
| R21 | 3.3 (total); coherencia F05 existente | Sí |
| R22 | 2.2, 5.2 | Sí |
| R23 | 3.3, 5.5 | Sí |

---

## Definition of Done (checklist tasks)

- [x] Criterios **R1–R23** implementados y testeados.
- [x] Migración `coupon_redemptions.code` + models/factories.
- [x] `CouponPricingService` + Actions admin Coupons.
- [x] Validate/create/cancel Orders enganchados.
- [x] Filament CouponResource + order discount/code.
- [x] i18n coupons EN/ES.
- [x] PHPUnit del alcance en verde vía Sail; Pint OK.
- [x] Specs + roadmap con estado **Completa** (al cerrar).
