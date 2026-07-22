# F03 — Carrito · Diseño técnico

> **ID:** F03 · **Slug:** `03-cart`  
> **Requirements:** [`requirements.md`](requirements.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md)  
> **Producto:** [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **Dominio:** `app/Models/Cart.php`, `app/Models/CartItem.php`, `Product` / `ProductVariant` / `ProductVariantPrice`, `CurrencyEnum`  
> **Layout código:** tipo primero, área **Cart** (`app/Actions/Cart`, `app/DTOs/Cart`, `app/Exceptions/Cart`, `app/Services/Cart` si aplica)  
> **Stack en alcance:** Laravel 13, PHPUnit, Sail; Livewire v4 **o** routes HTTP mínimas  
> **Fuera de alcance F03:** Filament de carritos; storefront de marca (F01-S); checkout (F04); cupones (F06)

Este documento describe el **CÓMO**. El **QUÉ** está en `requirements.md`.

---

## 1. Alcance técnico

| Incluye (F03) | Excluye |
|---------------|---------|
| Actions + DTOs + excepciones de dominio Cart | Filament Resources de Cart |
| Resolución guest/user + merge | Auth UI storefront (login/registro páginas) |
| Pricing de **lectura** (service opcional) | Snapshot de precios en `cart_items` |
| Entrada HTTP o Livewire mínima | UI de marca, drawer, PDP polished |
| Feature tests dominio (+ entrypoint) | Reserva de stock, webhooks, cupones |
| `lang/{en,es}/cart.php` (mensajes) | Schema nuevo (salvo gap real) |

Sin migrations de esquema nuevas: `carts` y `cart_items` ya existen.

---

## 2. Modelo de datos (existente)

### `carts`

| Campo | Uso F03 |
|-------|---------|
| `user_id` nullable | Carrito de usuario; null en guest |
| `session_id` nullable + index | Carrito guest; UUID/string de sesión de app |
| `currency` string(3) default `COP` | Cast a `CurrencyEnum` |

### `cart_items`

| Campo | Uso F03 |
|-------|---------|
| `cart_id` | FK cascade |
| `product_variant_id` | Línea por variante |
| `quantity` unsigned int | ≥ 1 en persistencia; 0 en comando = delete |
| unique `(cart_id, product_variant_id)` | Upsert sumando |

**No** se agregan columnas de precio unitario en F03.

### Factories

- `CartFactory`: estados `guest()`, `eur()` ya existen; reutilizar.
- `CartItemFactory`: reutilizar; tests deben armar variante elegible con stock y precio.

---

## 3. Identidad del carrito y sesión

### Guest

1. El entrypoint obtiene un **token de sesión de carrito** estable:
   - Preferencia: valor en session Laravel (`cart_session_id`) generado en primer acceso si no existe; o cookie dedicada firmada si se elige API sin session cookie full.
2. `GetOrCreateCartAction` (o resolver dedicado) busca `Cart` con `user_id = null` y `session_id = token`.
3. Si no existe, crea con `currency` default `COP` (o la pedida si válida).

### User autenticado

1. Buscar carrito con `user_id = auth id` (un activo: el más reciente o el único; F03 asume **uno** — si hubiera basura histórica, tomar el de mayor `id` o consolidar en merge).
2. Crear si no existe.

### Merge (R7)

Disparar desde:

- listener de login / `Authenticated` **o**
- llamada explícita al inicio de request autenticada si hay guest token,

vía `MergeGuestCartIntoUserCartAction`:

1. Cargar guest cart por `session_id` (si hay ítems).
2. Cargar/crear user cart.
3. En `DB::transaction`: por cada ítem guest, upsert en user cart sumando qty con cap `min(stock, 99)`; si stock 0, omitir o fallar línea — **preferencia:** sumar hasta stock disponible; si stock 0, **no** agregar esa línea y continuar (merge best-effort) **o** documentar skip. **Decisión de implementación:** sumar hasta `min(stock, 99)`; líneas con stock 0 se omiten del merge sin abortar todo el merge.
4. Borrar ítems del guest (o delete del cart guest).
5. Invalidar uso del guest para esa sesión (limpiar binding).

Moneda en merge: si user cart y guest difieren, **ganar la moneda del user cart**; reprecio live al leer. Si una línea no tiene precio en moneda del user, omitir esa línea en merge o rechazar merge de esa línea (misma best-effort: omitir no elegibles).

---

## 4. Dominio: Actions, DTOs, Service

Actions invokables; DTOs `readonly`; multi-write en `DB::transaction` cuando haga falta.

### DTOs (`app/DTOs/Cart/`)

| Clase | Campos (orientativos) |
|-------|------------------------|
| `ResolveCartDTO` | `?userId`, `?sessionId`, `?CurrencyEnum currency` al crear |
| `AddCartItemDTO` | `cartId` o cart resuelto, `productVariantId`, `quantity` (≥ 1) |
| `UpdateCartItemQuantityDTO` | `cartId`, `productVariantId` o `cartItemId`, `quantity` (≥ 0; 0 = remove) |
| `ChangeCartCurrencyDTO` | `cartId`, `CurrencyEnum currency` |

### Actions (`app/Actions/Cart/`)

| Clase | Rol | Criterios |
|-------|-----|-----------|
| `GetOrCreateCartAction` | Resuelve guest/user cart | R1, R2 |
| `AddCartItemAction` | Elegibilidad + stock + upsert sumando | R3, R11, R12, R13, R15, R17 |
| `UpdateCartItemQuantityAction` | Update qty; 0 → delete línea | R4, R5, R12, R13, R15 |
| `RemoveCartItemAction` | Delete línea | R6 |
| `ClearCartAction` | Delete all items; keep cart row | R6 |
| `MergeGuestCartIntoUserCartAction` | Merge al auth | R7 |
| `ChangeCartCurrencyAction` | Valida precios de **todas** las líneas; update currency o throw | R9, R14 |
| `GetCartViewAction` (opcional) | Devuelve cart + lines + money view model/DTO | R8 |

### Service (solo si se reutiliza)

| Clase | Rol |
|-------|-----|
| `App\Services\Cart\CartPricingService` | Dado `Cart` con items+variants+prices: unit price, line subtotal, total en `cart.currency`. Enteros. |

No crear Service 1:1 que solo reenvíe a un Action.

### Elegibilidad (R11)

Al mutar cantidad hacia arriba o add, la variante DEBE:

1. `is_active = true`
2. Producto `is_active = true` (alineado a storefront; reutilizar lógica de `publishedForStorefront` o helper de dominio)
3. Existir `ProductVariantPrice` para `cart.currency`

### Stock y techo (R12, R13, R17)

```text
maxAllowed = min(variant.stock, 99)
newQty <= maxAllowed  // si no → excepción
// Nunca: variant.stock -= qty en F03
```

**R13:** **rechazar** si `newQty > 99` (aunque stock > 99), con mensaje claro.

### Excepciones (`app/Exceptions/Cart/`)

Ejemplos (nombres finales al implementar):

- `CartItemNotEligibleException`
- `InsufficientCartStockException`
- `CartQuantityNotAllowedException`
- `CartCurrencyChangeBlockedException`
- `CartAccessDeniedException`

Mensajes vía `__('cart....')` — no hardcode ES/EN en la excepción si el proyecto ya usa keys (patrón F01 `ProductCannotBePublishedException`).

---

## 5. Entrada mínima (R10)

Elegir **una** al implementar (no ambas salvo necesidad):

### Opción A — Livewire multi-file (preferida si ya hay app layout)

- Componente `App\Livewire\Cart\CartPage` (o `MiniCart`) con:
  - listado líneas + total
  - update qty / remove
- Ruta web nombre `cart.show`
- Add-to-cart puede ser método del mismo componente o `AddToCart` invocable desde una ruta de prueba

### Opción B — Routes + controller delgado

- `GET /cart`, `POST /cart/items`, `PATCH /cart/items/{variant}`, `DELETE ...`, `POST /cart/currency`
- Controller solo valida request y llama Actions; JSON o Blade simple

**Validación de borde:** Form Request o rules Livewire; Action recibe DTO ya saneado (qty int, currency enum).

**AuthZ:** ownership en Action o policy simple: guest solo su `session_id`; user solo su `user_id`.

No registrar Resource Filament.

---

## 6. i18n

| Archivo | Uso |
|---------|-----|
| `lang/en/cart.php` | Keys estables |
| `lang/es/cart.php` | Traducción operador/comprador |

Dominio ejemplo: `cart.errors.insufficient_stock`, `cart.errors.not_eligible`, `cart.errors.currency_blocked`, `cart.errors.quantity_max`.

---

## 7. Flujo resumido

```text
Request (session ± auth)
  → resolve session_id
  → GetOrCreateCartAction
  → [si auth + guest items] MergeGuestCartIntoUserCartAction

Mutación (add / update / remove / clear / currency)
  → validate edge
  → Cart Action + (transaction si multi-write)
  → exceptions de dominio si invariante falla

Lectura
  → Cart + items.variant.prices
  → CartPricingService / GetCartViewAction
  → totales enteros en cart.currency
```

---

## 8. Tests (PHPUnit) — DoD F03

| Área | Escenarios |
|------|------------|
| Guest resolve | Crea y reutiliza por session_id (R1) |
| User resolve | Un cart por user (R2) |
| Add new / add existing | Upsert suma (R3) |
| Update / zero remove | R4, R5 |
| Remove / clear | R6 |
| Merge | Suma variantes; guest no canónico (R7) |
| Pricing | Totales enteros COP/EUR (R8) |
| Currency ok / blocked | R9, R14 |
| Not eligible | R11 |
| Stock / max 99 | R12, R13 |
| Negative qty | R15 |
| Wrong owner | R16 |
| Stock no decrementa | R17 |
| Entrypoint mínimo | Al menos un happy path HTTP/Livewire (R10) |

Factories: `Product` + variant active + price en moneda; stock controlado.

---

## 9. Riesgos y no-objetivos

| Riesgo | Mitigación |
|--------|------------|
| Race stock entre dos guests | Aceptable en F03; F04 revalida |
| Multi carritos huérfanos por user | GetOrCreate elige uno; merge limpia guest |
| Livewire sin catálogo público | Entrypoint mínimo + tests; F01-S re-skin |
| Merge con monedas distintas | Gana moneda user; omitir no elegibles |

**No-objetivos:** multi-cart wishlist-style, persistir precio en línea, admin Filament carts, cupones.

---

## 10. Orden de implementación sugerido

1. Excepciones + lang keys  
2. DTOs + GetOrCreate + Add/Update/Remove/Clear  
3. Pricing service + GetCart view  
4. Change currency + Merge  
5. Entrypoint mínimo  
6. Tests + Pint  

Detalle ejecutable: [`tasks.md`](tasks.md).
