# F04 — Checkout y órdenes

> **Estado:** Completa  
> **ID:** F04 · **Slug:** `04-checkout-orders`  
> **Prerequisitos:** F01 (catálogo), F02 (cuentas/direcciones admin), F03 (carrito) — ver [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **Desbloquea:** F05 (pagos)

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Producto y alcance F04 | [`specs/_global/01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md) |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Convenciones de código | [`AGENTS.md`](../../../AGENTS.md) / project-conventions |
| Esquema de dominio | `app/Models/Order.php`, `OrderItem.php`, `Cart.php`, `Address.php`; enums `OrderStatusEnum`, `CurrencyEnum` |
| Handoff carrito | [`specs/features/03-cart/requirements.md`](../03-cart/requirements.md) (revalidación y snapshots en F04) |
| UI orientativa | [`specs/ui-briefs/05-checkout.md`](../../ui-briefs/05-checkout.md) (no es DoD de marca) |

## User stories

1. **Como** visitante (guest), **quiero** completar un checkout con mis datos de contacto y dirección, **para** crear un pedido sin registrarme.
2. **Como** usuario autenticado, **quiero** confirmar un pedido usando una dirección de mi libreta o una dirección one-shot, **para** comprar sin reescribir datos si no quiero.
3. **Como** comprador, **quiero** que al entrar y al confirmar se revalide stock y elegibilidad, **para** no crear un pedido inválido.
4. **Como** comprador, **quiero** ver una página de gracias con acceso a mi pedido, **para** confirmar que la compra se registró.
5. **Como** administrador, **quiero** listar y ver pedidos y cancelar los `pending`, **para** operar el flujo pre-pago.
6. **Como** sistema de cobro futuro (F05), **quiero** órdenes `pending` con snapshots y sin stock ya descontado, **para** descontar inventario solo al confirmar pago.

## Alcance de esta feature

**Incluye:**

- Dominio: validar carrito para checkout, crear orden `pending` desde carrito con snapshots, cancelar `pending→cancelled` (admin), consultar orden (dominio/API).
- Revalidación de stock y elegibilidad al **entrar** al checkout y al **confirmar**.
- Precios **live** del catálogo al confirmar → snapshot en `order_items`; moneda de la orden = moneda del carrito.
- Totales: subtotal de líneas; `shipping_cost` de opción única configurable (“Envío estándar”); `discount = 0`; `tax_amount = 0`.
- Consumo del carrito (vaciar ítems) al crear la orden con éxito.
- **Sin** descuento de stock en F04 (queda para F05 al pago confirmado). Over-sell entre `pending` y `paid` es aceptado.
- Checkout **mínimo funcional** (HTTP y/o Livewire): contacto, dirección, notas, confirmar; sin paso de pago.
- Página simple de “Gracias por tu compra”.
- Guest: acceso a confirmación/orden vía **signed URL**.
- User: policy — solo ve sus propias órdenes.
- Filament: administración de pedidos (listar, ver detalle, cancelar `pending`).
- Feature tests PHPUnit del dominio, entrypoint y admin esencial.
- i18n `lang/{en,es}/orders.php` (y keys de navigation/enums si faltan).

**No incluye (F04):**

- Pagos reales Stripe/Bold ni webhooks (F05).
- Descuento de stock / reserva de inventario (F05 al `paid`).
- Cupones y `discount` real (F06).
- Impuestos por ubicación; envíos multi-opción o transportadoras.
- “Mis pedidos” en perfil storefront (diferido; solo dominio/API de consulta).
- UI de marca completa del brief de checkout (F01-S / polish posterior).
- Transiciones de estado distintas de crear `pending` y admin `pending→cancelled`.

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | DoD superficie | Dominio + checkout mínimo funcional + Filament de pedidos + página de gracias simple. |
| D2 | Perfil “mis pedidos” | **Diferido.** F04 expone consulta de orden en dominio/API; no UI de perfil. |
| D3 | Compradores | **Guest + user.** |
| D4 | Guest contacto | Nombre, apellidos, email, teléfono + dirección de envío completa. |
| D5 | User dirección | Libreta **+** one-shot (dirección distinta **sin** guardarla en libreta). |
| D6 | Revalidación | Al **entrar** al checkout y al **confirmar** la compra. |
| D7 | Stock | **No** descontar en F04. Descuento cuando el pago se confirma en **F05**. |
| D8 | Fallo stock/elegibilidad | **Bloquear** toda la orden y devolver al carrito con error. |
| D9 | Carrito post-orden | **Consumir/vaciar** ítems del carrito al crear la orden OK. |
| D10 | Moneda | Orden conserva la moneda del carrito. |
| D11 | Precios | **Live** al confirmar → snapshot en `order_items`. |
| D12 | Totales F04 | `shipping_cost` configurable simple; `discount = 0`; `tax_amount = 0`. |
| D13 | Envío | Una opción: **Envío estándar** (costo desde config, puede ser 0). |
| D14 | Estado al crear | Solo `pending`. |
| D15 | Admin estados | Solo `pending → cancelled`. Resto en F05+. |
| D16 | `order_number` | Formato legible `ORD-YYYYMMDD-XXXX`. |
| D17 | Pago en UI | **Sin** paso de pago; “Confirmar pedido” crea `pending`. |
| D18 | Cobro | Se puede confirmar sin pago real; cobro en F05. |
| D19 | Doble submit | Idempotencia: **una orden por conversión exitosa del carrito activo** (lock + carrito vacío post-éxito). |
| D20 | Guest post-compra | Acceso a confirmación/orden con **signed URL**. |
| D21 | AuthZ user | Cada usuario solo ve (y no muta ajenas) sus órdenes. |
| D22 | AuthZ admin | Gestión de pedidos en Filament (listar, ver, cancelar pending). |
| D23 | Cupones | Fuera de F04 (`discount = 0`). |
| D24 | Notas | `customer_notes` permitido desde v1. |
| D25 | Dinero | Enteros; COP pesos; EUR centavos (convención catálogo). |
| D26 | Naming | Slug `04-checkout-orders`, ID **F04**. Área de código: **Orders** (Actions/DTOs/Exceptions); checkout entrypoint delgado. |
| D27 | Idioma specs | Español. EARS según [`02-feature-quality.md`](../../_global/02-feature-quality.md). |
| D28 | Schema | Preferir tablas `orders` / `order_items` existentes; config de envío en `config/ecommerce.php` (o equivalente). Sin columnas de pago nuevas. |

---

## Criterios de aceptación (EARS)

### Happy path — en alcance F04

### R1 — Entrar al checkout con carrito válido

CUANDO un comprador (guest o autenticado) con carrito que tiene al menos una línea elegible y stock suficiente solicita el checkout,  
EL SISTEMA DEBE revalidar elegibilidad y stock de **todas** las líneas  
Y DEBE exponer el resumen (líneas, subtotal, costo de envío estándar configurable, total) en la moneda del carrito  
SIN crear una orden todavía  
SIN decrementar stock de variantes.

### R2 — Rechazo al entrar si el carrito no es comprable

CUANDO un comprador solicita el checkout y el carrito está vacío, o alguna línea no es elegible, o alguna cantidad supera el stock,  
EL SISTEMA DEBE rechazar el acceso al checkout (o el avance) e informar el error  
Y DEBE orientar al comprador a volver al carrito  
SIN crear una orden.

### R3 — Crear orden pending desde carrito (user)

CUANDO un usuario autenticado confirma el pedido con datos de contacto válidos, dirección de envío válida (libreta propia o one-shot) y carrito revalidado OK,  
EL SISTEMA DEBE crear una orden en estado `pending` con moneda del carrito,  
DEBE persistir snapshots de cada línea (`product_name`, `variant_label`, `sku`, `unit_price` live, `quantity`) y de la dirección de envío,  
DEBE fijar `discount = 0`, `tax_amount = 0` y `shipping_cost` según la tarifa configurable de envío estándar,  
DEBE asociar `user_id` al usuario,  
DEBE vaciar los ítems del carrito del usuario  
Y DEBE generar `order_number` con formato `ORD-YYYYMMDD-XXXX`  
SIN decrementar el stock de las variantes  
SIN marcar la orden como pagada.

### R4 — Crear orden pending desde carrito (guest)

CUANDO un visitante no autenticado confirma el pedido con nombre, apellidos, email, teléfono y dirección de envío completa válidos y carrito revalidado OK,  
EL SISTEMA DEBE crear una orden `pending` con `user_id` nulo y el email indicado,  
DEBE aplicar los mismos snapshots, totales y reglas de R3,  
DEBE vaciar el carrito guest de esa sesión  
Y DEBE exponer un acceso a la confirmación/orden mediante **URL firmada**.

### R5 — Página de gracias

CUANDO la creación de la orden finaliza con éxito,  
EL SISTEMA DEBE mostrar una página simple de confirmación (“Gracias por tu compra”) con el número de orden  
Y DEBE permitir al guest llegar a esa vista solo con signed URL (o redirección firmada inmediata post-confirmación)  
Y al usuario autenticado con ownership de la orden.

### R6 — One-shot de dirección (user)

CUANDO un usuario autenticado confirma con una dirección que **no** corresponde a un id de su libreta (one-shot),  
EL SISTEMA DEBE copiar esos campos al snapshot de la orden  
SIN crear ni modificar un registro en la libreta de direcciones del usuario  
(salvo que envíe explícitamente un `shipping_address_id` de su libreta, en cuyo caso puede setear la FK débil y copiar snapshot desde esa dirección).

### R7 — Dirección de libreta (user)

CUANDO un usuario autenticado confirma eligiendo un `shipping_address_id` que le pertenece,  
EL SISTEMA DEBE usar los datos de esa dirección para el snapshot de envío  
Y DEBE guardar `shipping_address_id` como referencia débil  
SIN fallar si más tarde la dirección de libreta se edita o borra (el snapshot manda).

### R8 — Notas del cliente

CUANDO el comprador envía `customer_notes` opcionales al confirmar,  
EL SISTEMA DEBE persistirlas en la orden  
Y SI no envía notas, DEBE dejar el campo nulo o vacío sin error.

### R9 — Consultar orden (dominio/API)

CUANDO un actor autorizado solicita el detalle de una orden por id (o número),  
EL SISTEMA DEBE devolver la orden con ítems y totales  
Y DEBE denegar el acceso si el actor no es el dueño (user), no presenta signed URL válida (guest) ni es admin del panel.

### R10 — Cancelar orden pending (admin)

DONDE un administrador autenticado con acceso al panel,  
CUANDO solicita cancelar una orden en estado `pending`,  
EL SISTEMA DEBE pasar el estado a `cancelled`  
SIN reponer stock (porque F04 no descontó stock)  
SIN permitir la misma transición desde estados distintos de `pending` en F04.

### R11 — Filament listado y detalle de pedidos

DONDE un administrador con acceso al panel,  
CUANDO abre el recurso de pedidos,  
EL SISTEMA DEBE listar órdenes (número, email, estado, moneda, total, fechas)  
Y DEBE permitir ver el detalle con ítems snapshot y dirección de envío  
Y DEBE ofrecer la acción de cancelar solo si el estado es `pending`.

### R12 — Checkout mínimo sin paso de pago

CUANDO el comprador completa el checkout mínimo,  
EL SISTEMA DEBE permitir confirmar el pedido **sin** seleccionar método de pago ni invocar pasarela  
Y DEBE dejar la orden en `pending` lista para F05.

---

### Validación y error

### R13 — Revalidación al confirmar (stock)

CUANDO al confirmar alguna línea del carrito supera el stock actual de la variante,  
EL SISTEMA DEBE rechazar la creación de la orden e informar el error  
Y DEBE devolver al comprador al carrito (flujo de error)  
SIN crear orden parcial  
SIN vaciar el carrito  
SIN decrementar stock.

### R14 — Revalidación al confirmar (elegibilidad / precio)

CUANDO al confirmar alguna línea no es elegible (producto/variante inactivos) o carece de precio en la moneda del carrito,  
EL SISTEMA DEBE rechazar la creación de la orden e informar el error  
Y DEBE devolver al carrito  
SIN crear orden.

### R15 — Carrito vacío o sin líneas válidas al confirmar

CUANDO se confirma con carrito sin ítems,  
EL SISTEMA DEBE rechazar la operación con error de dominio claro  
SIN crear una orden.

### R16 — Doble confirmación / concurrencia

CUANDO dos confirmaciones concurrentes intentan convertir el mismo carrito activo con ítems,  
EL SISTEMA DEBE crear **como máximo una** orden exitosa para esa conversión  
(usando bloqueo transaccional del carrito; la segunda falla por carrito vacío o conflicto)  
SIN duplicar pedidos por doble clic en condiciones normales de carrera.

### R17 — Datos de contacto/dirección inválidos

CUANDO el comprador confirma con email inválido, campos de dirección obligatorios vacíos u otros datos de borde inválidos,  
EL SISTEMA DEBE rechazar en la validación de borde  
SIN crear una orden.

### R18 — Ownership de carrito al confirmar

CUANDO se intenta confirmar un carrito que no corresponde a la sesión guest actual ni al usuario autenticado,  
EL SISTEMA DEBE denegar la operación  
SIN crear una orden sobre ese carrito.

### R19 — Transiciones de estado prohibidas en F04 (no admin cancel)

CUANDO un actor no admin (o un flujo no admin) intenta cambiar el estado de una orden a `paid`, `processing`, `shipped`, `delivered` o `refunded` vía las operaciones de F04,  
EL SISTEMA DEBE no exponer esas transiciones en el alcance F04  
(el único cambio de estado de escritura en F04 además de la creación en `pending` es admin `pending→cancelled`).

### R20 — Sin descuento de stock en F04

MIENTRAS una orden se crea o se cancela en F04,  
EL SISTEMA NO DEBE modificar el `stock` de las variantes por el solo hecho de crear o cancelar la orden.

### R21 — Usuario solo ve sus órdenes

CUANDO un usuario autenticado intenta ver una orden de otro usuario,  
EL SISTEMA DEBE denegar el acceso.

### R22 — Guest sin signed URL

CUANDO un visitante intenta ver la confirmación/detalle de una orden guest sin una signed URL válida (o expirada),  
EL SISTEMA DEBE denegar el acceso.

---

## Trazabilidad a fases siguientes

| Tema | Fase |
|------|------|
| Cobro Stripe/Bold, webhooks, `paid` | F05 |
| Descontar stock al pago confirmado | F05 |
| Cupones / `discount` real | F06 |
| “Mis pedidos” en perfil storefront | UI perfil / post-F04 |
| UI de marca checkout (stepper Stitch) | polish / F01-S |
| Impuestos y envíos avanzados | futuro |

---

## Definition of Done (resumen)

- Criterios **R1–R22** implementados y cubiertos por tests del alcance.
- Órdenes solo `pending` al crear; admin `pending→cancelled`.
- Snapshots de líneas y envío; sin descuento de stock; carrito vaciado al éxito.
- Checkout mínimo + gracias + signed URL guest + Filament pedidos.
- PHPUnit en verde vía Sail; Pint OK; roadmap F04 actualizado al cerrar.
