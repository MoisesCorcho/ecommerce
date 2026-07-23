# F06 — Cupones · Diseño técnico

> **ID:** F06 · **Slug:** `06-coupons`  
> **Requirements:** [`requirements.md`](requirements.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md)  
> **Producto:** [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **Dominio:** `Coupon`, `CouponRedemption`, `Order`, `Cart`; enums `CouponTypeEnum`, `CurrencyEnum`, `OrderStatusEnum`  
> **Layout código:** tipo primero, área **Coupons** (`app/Actions/Coupons`, `app/Services/Coupons`, `app/DTOs/Coupons`, `app/Exceptions/Coupons`); enganche en área **Orders** (validate/create/cancel)  
> **Stack:** Laravel 13, entrypoints HTTP/Livewire checkout existentes, Filament v5, PHPUnit, Sail  
> **Fuera de alcance F06:** UI de marca storefront, cupón en carrito, multi-cupón, free shipping, pivots catálogo, skip-pay total 0, emails

Este documento describe el **CÓMO**. El **QUÉ** está en `requirements.md`.

---

## 1. Alcance técnico

| Incluye (F06) | Excluye |
|---------------|---------|
| Filament `CouponResource` + redenciones | Brand UI apply-coupon |
| `CouponPricingService` + validación de reglas | Pivots product/category |
| Preview checkout con `couponCode` | Persistencia en `carts` |
| Create order escribe discount + redemption + `used_count` | Stacking multi-código |
| Cancel pending libera redención | Liberar en refund F05 |
| Snapshot `code` en redención (migración) | Prorrateo a order_items |
| i18n + exceptions Coupons | Gateways externos |
| Feature tests | Job auto-expire pending |

---

## 2. Modelo de datos

### `coupons` (existente)

| Campo | Uso F06 |
|-------|---------|
| `code` | Unique; almacenar **normalizado** (UPPER + trim) |
| `type` | `percentage` \| `fixed` |
| `value` | % 1–100 o monto fixed en minor units |
| `currency` | Required si fixed; null si percentage |
| `min_order_amount` / `min_order_currency` | Opcional; si amount set, currency debe match carrito |
| `usage_limit` / `usage_limit_per_user` | Null = ilimitado |
| `used_count` | Cache de redenciones vivas |
| `starts_at` / `expires_at` | Null = abierto en ese extremo |
| `is_active` | Kill switch |

### `coupon_redemptions` (existente + migración)

| Campo | Uso F06 |
|-------|---------|
| `coupon_id`, `order_id` | Unique pair |
| `user_id` | Null guest; set si orden con user |
| `discount_amount` | Entero = `orders.discount` al crear |
| `currency` | = `orders.currency` |
| **`code`** (**nuevo**) | Snapshot string del código normalizado (D41) |

Migración única sugerida:

```php
$table->string('code', 32)->after('coupon_id'); // o al final; NOT NULL en filas nuevas
```

Backfill no requerido si no hay redenciones en prod; en tests factories actualizan.

### `orders` (writes F06)

| Campo | Uso |
|-------|-----|
| `coupon_id` | FK al cupón; null si sin código |
| `discount` | Entero ≥ 0; techo subtotal |
| `total` | `subtotal + shipping_cost - discount + tax_amount` (tax 0) |

No añadir `orders.coupon_code` si el snapshot vive en redención (una fuente para el código histórico + FK).

### `carts`

Sin cambios. Código solo en request de preview/confirm.

### Factories

- Extender `CouponFactory` (states percentage/fixed, inactive, expired, limited).
- Extender `CouponRedemptionFactory` con `code`.
- Reutilizar Order/Cart factories en tests de integración.

---

## 3. Flujo de extremo a extremo

```text
[Admin] CouponResource → UpsertCouponAction (invariantes type/currency/value)
                              ↓
[Checkout preview] ValidateCartForCheckout(+ couponCode?)
                              ↓ CouponPricingService::quote (no write)
                              ↓ CheckoutPreviewDTO.discount / total
[Checkout confirm] CreateOrderFromCart(+ couponCode?)
                              ↓ lock cart + (si cupón) lock coupon
                              ↓ revalidate lines + quote cupón
                              ↓ TX: order + items + redemption + used_count++ + clear cart
[F05 pay] StartOrderPayment → order.total (ya con discount)
[Admin cancel pending] CancelOrderAction
                              ↓ status cancelled + release redemption + used_count--
[F05 refund] ProcessPaymentWebhook → order refunded; cupón intacto
```

### Orden de validación al confirm (D / R)

1. Ownership carrito  
2. Líneas: elegibilidad + stock + precios live  
3. Subtotal + shipping  
4. Cupón (si código presente): reglas + quote  
5. Totales finales  
6. Write: order, items, redemption, used_count, clear cart  

Fallo en 4 → no write order/cart/cupón (R20).

### Concurrencia de límites

Dentro de la TX de create:

1. `Coupon::query()->whereKey($id)->lockForUpdate()->first()`  
2. Releer conteos / `used_count` vs `usage_limit`  
3. Insert redemption + `used_count++`  

Unique `(coupon_id, order_id)` evita doble redención misma orden.

---

## 4. Dominio: Services, Actions, DTOs, excepciones

### Service (`app/Services/Coupons/`)

| Clase | Responsabilidad |
|-------|-----------------|
| `CouponPricingService` | Resolver código → cupón; validar reglas de aplicabilidad; calcular `discount_amount` (floor %; min con subtotal). **Sin** writes. |

Entrada conceptual: código normalizado, subtotal, moneda, `?userId`, “now”.  
Salida: cupón + discount amount (o throw).

Reglas de validación (en un solo lugar):

- exists + `is_active`  
- ventana `starts_at` / `expires_at`  
- type/currency match  
- min order  
- `usage_limit` (redenciones vivas / used_count coherente)  
- `usage_limit_per_user` solo si `userId` no null  

Mensajes: excepciones de dominio con reason; entrypoint storefront mapea a genérico (D45).

### Actions Coupons (`app/Actions/Coupons/`)

| Action | Responsabilidad |
|--------|-----------------|
| `CreateCouponAction` / `UpdateCouponAction` (o `Upsert*`) | Invariantes admin: fixed⇒currency; percentage⇒currency null + value 1–100; normalizar code; bloquear type/value/currency si hay redenciones |
| (opcional) `DeactivateCouponAction` | Solo `is_active=false` si se prefiere separado |

No Action “ApplyCoupon” suelta si la orquestación vive en Orders; el Service es el núcleo compartido.

### Integración Orders (modificar F04)

| Pieza | Cambio |
|-------|--------|
| `CreateOrderFromCartDTO` | `?string $couponCode` |
| Preview DTO / validate | `?string $couponCode`; salida `discount` real |
| `CreateOrderFromCartAction` | Tras subtotal: quote cupón; set coupon_id/discount; crear `CouponRedemption` con `code`; `used_count++` |
| `CancelOrderAction` | Si hay redención: delete + `used_count = max(0, used_count-1)` en TX con cambio de status |
| `ValidateCartForCheckoutAction` | Aceptar código opcional; devolver discount en preview |

### DTOs (`app/DTOs/Coupons/`)

| Clase | Uso |
|-------|-----|
| `UpsertCouponDTO` | Admin create/update |
| `CouponQuoteDTO` (opcional) | Resultado de pricing: couponId, code, discountAmount, currency |

### Excepciones (`app/Exceptions/Coupons/`)

Preferir:

- `InvalidCouponException` con enum/reason interno (`not_found`, `inactive`, `not_started`, `expired`, `currency_mismatch`, `min_not_met`, `usage_exhausted`, `per_user_exhausted`, `immutable_fields`, …)  
- O excepciones tipadas por familia si el repo prefiere ese estilo (ver `Exceptions/Cart`, `Exceptions/Orders`).

i18n: `lang/{en,es}/coupons.php` → `errors.*`, `fields.*`, `actions.*`, Filament labels.

### Normalización de código

```text
trim → uppercase (mb) → validar charset/length
```

Persistir normalizado en `coupons.code` y en `coupon_redemptions.code`.

### Fórmula

```text
subtotal     = sum(line unit_price * qty)   // live F04
shipping     = ShippingCostService(currency)
raw_discount =
  percentage → floor(subtotal * value / 100)
  fixed      → value  // same currency only
discount     = min(raw_discount, subtotal)
total        = subtotal + shipping - discount + 0
```

---

## 5. Entrypoints

### Checkout (existente, extender)

- Preview/validate: body/query opcional `coupon_code` / `couponCode` (elegir un nombre y documentarlo; preferir snake en HTTP si el checkout actual usa arrays form).  
- Confirm: mismo campo en el payload de create.

Sin nueva ruta obligatoria “apply coupon” si preview + confirm bastan para el front.  
Si el front pide endpoint dedicado `POST /checkout/coupon/preview`, puede ser thin wrapper sobre `ValidateCartForCheckout` + código — opcional, no bloquea DoD si preview ya lo cubre.

### AuthZ

- Aplicar cupón en checkout: mismo acceso que checkout F04 (guest session / user cart).  
- Admin cupones: gate panel `admin_emails` como resto Filament.  
- No crear redenciones manuales desde UI.

---

## 6. Filament

### `CouponResource`

- Form: code, type (live), value, currency (visible/required si fixed), min amount/currency, usage limits, starts/expires, is_active.  
- Table: code, type, value, currency, used_count/limit, active, dates.  
- Filters: type, active, currency.  
- Pages: List/Create/Edit; **no** Delete hard (ocultar o deshabilitar si hay redenciones; preferir toggle active).  
- RelationManager o Infolist: **Redemptions** (order_number link, user email, code snapshot, discount_amount, currency, created_at).

### `OrderResource` (existente)

- Infolist/sección: `discount`, código desde `couponRedemption.code` o `coupon.code` fallback.  
- No editar cupón desde la orden en F06.

Navigation: grupo Commerce / Marketing / Cupones (seguir convención de grupos existentes del panel).

---

## 7. i18n

| Archivo | Contenido |
|---------|-----------|
| `lang/{en,es}/coupons.php` | fields, validation, errors, filament labels |
| `lang/{en,es}/enums.php` | ya tiene `coupon_type.*` — completar si falta |
| `lang/{en,es}/orders.php` | keys de preview “discount” si faltan |
| `lang/{en,es}/navigation.php` | grupo/item Cupones |

Storefront: mapear cualquier reason → `__('coupons.errors.invalid')` (genérico).  
Admin: reasons específicos OK.

---

## 8. Tests (mapa mínimo)

| Área | Escenarios |
|------|------------|
| Pricing unit/feature | % COP/EUR floor; fixed match; fixed mismatch; min; inactive; expired; not started; cap a subtotal; shipping intacto |
| Create order | con código escribe redemption+code+discount+used_count; sin código discount 0 |
| Preview | discount > 0 sin side effects |
| Limits | usage global race-safe; per-user user only; guest ignores per-user |
| Cancel | libera redención y used_count |
| Refund | no libera (si hay test F05 de refund, assert redemption sigue) |
| Admin | create percentage/fixed; reject fixed sin currency; immutable type/value con redemptions |
| HTTP/Livewire checkout | payload con couponCode happy + fail |

Usar factories; dinero enteros; `App::setLocale` solo si se aserta texto.

---

## 9. Riesgos y no-objetivos

| Riesgo | Mitigación |
|--------|------------|
| Doble uso concurrente del último cupón | `lockForUpdate` + recheck limit |
| Cancel no libera (olvidado) | Task explícita en CancelOrderAction + test R9 |
| Snapshot code perdido al borrar cupón | No hard-delete; code en redención |
| Front espera cupón en carrito | Documentar: solo checkout; D5 |
| Total 0 y F05 | Permitido en dominio; skip-pay out; ops/manual o feature futura |
| used_count desincronizado | Fuente de verdad = count redenciones; used_count cache en TX |

---

## 10. Orden de implementación sugerido

1. Migración `coupon_redemptions.code` + models/factories  
2. i18n + exceptions + `CouponPricingService`  
3. Admin Actions + Filament CouponResource  
4. Enganche Validate + CreateOrder  
5. Cancel release  
6. Order infolist código/descuento  
7. Tests + Pint + roadmap Completa  

Sin dependencias de F07/F08.
