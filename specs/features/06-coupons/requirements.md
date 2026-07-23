# F06 — Cupones y redenciones

> **Estado:** Lista para implementar  
> **ID:** F06 · **Slug:** `06-coupons`  
> **Prerequisitos:** F03 (carrito), F04 (checkout y órdenes) — ver [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **Desbloquea:** descuento real en órdenes; handoff a F05 sin recálculo al pagar

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Producto y alcance F06 | [`specs/_global/01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md) |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Convenciones de código | [`AGENTS.md`](../../../AGENTS.md) / project-conventions |
| Esquema de dominio | `app/Models/Coupon.php`, `CouponRedemption.php`, `Order.php`, `Cart.php`; enums `CouponTypeEnum`, `CurrencyEnum`, `OrderStatusEnum` |
| Handoff checkout | [`specs/features/04-checkout-orders/requirements.md`](../04-checkout-orders/requirements.md) (`discount = 0` hasta F06; create order + cancel pending) |
| Handoff pagos | [`specs/features/05-payments/requirements.md`](../05-payments/requirements.md) (cobra `order.total` ya fijado; refund no reabre totales) |
| UI orientativa | [`specs/ui-briefs/04-carrito-de-compra.md`](../../ui-briefs/04-carrito-de-compra.md), [`05-checkout.md`](../../ui-briefs/05-checkout.md) (no son DoD de marca) |

## User stories

1. **Como** administrador, **quiero** crear y gestionar cupones (`percentage` / `fixed`) con límites y vigencia, **para** lanzar promociones sin tocar código.
2. **Como** comprador (guest o autenticado), **quiero** aplicar un código en el checkout y ver el descuento en el preview, **para** saber cuánto pagaré antes de confirmar.
3. **Como** comprador, **quiero** que al confirmar el pedido el descuento quede fijado en la orden, **para** que el cobro (F05) use el total ya descontado.
4. **Como** sistema, **quiero** consumir el cupón al crear la orden `pending` y liberarlo si se cancela en `pending`, **para** no quemar usos por errores de compra y sí impedir overuse concurrente.
5. **Como** administrador, **quiero** ver el código y el descuento en el pedido y las redenciones del cupón, **para** operar soporte y marketing.
6. **Como** front de tienda, **quiero** un contrato estable de preview/confirm con errores i18n, **para** enganchar la UI de marca sin reabrir dominio.

## Alcance de esta feature

**Incluye:**

- Admin Filament: CRUD de cupones (`percentage` | `fixed`), filtros útiles, ver redenciones; sin hard-delete operativo (desactivar).
- Validación y cálculo de descuento sobre **subtotal de líneas** (enteros; COP pesos / EUR céntimos).
- Preview de checkout con código opcional **sin** consumir uso.
- Aplicación al **confirmar orden** (`CreateOrderFromCart`): revalidar cupón; escribir `orders.coupon_id`, `orders.discount`, redención con **snapshot de código**, `used_count++` en la misma transacción.
- Un solo cupón por orden.
- Guest + user pueden usar cupón; límite per-user solo para autenticados; guest solo límite global.
- Cancel admin `pending→cancelled`: **liberar** redención y decrementar `used_count`.
- Refund F05 / orden `refunded`: **no** liberar cupón.
- Contrato mínimo (Action/HTTP) para preview + create con `couponCode`; keys i18n `coupons.*`.
- Mostrar cupón/código y descuento en ficha de pedido Filament.
- Feature tests PHPUnit del dominio, admin esencial y enganche checkout.

**No incluye (F06):**

- Persistencia de cupón en carrito (`carts.coupon_id`).
- UI de marca storefront (campo cupón “bonito” lo hace el front).
- Multi-cupón / stacking.
- Elegibilidad por producto/categoría/SKU (pivots).
- Free shipping como tipo o efecto de cupón.
- First-purchase-only formal; gift cards; store credit; BOGO / cupones sin código.
- Prorrateo de descuento a `order_items`.
- Partial refund con recálculo de cupón.
- Impuestos (`tax_amount` sigue 0).
- Flujo “orden gratis sin pasarela” cuando `total = 0` (cálculo puede dar 0; cobro/skip-pay out).
- Auto-release de pending abandonados (job).
- Emails “cupón aplicado”.
- Auth register/login storefront.

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | DoD superficie | Dominio + Filament cupones + enganche preview/create-order + tests + contrato estable para front. **Sin** UI de marca. |
| D2 | UI marca carrito/checkout | **Out** del DoD F06 (equipo front). |
| D3 | Schema base | Reutilizar `coupons`, `coupon_redemptions`, `orders.coupon_id`, `orders.discount`. |
| D4 | Migración extra | **Sí, mínima:** columna snapshot **`code`** en `coupon_redemptions` (D44). Sin cupón en `carts`. Sin pivots. |
| D5 | Punto de aplicación | Solo en **checkout/confirm orden**. No persistir cupón en carrito. |
| D6 | Preview | Código opcional en validate/preview; calcula `discount` **sin** escribir redención ni `used_count`. |
| D7 | Transporte del código | `couponCode` string normalizado en DTO de preview/create (no `coupon_id` desde el cliente). |
| D8 | Fallo cupón al confirm | **Bloquear** creación de orden (no crear sin descuento en silencio). |
| D9 | Fuente de verdad | `orders.discount` + `orders.coupon_id` + fila `coupon_redemptions` (con `code` snapshot). F05 **no** recalcula. |
| D10 | Tipos MVP | Solo `percentage` y `fixed` (`CouponTypeEnum`). |
| D11 | Base del descuento | Sobre **subtotal de líneas** (pre-shipping, pre-tax). |
| D12 | Free shipping | **Out.** Shipping siempre de F04 (`ShippingCostService` / config). |
| D13 | Percentage value | Entero **1–100** inclusive. |
| D14 | Fixed value | Entero en minor units de `coupon.currency`. |
| D15 | Cap extra | Sin `max_discount`; solo `discount = min(calculado, subtotal)`. |
| D16 | Shipping vs 100% off | Si descuento = subtotal, **shipping se sigue cobrando**; `total = shipping_cost` (si shipping > 0). |
| D17 | Redondeo % | **Floor** del porcentaje sobre subtotal. |
| D18 | Prorrateo líneas | **No** en F06. |
| D19 | Fixed + moneda | `currency` **required**; solo aplica si `order/cart.currency === coupon.currency`. |
| D20 | Percentage + moneda | `currency` **null**; aplica COP y EUR. |
| D21 | Mínimo de compra | Si hay `min_order_amount`, exige `min_order_currency` match con moneda del carrito/orden; mínimo sobre **subtotal** pre-discount. |
| D22 | Elegibilidad catálogo | **Todo el subtotal** del carrito; sin pivots producto/categoría. |
| D23 | Guest | **Puede** usar cupón; `redemption.user_id` null. |
| D24 | Allowlist users | **Out.** |
| D25 | First purchase only | **Out** (aprox. con `usage_limit_per_user=1` en users si se necesita). |
| D26 | Código | Unique global; comparación **case-insensitive**; normalizar `UPPER` + trim; alfanumérico + guiones; max razonable (~32). |
| D27 | Vigencia | `starts_at` null = ya válido; `expires_at` null = sin fin; timezone de app; bordes inclusivos (`now >= starts`, `now <= expires`). |
| D28 | `is_active` | Inactivo → no aplica aunque fechas OK. |
| D29 | Edición post-uso | Con redenciones: **no** cambiar type/value/currency; sí límites, fechas, active. |
| D30 | Borrado | **No hard-delete** operativo; desactivar. |
| D31 | Stacking | **Un solo cupón por orden** (`orders.coupon_id` singular). |
| D32 | `usage_limit` | `null` = ilimitado; si set, no más redenciones vivas que el límite. |
| D33 | `usage_limit_per_user` | Solo **autenticados** (count por `user_id`). Guest: solo límite global. |
| D34 | `used_count` | Cache; ++ en misma TX que redención; `lockForUpdate` en cupón al redimir. |
| D35 | Cuándo consume | Al **crear orden `pending`** con cupón válido (misma TX). |
| D36 | Cancel pending | **Liberar**: eliminar/void redención + `used_count--` (mínimo 0). |
| D37 | Pending abandonado | Sigue consumido hasta cancel admin; **sin** job auto-release en F06. |
| D38 | Refund full (F05) | **No** liberar cupón. |
| D39 | D25 pagos | Cupón sigue consumido (redención al create). |
| D40 | Total 0 | Dominio **permite** `total = 0` si subtotal descontado + shipping 0; flujo “skip pay / free order” **out** de F06. |
| D41 | Snapshot código | **Sí:** `coupon_redemptions.code` (string) al redimir = código normalizado usado. |
| D42 | Filament cupones | Resource CRUD list/create/edit; ver redenciones; **no** crear redención manual. |
| D43 | Filament orden | Mostrar código snapshot + `discount` (+ cupón si existe). |
| D44 | Contrato front | Preview/validate + create aceptan `couponCode`; errores vía keys i18n estables. |
| D45 | Mensajes storefront | Genéricos al comprador (“código no válido”); reason específico en log/admin. |
| D46 | Naming | Slug `06-coupons`, ID **F06**. Áreas: **Coupons** (+ toques **Orders** / checkout). |
| D47 | Idioma specs | Español. EARS según [`02-feature-quality.md`](../../_global/02-feature-quality.md). |
| D48 | Arquitectura | `CouponPricingService` (cálculo/validación) + integración en validate/create order; admin Upsert vía Actions si hay invariantes. Sin gateways. |
| D49 | Out of scope bloque | Multi-cupón, pivots catálogo, free shipping, BOGO, gift cards, first-purchase flag, partial refund, tax, auto-release pending, UI marca, emails, prorrateo líneas. |

---

## Criterios de aceptación (EARS)

### Happy path — en alcance F06

### R1 — Admin crea cupón percentage

DONDE un administrador con acceso al panel,  
CUANDO crea un cupón `percentage` con código único, `value` entre 1 y 100, sin moneda (o moneda nula), vigencia opcional y activo,  
EL SISTEMA DEBE persistirlo y mostrarlo en el listado de cupones.

### R2 — Admin crea cupón fixed

DONDE un administrador con acceso al panel,  
CUANDO crea un cupón `fixed` con código único, `value` entero, `currency` obligatoria (COP o EUR) y activo,  
EL SISTEMA DEBE persistirlo  
Y DEBE rechazar fixed sin moneda.

### R3 — Preview con cupón válido (sin consumir)

CUANDO un comprador (guest o user) con carrito válido solicita el preview/validate de checkout enviando un `couponCode` válido y aplicable,  
EL SISTEMA DEBE devolver el resumen con `discount` calculado y `total` coherente (`subtotal + shipping - discount`, tax 0)  
SIN crear orden  
SIN crear `coupon_redemptions`  
SIN incrementar `used_count`.

### R4 — Crear orden con cupón (user)

CUANDO un usuario autenticado confirma el pedido con carrito revalidado OK y `couponCode` válido y aplicable,  
EL SISTEMA DEBE crear la orden `pending` con `coupon_id` del cupón,  
DEBE fijar `discount` al monto calculado (enteros, techo subtotal),  
DEBE fijar `total = subtotal + shipping_cost - discount + tax_amount` con `tax_amount = 0`,  
DEBE crear una redención con `discount_amount`, `currency` de la orden, `user_id` del usuario y **`code` snapshot** del código normalizado,  
DEBE incrementar `used_count` del cupón en la misma transacción  
Y DEBE vaciar el carrito como en F04  
SIN decrementar stock.

### R5 — Crear orden con cupón (guest)

CUANDO un guest confirma con `couponCode` válido y aplicable,  
EL SISTEMA DEBE aplicar las mismas reglas de totales y redención que R4  
CON `user_id` nulo en la redención  
Y DEBE exponer thank-you vía signed URL como F04.

### R6 — Percentage multi-moneda

CUANDO el carrito está en COP o en EUR y el cupón es `percentage` activo aplicable,  
EL SISTEMA DEBE calcular el descuento como floor(subtotal × value / 100) en la moneda del carrito  
SIN exigir `coupon.currency`.

### R7 — Fixed solo en su moneda

CUANDO el carrito/orden está en la misma moneda que un cupón `fixed` aplicable,  
EL SISTEMA DEBE descontar `min(coupon.value, subtotal)`  
Y CUANDO la moneda no coincide, DEBE rechazar el cupón (preview y confirm).

### R8 — Mínimo de compra

CUANDO el cupón define `min_order_amount` y el subtotal del carrito en la moneda del carrito es **menor** que ese mínimo (con `min_order_currency` coincidente),  
EL SISTEMA DEBE rechazar el cupón en preview y en confirm  
SIN crear orden si el fallo es en confirm.

### R9 — Cancel pending libera cupón

DONDE un administrador cancela una orden `pending` que tiene redención de cupón,  
CUANDO la cancelación tiene éxito (`pending→cancelled`),  
EL SISTEMA DEBE eliminar (o anular de forma equivalente) la redención asociada  
Y DEBE decrementar `used_count` del cupón (sin bajar de 0)  
Y DEBE dejar de contar ese uso para `usage_limit` / `usage_limit_per_user`.

### R10 — Refund no libera cupón

CUANDO una orden que usó cupón pasa a `refunded` por el flujo de F05 (webhook),  
EL SISTEMA DEBE **no** eliminar la redención ni decrementar `used_count` por ese refund  
(comportamiento F06: beneficio consumido).

### R11 — Un cupón por orden

CUANDO se crea una orden con cupón,  
EL SISTEMA DEBE asociar **como máximo un** `coupon_id` y **una** redención para ese par cupón-orden  
SIN soportar stacking de varios códigos en la misma orden.

### R12 — Filament: redenciones y pedido

DONDE un administrador,  
CUANDO abre un cupón, DEBE poder ver sus redenciones (orden, usuario si hay, monto, código snapshot, fecha)  
Y CUANDO abre un pedido con descuento, DEBE ver el monto `discount` y el código de cupón usado (snapshot y/o relación).

### R13 — Admin desactiva cupón

CUANDO un administrador marca un cupón `is_active = false`,  
EL SISTEMA DEBE impedir nuevas aplicaciones (preview y confirm)  
SIN borrar redenciones históricas.

### R14 — Contrato de errores i18n

CUANDO el código no es aplicable (inválido, inactivo, expirado, moneda, mínimo, límites, etc.),  
EL SISTEMA DEBE fallar con error de dominio mapeable a keys de `lang/{en,es}/coupons.php`  
Y DEBE exponer al comprador un mensaje genérico seguro en el entrypoint de tienda  
SIN revelar obligatoriedad de reasons detallados al storefront.

---

### Validación y error

### R15 — Código inexistente o no aplicable (genérico)

CUANDO el comprador envía un código que no existe, está inactivo, fuera de vigencia o no cumple reglas,  
EL SISTEMA DEBE rechazar la aplicación  
SIN crear redención  
Y en confirm SIN crear orden.

### R16 — Límite global agotado

CUANDO `usage_limit` no es nulo y el número de redenciones vivas del cupón ya alcanzó el límite,  
EL SISTEMA DEBE rechazar nuevas aplicaciones  
SIN incrementar `used_count` por encima del límite en condiciones de carrera controladas (lock).

### R17 — Límite per-user (autenticado)

CUANDO un usuario autenticado ya tiene redenciones vivas del cupón iguales a `usage_limit_per_user`,  
EL SISTEMA DEBE rechazar una nueva aplicación de ese cupón para ese usuario  
SIN aplicar el mismo conteo per-user a guests (guest solo límite global).

### R18 — Fixed sin moneda o percentage mal configurado (admin)

CUANDO un admin intenta guardar un cupón `fixed` sin `currency`, o `percentage` con `value` fuera de 1–100,  
EL SISTEMA DEBE rechazar la validación  
SIN persistir el registro inválido.

### R19 — Edición de type/value/currency con redenciones

CUANDO un cupón ya tiene al menos una redención,  
EL SISTEMA DEBE impedir cambiar `type`, `value` o `currency`  
Y DEBE permitir cambiar límites, fechas e `is_active` si el resto de validaciones OK.

### R20 — Fallo de cupón no vacía carrito

CUANDO el confirm falla solo por cupón inválido,  
EL SISTEMA DEBE no crear orden  
Y DEBE no vaciar el carrito  
Y DEBE no consumir el cupón.

### R21 — Pago F05 usa total con descuento

CUANDO existe una orden `pending` creada con cupón y `discount > 0`,  
EL SISTEMA (F05) DEBE iniciar cobro por `order.total` ya descontado  
SIN revalidar ni reaplicar el cupón al pagar.

### R22 — Shipping no se anula por cupón

CUANDO un cupón percentage o fixed reduce el subtotal (incluso a 0),  
EL SISTEMA DEBE seguir aplicando el `shipping_cost` estándar de F04 al total  
SIN poner el envío en 0 por efecto del cupón.

### R23 — Sin cupón comportamiento F04

CUANDO el comprador no envía código (o envía vacío),  
EL SISTEMA DEBE crear la orden con `coupon_id` nulo, `discount = 0` y sin redención  
como en F04.

---

## Definition of Done (F06)

- [ ] Criterios **R1–R23** implementados y testeados (o justificados N/A con traza).
- [ ] Filament `CouponResource` (+ redenciones visibles); Order muestra código/descuento.
- [ ] Preview no consume; create pending consume; cancel pending libera; refund no libera.
- [ ] Snapshot `coupon_redemptions.code`; tipos percentage/fixed; un cupón por orden.
- [ ] i18n `lang/{en,es}/coupons.php` (+ navigation/enums si aplica).
- [ ] PHPUnit del alcance en verde vía Sail; Pint OK.
- [ ] Specs + roadmap con estado **Completa** al cerrar implementación.
- [ ] Sin UI de marca storefront en el alcance entregado; contrato listo para el front.

---

## Notas de handoff

| Hacia | Qué asume |
|-------|-----------|
| **Front** | `couponCode` opcional en preview/confirm; keys de error estables; sin depender de Filament. |
| **F05** | Sin cambios de contrato: cobra `order.total`. Refund no toca cupones (R10). |
| **F04** | `CancelOrderAction` debe extenderse para liberar redención (R9). Validate/create aceptan código. |
| **Futuro** | Free shipping, pivots catálogo, cupón en carrito, auto-release pending, skip-pay total 0 = features aparte. |
