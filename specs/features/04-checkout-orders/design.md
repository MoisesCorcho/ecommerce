# F04 — Checkout y órdenes · Diseño técnico

> **ID:** F04 · **Slug:** `04-checkout-orders`  
> **Requirements:** [`requirements.md`](requirements.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md)  
> **Producto:** [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **Dominio:** `Order`, `OrderItem`, `Cart`/`CartItem`, `Address`, `ProductVariant`, `OrderStatusEnum`, `CurrencyEnum`  
> **Layout código:** tipo primero, área **Orders** (`app/Actions/Orders`, `app/DTOs/Orders`, `app/Exceptions/Orders`, `app/Policies` si aplica)  
> **Stack:** Laravel 13, Livewire v4 y/o HTTP, Filament v5, PHPUnit, Sail  
> **Fuera de alcance F04:** pasarelas de pago (F05), cupones (F06), descuento de stock, perfil “mis pedidos” UI

Este documento describe el **CÓMO**. El **QUÉ** está en `requirements.md`.

---

## 1. Alcance técnico

| Incluye (F04) | Excluye |
|---------------|---------|
| Actions + DTOs + excepciones Orders | Payment gateways / webhooks |
| Validación de carrito para checkout | Decremento de `stock` |
| Creación de orden + snapshots + clear cart | Cupones / tax engine |
| Cancel pending (admin Action + Filament) | Transiciones `paid`+ |
| Checkout mínimo + thank-you + signed URL | UI de marca completa |
| Policy de Order (view) | Perfil “mis pedidos” |
| Config costo envío estándar | Multi-carrier shipping |
| Feature tests | Schema de pagos nuevo |

Sin migrations de negocio nuevas salvo gap real. Reutilizar `orders` / `order_items`.

---

## 2. Modelo de datos (existente)

### `orders` (campos usados en F04)

| Campo | Uso |
|-------|-----|
| `order_number` | Único; generador `ORD-YYYYMMDD-XXXX` |
| `user_id` | Null guest; set user |
| `email` | Contacto (guest o user) |
| `status` | `pending` al crear; `cancelled` admin |
| `currency` | Copia del cart |
| `subtotal`, `shipping_cost`, `discount`, `tax_amount`, `total` | Enteros; discount/tax = 0 |
| `shipping_address_id` | Nullable; FK débil si libreta |
| `shipping_*` | Snapshot obligatorio |
| `customer_notes` | Opcional |
| `coupon_id`, `paid_at`, `shipped_at`, `tracking_number` | Null / no usados en F04 writes |

### `order_items`

| Campo | Uso |
|-------|-----|
| `product_variant_id` | FK nullable (snapshot manda) |
| `product_name`, `variant_label`, `sku` | Snapshot al confirmar |
| `unit_price`, `quantity` | Live price × qty al confirmar |

### Config

En `config/ecommerce.php`:

```php
'shipping' => [
    'standard_cost_cop' => (int) env('ECOMMERCE_SHIPPING_STANDARD_COST_COP', 0),
    'standard_cost_eur' => (int) env('ECOMMERCE_SHIPPING_STANDARD_COST_EUR', 0),
],
```

Costo según moneda del carrito/orden. Una sola opción lógica: “Envío estándar”.

### Factories

- `OrderFactory`: ajustar default `order_number` al formato D16 en tests nuevos o helper de generación de producción.
- Reutilizar `OrderItemFactory`, `Cart*`, `Address`, `Product*`.

---

## 3. Flujo de extremo a extremo

```text
[Cart F03] → GET checkout (ValidateCartForCheckout)
                ↓ OK: formulario + resumen
                ↓ FAIL: redirect cart + flash error
           → POST confirm (CreateOrderFromCart)
                ↓ lock cart
                ↓ revalidate lines
                ↓ create order + items (transaction)
                ↓ clear cart items
                ↓ redirect thank-you (signed if guest)
           → F05 later: pay → stock decrement (out of scope)
```

### Idempotencia / doble submit (D19, R16)

1. `DB::transaction` + `Cart::lockForUpdate()`.
2. Si el carrito no tiene ítems tras el lock → excepción “carrito vacío” (segunda request).
3. No se añade `cart_id` en `orders` en F04; la unicidad de conversión se garantiza por vaciado + lock.
4. Opcional en HTTP: redirect 303 a thank-you evita resubmit del POST.

---

## 4. Dominio: Actions, DTOs, excepciones

### DTOs (`app/DTOs/Orders/`)

| Clase | Campos orientativos |
|-------|---------------------|
| `CheckoutContactDTO` | `firstName`, `lastName`, `email`, `phone` |
| `CheckoutShippingDTO` | `fullName` o derivado, `phone`, lines, city, state, country, postalCode; `?addressId` |
| `CreateOrderFromCartDTO` | `cartId`, `?userId`, `?sessionId`, contact, shipping, `?customerNotes` |
| `CancelOrderDTO` | `orderId`, `actorUserId` (admin) |
| `OrderViewDTO` (opcional) | proyección de lectura para API/thank-you |

### Actions (`app/Actions/Orders/`)

| Action | Responsabilidad |
|--------|-----------------|
| `ValidateCartForCheckoutAction` | Carga cart+items+variants; revalida elegibilidad y stock **todas** las líneas; calcula subtotal live + shipping + total; **no** escribe. Lanza si vacío/inválido. |
| `CreateOrderFromCartAction` | Ownership cart; lock; revalidate; build snapshots; create order+items; clear cart items; return Order. **No** toca stock. |
| `CancelOrderAction` | Solo si `pending`; set `cancelled`. Admin. |
| `GetOrderAction` / show vía policy | Lectura con authz. |

### Revalidación

Reutilizar reglas de F03 (`AssertsCartItemEligibility` o extraer concern compartido si evita duplicación pesada):

- variante `is_active`, producto activo
- precio en moneda del cart
- `1 ≤ qty ≤ min(stock, 99)` (al checkout, qty ya persistida debe cumplir stock actual)

Si **una** línea falla → abortar **todo** (R8/R13/R14).

### Snapshots de línea

Por cada `CartItem`:

- `product_name` ← product.name  
- `variant_label` ← p.ej. color/label de variante si existe; si no, sku o string estable del modelo  
- `sku` ← variant.sku  
- `unit_price` ← `priceIn(cart.currency)->price`  
- `quantity` ← cart item qty  
- `product_variant_id` ← variant id  

### Snapshot de envío

- Si `addressId` y user: cargar `Address` del user; copiar campos; set FK.  
- Si one-shot / guest: copiar del DTO; `shipping_address_id = null`.  
- `shipping_full_name`: guest = `firstName + lastName` (o campo full del form); user puede reutilizar address.full_name.

### `order_number`

Generador dedicado (`OrderNumberGenerator` en `app/Support/Orders` o private en Action):

- Prefijo `ORD-`
- `Ymd` en timezone app
- Sufijo 4 chars: random alfanumérico o secuencia con retry on unique violation

### Excepciones (`app/Exceptions/Orders/`)

| Exception | Cuándo |
|-----------|--------|
| `CheckoutCartEmptyException` | Sin ítems |
| `CheckoutCartNotReadyException` | Fallo stock/elegibilidad (mensaje i18n; puede envolver detalle) |
| `OrderAccessDeniedException` | Ownership / signed |
| `OrderCannotBeCancelledException` | Estado ≠ pending |
| Reuso de cart access denied si aplica | Ownership cart |

Mensajes vía `__('orders.errors.*')`.

### Policy

`OrderPolicy`:

- `view`: admin panel gate **o** `order.user_id === auth id` **o** request con signed route válida (signed middleware en ruta guest).
- `cancel`: solo admin (Filament + Action).
- `create`/`update` genéricos: no exponer writes storefront salvo create via Action.

Admin panel sigue gate `admin_emails` existente (F01/F02).

---

## 5. Superficie HTTP / Livewire

### Rutas sugeridas

| Método | Ruta | Nombre | Auth |
|--------|------|--------|------|
| GET | `/checkout` | `checkout.show` | público (session) |
| POST | `/checkout` | `checkout.store` | público |
| GET | `/orders/{order}/thank-you` | `orders.thank-you` | auth **o** `signed` |

API de lectura opcional mínima (si se prefiere simetría con cart):

| GET | `/api/orders/{order}` | `orders.show` | auth o signed |

### Livewire (preferido si ya hay cart-page)

- Componente multi-file `checkout-page` (como `cart-page`): form contact + shipping + notes + resumen + confirm.
- Al `mount`: `ValidateCartForCheckoutAction`; on fail redirect `cart.page` + error.
- `confirm()`: Form Request rules o Livewire validate → `CreateOrderFromCartAction` → redirect thank-you.

Guest signed thank-you:

```php
URL::temporarySignedRoute('orders.thank-you', now()->addDays(7), ['order' => $order->id]);
```

User: misma ruta con middleware `auth` **o** signed opcional; policy view.

### Controller thin (alternativa/complemento)

`App\Http\Controllers\Orders\CheckoutController` + Form Requests:

- `ShowCheckoutRequest` / no body  
- `StoreCheckoutRequest` validación borde  

Validación de borde (no domain): email, strings required, country length 2, phone max, notes max.

---

## 6. Filament

`app/Filament/Resources/Orders/OrderResource`:

- Navigation group: p.ej. `orders` / Commerce (`__('navigation.groups.orders')`).
- Pages: `ListOrders`, `ViewOrder` (preferir View sobre Edit libre).
- Table: order_number, email, status badge, currency, total, created_at.
- Infolist: header + items repeater/table + shipping snapshot + notes.
- Action `cancel`: visible si `status === Pending`; llama `CancelOrderAction`.
- **No** CreateOrder desde admin en F04 (pedidos nacen del checkout).
- **No** editar snapshots a mano en F04 (evita inconsistencias).

i18n: `lang/{en,es}/orders.php`, `navigation.php` group.

---

## 7. AuthZ matrix

| Actor | Entrar checkout | Confirmar | Ver thank-you / order | Cancel |
|-------|-----------------|-----------|------------------------|--------|
| Guest | Sí (su cart session) | Sí | Signed URL | No |
| User | Sí (su cart) | Sí | Own order | No |
| Admin panel | N/A storefront | N/A | Filament view all | Pending only |

---

## 8. Tests (mapa)

| Área | Escenarios |
|------|------------|
| Domain | Validate OK; empty fail; stock fail; ineligible fail; create user; create guest; snapshots; totals shipping config; cart cleared; stock unchanged; cancel pending; cancel non-pending fails; order number format |
| Idempotency | Segundo create mismo cart vacío falla; no 2 órdenes |
| HTTP/Livewire | Checkout show redirect if bad cart; store happy; validation errors |
| Auth | User cannot view foreign; guest without signature 403; signed OK |
| Filament | Admin lista; cancel pending (actingAs admin email) |

Usar factories; `Config::set` para shipping cost en tests.

---

## 9. i18n

`lang/en/orders.php` + `lang/es/orders.php`:

- `errors.*` (empty, not_ready, access, cannot_cancel, …)
- `fields.*`, `actions.*`, `thank_you.*`, model labels Filament

Enums de status ya en `lang/*/enums.php` (verificar `order_status`).

---

## 10. Orden de implementación sugerido

1. Config shipping + lang + exceptions  
2. DTOs + Validate + Create + Cancel + number generator  
3. Policy + routes + checkout UI mínima + thank-you  
4. Filament Order resource  
5. Tests + Pint + marcar tasks/roadmap  

---

## 11. Riesgos y no-objetivos

| Riesgo | Mitigación |
|--------|------------|
| Over-sell pending→paid | Aceptado D7; F05 debe revalidar/descontar atómicamente |
| Doble submit | Lock + clear cart + redirect |
| Guest link leak | Temporary signed URL (TTL 7 días por defecto) |
| Address edit after order | Snapshot columns; FK débil |

**No** implementar en F04: Payment models writes, stock mutation, coupon application.
