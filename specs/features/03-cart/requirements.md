# F03 — Carrito

> **Estado:** Lista para implementar  
> **ID:** F03 · **Slug:** `03-cart`  
> **Prerequisitos:** F01 catálogo admin (variantes, precios multi-moneda, elegibilidad de venta) — ver [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **No bloquea:** F01-S (storefront de marca); F02 admin de cuentas (el carrito guest no depende de UI de auth comprador)

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Producto y alcance F03 | [`specs/_global/01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md) |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Convenciones de código | [`AGENTS.md`](../../../AGENTS.md) / project-conventions |
| Esquema de dominio | `app/Models/Cart.php`, `app/Models/CartItem.php`, migrations `carts` / `cart_items`, precios en `product_variant_prices` |
| Elegibilidad catálogo | Scopes y reglas de F01 (`Product::publishedForStorefront`, variantes activas, precio por moneda) |

## User stories

1. **Como** visitante (guest), **quiero** agregar variantes a un carrito ligado a mi sesión, **para** armar una compra sin registrarme.
2. **Como** usuario autenticado, **quiero** un carrito propio, **para** retomar mis ítems entre sesiones.
3. **Como** comprador que se autentica con ítems de guest, **quiero** que se fusionen con mi carrito de usuario, **para** no perder lo que ya elegí.
4. **Como** comprador, **quiero** cambiar cantidades, quitar líneas y vaciar el carrito, **para** controlar lo que llevaré al checkout (F04).
5. **Como** comprador, **quiero** ver totales en la moneda del carrito con precios actuales del catálogo, **para** saber cuánto pagaría antes del checkout.
6. **Como** comprador, **quiero** cambiar la moneda del carrito cuando todas las líneas tengan precio en esa moneda, **para** evaluar COP o EUR sin carritos a medias.

## Alcance de esta feature

**Incluye:**

- Dominio de carrito: obtener/crear carrito (guest o user), add (upsert sumando), update qty, remove, clear, merge al auth, cambio de moneda con reprecio live.
- Validación de elegibilidad y stock al mutar (sin reserva de inventario).
- Totales/precios de lectura desde `product_variant_prices` en la moneda del carrito (enteros; sin snapshot en `cart_items`).
- Entrada HTTP o Livewire **mínima** para ejercitar el flujo (no UI de marca).
- Feature tests PHPUnit del dominio (y del entrypoint mínimo).
- i18n de mensajes de dominio/validación del carrito (`lang/{en,es}/cart.php` o claves acordadas).

**No incluye (F03):**

- Storefront de catálogo con manual de marca (F01-S).
- Filament CRUD de carritos de terceros / panel admin de carritos.
- Checkout, órdenes, snapshots de precio en orden (F04).
- Reserva de stock, hold de inventario, jobs de expiración agresiva.
- Cupones / descuentos (F06).
- Pagos (F05), wishlist, reviews.
- Auth storefront completa (login/registro UI) — el merge se prueba a nivel dominio / actingAs; no es DoD una pantalla de login de tienda.

### Fuera de alcance por decisión de slice

La UI “de marca” del carrito (drawer, PDP add-to-cart polido) se re-skinnea cuando exista F01-S. F03 entrega comportamiento correcto y una superficie delgada.

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | Identidad | **Guest + user.** Guest: carrito por `session_id`. User: **un** carrito activo por `user_id`. |
| D2 | Merge al auth | Al autenticarse con carrito guest: **merge** al carrito del user; misma variante → **sumar qty** (cap por stock y techo D8). Luego el guest deja de ser el canónico (se descarta/vacía). |
| D3 | Superficie | **Núcleo = Actions + tests.** Entrada HTTP o Livewire **mínima**. **Sin** Filament de carritos ni storefront de marca. |
| D4 | Moneda | Campo `currency` en el cart; **default COP**. Precios **live** desde catálogo. **Sin** snapshot de precio en `cart_items`. |
| D5 | Cambio de moneda | Permitido con reprecio live de todas las líneas. Si **alguna** variante **no tiene** precio en la moneda destino → **bloquear** el cambio (error de dominio); no dejar el carrito a medias. |
| D6 | Elegibilidad al mutar | Solo variantes de producto **vendible**: producto activo/publicable para storefront, variante **active**, con **precio** en la moneda del carrito. |
| D7 | Stock | Al add/update: `qty ≤ stock` actual. **Sin reserva** en carrito. Over-sell posible hasta checkout; **F04 revalida**. |
| D8 | Cantidades | Add = **upsert sumando**. `qty = 0` → **remove** línea. `qty < 1` (salvo 0→remove) inválido. Techo por línea: `min(stock, 99)`. |
| D9 | Unicidad de línea | Una fila por `(cart_id, product_variant_id)` (ya en schema). |
| D10 | Schema | Sin columnas nuevas por defecto. Usar `carts` / `cart_items` existentes. |
| D11 | Cupones | Fuera de F03 (F06). |
| D12 | Naming | Slug `03-cart`, ID **F03**. Área de código: **Cart**. |
| D13 | Dinero | Enteros; COP pesos enteros; EUR centavos (misma convención de catálogo/F01). |
| D14 | Idioma specs | Español. EARS según [`02-feature-quality.md`](../../_global/02-feature-quality.md). |

---

## Criterios de aceptación (EARS)

### Happy path — en alcance F03

### R1 — Carrito guest por sesión

CUANDO un visitante no autenticado solicita su carrito o agrega el primer ítem con un identificador de sesión válido,  
EL SISTEMA DEBE obtener o crear un carrito con `user_id` nulo, `session_id` de esa sesión y moneda por defecto COP (salvo que se indique otra moneda válida al crear)  
Y DEBE reutilizar el mismo carrito guest en operaciones posteriores de esa sesión.

### R2 — Carrito de usuario autenticado

CUANDO un usuario autenticado solicita su carrito o muta ítems,  
EL SISTEMA DEBE usar el carrito activo asociado a su `user_id` (creándolo si no existe)  
Y DEBE ser el único carrito activo de ese usuario para F03.

### R3 — Agregar variante (upsert sumando)

CUANDO se agrega una cantidad válida de una variante elegible a un carrito existente,  
SI la variante **no** estaba en el carrito, EL SISTEMA DEBE crear una línea con esa cantidad  
Y SI ya estaba, EL SISTEMA DEBE sumar la cantidad a la línea existente  
SIN crear una segunda línea para la misma variante  
Y la cantidad resultante DEBE respetar el techo `min(stock, 99)`.

### R4 — Actualizar cantidad de línea

CUANDO se actualiza la cantidad de una línea existente a un entero entre 1 y el techo permitido,  
EL SISTEMA DEBE persistir la nueva cantidad en esa línea.

### R5 — Cantidad cero elimina la línea

CUANDO se actualiza la cantidad de una línea a **0**,  
EL SISTEMA DEBE eliminar esa línea del carrito  
SIN dejar una fila con cantidad 0.

### R6 — Quitar línea y vaciar carrito

CUANDO se solicita eliminar una línea concreta, EL SISTEMA DEBE quitar solo esa línea.  
CUANDO se solicita vaciar el carrito, EL SISTEMA DEBE eliminar todas las líneas de ese carrito  
conservando el registro del carrito (identidad guest/user/moneda) salvo decisión técnica contraria documentada en design (preferencia: conservar cabecera del cart).

### R7 — Merge guest → user al autenticarse

CUANDO un usuario se autentica y existe un carrito guest de su sesión con ítems,  
EL SISTEMA DEBE fusionar esos ítems en el carrito del usuario sumando cantidades por variante (con techos de stock/99),  
EL SISTEMA DEBE dejar de usar el carrito guest como canónico de esa sesión  
Y el carrito del usuario DEBE reflejar el resultado del merge.

### R8 — Lectura de precios y total en moneda del carrito

CUANDO se consulta el carrito con ítems cuyas variantes tienen precio en la moneda del carrito,  
EL SISTEMA DEBE exponer por línea el precio unitario actual (entero) y el subtotal de línea, y el total del carrito,  
TODO en la moneda del carrito  
SIN usar floats para montos  
Y SIN persistir esos montos en `cart_items`.

### R9 — Cambio de moneda exitoso

CUANDO se solicita cambiar la moneda del carrito a otra moneda soportada (COP o EUR)  
Y **todas** las variantes del carrito tienen precio en la moneda destino (o el carrito está vacío),  
EL SISTEMA DEBE actualizar la moneda del carrito  
Y las lecturas de precio posteriores DEBEN usar la nueva moneda.

### R10 — Entrada mínima usable

DONDE existe la superficie HTTP o Livewire mínima de F03,  
CUANDO un cliente realiza add / update qty / remove / ver carrito con datos válidos,  
EL SISTEMA DEBE aplicar las mismas reglas de dominio que las Actions  
Y DEBE reflejar el estado actualizado del carrito en la respuesta.

---

### Validación y error — en alcance F03

### R11 — Variante no elegible

CUANDO se intenta agregar o sumar una variante que no es vendible (producto inactivo o no publicable, variante inactiva, o sin precio en la moneda del carrito),  
EL SISTEMA DEBE rechazar la operación e informar el motivo de dominio  
SIN crear ni incrementar la línea.

### R12 — Stock insuficiente

CUANDO se intenta dejar una línea con cantidad mayor al stock disponible de la variante,  
EL SISTEMA DEBE rechazar la operación e informar el límite  
SIN persistir la cantidad inválida.

### R13 — Techo de cantidad por línea

CUANDO se intenta dejar una línea con cantidad mayor a 99 (aunque el stock sea mayor),  
EL SISTEMA DEBE rechazar la operación o capear según la regla fija de implementación documentada en design (**rechazar** es la opción preferida para feedback claro)  
SIN superar 99 unidades en la línea.

### R14 — Cambio de moneda bloqueado

CUANDO se solicita cambiar la moneda del carrito  
Y al menos una línea tiene una variante **sin** precio en la moneda destino,  
EL SISTEMA DEBE rechazar el cambio  
SIN modificar la moneda del carrito  
Y SIN eliminar líneas en silencio.

### R15 — Cantidad inválida (negativa o no entera de negocio)

CUANDO se envía una cantidad menor que 0,  
EL SISTEMA DEBE rechazar la operación  
SIN alterar la línea.

### R16 — Carrito u ownership incorrecto

CUANDO se intenta mutar un carrito que no corresponde a la sesión guest actual ni al usuario autenticado,  
EL SISTEMA DEBE denegar la operación  
SIN aplicar cambios a ese carrito.

### R17 — Sin reserva de stock

MIENTRAS un ítem está solo en el carrito (sin checkout),  
EL SISTEMA NO DEBE decrementar el stock de la variante por el solo hecho de estar en el carrito.

---

## Trazabilidad a fases siguientes

| Tema | Fase |
|------|------|
| Revalidar stock y elegibilidad al cerrar compra | F04 |
| Snapshots de precio/nombre en `order_items` | F04 |
| Cupones sobre carrito u orden | F06 |
| UI de marca catálogo + add-to-cart polido | F01-S |
