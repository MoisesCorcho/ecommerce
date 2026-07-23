# F05 — Pagos (Stripe / Bold + webhooks)

> **Estado:** Completa  
> **ID:** F05 · **Slug:** `05-payments`  
> **Prerequisitos:** F04 (checkout y órdenes `pending`) — ver [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **Desbloquea:** cobro real; stock al `paid`; base para fulfillment / F07 si exige compra

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Producto y alcance F05 | [`specs/_global/01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md) |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Convenciones de código | [`AGENTS.md`](../../../AGENTS.md) / project-conventions |
| Esquema de dominio | `app/Models/Payment.php`, `PaymentWebhookEvent.php`, `Order.php`, `OrderItem.php`, `ProductVariant.php`; enums `PaymentProviderEnum`, `PaymentStatusEnum`, `OrderStatusEnum`, `CurrencyEnum` |
| Handoff checkout | [`specs/features/04-checkout-orders/requirements.md`](../04-checkout-orders/requirements.md) (orden `pending`, sin stock descontado, guest signed URL) |
| Seguridad, hardening y residuales post-F05 | [`security-hardening.md`](security-hardening.md) (SH-\* código, RES-\* producto, ops go-live) |

## User stories

1. **Como** comprador (guest o autenticado) con una orden `pending`, **quiero** iniciar el pago del total de la orden, **para** completar la compra.
2. **Como** comprador en COP, **quiero** pagar con Bold; **como** comprador en EUR, **quiero** pagar con Stripe, **para** usar la pasarela alineada a la moneda de la orden.
3. **Como** comprador, **quiero** ser redirigido a un checkout hospedado del proveedor, **para** no ingresar datos de tarjeta en nuestra UI de marca (aún no existe).
4. **Como** sistema, **quiero** confirmar el cobro solo cuando el proveedor notifica el éxito de forma verificable e idempotente, **para** no marcar pagado por un simple return URL.
5. **Como** sistema, **quiero** descontar stock solo al pago aprobado (en la misma operación de negocio que marca la orden pagada), **para** respetar el handoff de F04.
6. **Como** comprador, **quiero** reintentar el pago si decliné o cancelé en la pasarela, **para** no perder la orden `pending`.
7. **Como** administrador, **quiero** ver los intentos de pago asociados a un pedido, **para** operar soporte sin un resource de pagos separado.
8. **Como** administrador, **quiero** que cancelar un `pending` impida que un webhook tardío lo marque como pagado, **para** no cobrar/operar pedidos ya cancelados.

## Alcance de esta feature

**Incluye:**

- Iniciar cobro sobre orden `pending` por el total fijado (`order.total` / `order.currency`).
- Routing de proveedor: **COP → Bold**, **EUR → Stripe** (sin elección manual del comprador).
- Checkout hospedado (redirect) por proveedor.
- Registro de intentos de pago (`payments`) con reintentos (varios registros; a lo sumo un `approved` efectivo por orden en el happy path de negocio).
- Webhooks Stripe y Bold: verificación de firma, persistencia de eventos, idempotencia por `(provider, event_id)`.
- Fuente de verdad de “pagado”: **webhook** (return URL solo UX).
- Efectos al pago aprobado: orden `pending → paid`, `paid_at`, descuento atómico de stock de las variantes de las líneas (cuando hay stock suficiente).
- Pago declinado / abandono: orden sigue `pending`; se puede reintentar.
- Webhook de refund del proveedor: si la orden estaba `paid`, pasar a `refunded` **sin** reponer stock en F05.
- Caso stock insuficiente al aprobar: registrar el pago `approved`, **no** marcar la orden `paid`, señal/log de dominio para ops; **sin** auto-refund en F05.
- Acceso guest a “pagar” / return vía **signed URL** (misma familia que thank-you F04); user dueño por ownership.
- Entrypoint HTTP mínimo: iniciar pago + webhooks; link/botón mínimo “Pagar” en thank-you/order view (sin UI de marca).
- Admin: ver payments en la ficha del pedido (no Resource Payments completo).
- Contratos de pasarela con implementaciones Stripe/Bold y fakes en tests.
- Config + env de keys; i18n de mensajes de pagos.
- Feature tests PHPUnit del dominio, webhooks e inicio de pago.

**No incluye (F05):**

- Auth de comprador (register/login storefront).
- Métodos de pago guardados / customer portal.
- Iniciar refunds desde admin o API propia (solo reaccionar a webhook).
- Reponer stock en refund.
- Cupones / recálculo de total al pagar (se cobra el total de la orden).
- Partial payments, multi-capture, disputes/chargebacks UI.
- Fulfillment (`shipped` / `delivered`).
- Email transaccional “pago recibido”.
- UI de marca / diseño premium de checkout de pago.
- Resource Filament dedicado solo a Payments.

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | DoD superficie | Dominio + webhooks + entrypoint HTTP mínimo + link/botón “Pagar” mínimo (sin UI de marca). |
| D2 | Refunds | Solo reaccionar a webhook del proveedor; no UI/API para iniciar refunds. |
| D3 | Admin | Payments visibles en la ficha del pedido (relation/infolist); no Resource Payments. |
| D4 | Auth comprador | **No requerido.** Guest + user como F04. |
| D5 | Métodos guardados / portal | **Out.** |
| D6 | Cupones (F06) | Cobrar siempre el total ya fijado en la orden. |
| D7 | Routing proveedor | **COP → Bold**, **EUR → Stripe.** |
| D8 | Provider por orden | Un provider derivado de la moneda; no se cambia entre reintentos. |
| D9 | Monto | Siempre `order.total` en `order.currency` (enteros; sin partial pay). |
| D10 | Inicio de cobro | Acción explícita “Pagar” sobre orden `pending` (no automático al crear la orden). |
| D11 | UX técnica | Hosted checkout / redirect (Stripe Checkout o equivalente + checkout Bold). |
| D12 | Guest acceso a pagar | **Signed URL** (familia thank-you F04). |
| D13 | User acceso a pagar | Dueño de la orden (policy); signed opcional OK. |
| D14 | Payments por orden | Varios intentos (nuevo registro `pending` por reintento); a lo sumo un camino de negocio “aprobado que paga la orden”. |
| D15 | Reintento con payment en vuelo | Permitir nuevo intento; el anterior queda a resolución por webhook (`pending`/`declined`). |
| D16 | Fuente de verdad “pagado” | **Solo webhook.** Return URL es UX (“confirmando…” / “no completado”). |
| D17 | Payment approved | Orden `pending → paid` + `paid_at` (si stock OK — ver D25). |
| D18 | Payment declined | Orden sigue `pending`; reintento permitido. |
| D19 | Payment refunded (webhook) | Si orden estaba `paid` → `refunded`; sin reponer stock (D24). |
| D20 | Transiciones order en F05 | Escritura: `pending→paid`, `paid→refunded` (webhook). Se mantiene admin F04 `pending→cancelled`. |
| D21 | Pagar solo `pending` | Rechazar inicio de pago si la orden no está `pending`. |
| D22 | Cancel admin vs webhook tardío | Si orden está `cancelled`, un `approved` tardío **no** marca `paid` (orden cancelada gana). |
| D23 | Stock al approved | Descontar en la **misma** operación de negocio que marca `paid`. |
| D24 | Stock en refund | **No** reponer en F05. |
| D25 | Sin stock al approved | Registrar payment `approved`; **no** marcar orden `paid`; señal/log para ops; **sin** auto-refund en F05. |
| D26 | Revalidación al iniciar pago | Revalidar stock (y elegibilidad de líneas si aplica) **antes** de redirigir al hosted checkout; si falla, no iniciar sesión de pago. |
| D27 | Idempotencia webhook | Unique lógico `(provider, event_id)`; reentrega no re-aplica side-effects. |
| D28 | Firma webhook | Verificación **obligatoria** (también en tests con fakes/keys de test). |
| D29 | Persistencia evento | Guardar siempre el evento de webhook; marcar procesado al aplicar (o al descartar de forma controlada). |
| D30 | Arquitectura | Puerto de pasarela + implementaciones Stripe y Bold; dominio no acoplado al SDK concreto. |
| D31 | Tests | Fakes/stubs de pasarela + payloads de webhook controlados. |
| D32 | Config | Keys y mode vía config + env (`STRIPE_*`, `BOLD_*` o equivalente). |
| D33 | Return success | Order/thank-you (signed si guest) con estado “confirmando pago…” hasta que el webhook actualice. |
| D34 | Return cancel | Misma vista de orden: pago no completado; ofrecer reintentar. |
| D35 | Email pago recibido | **Out** (diferido). |
| D36 | Entrypoints HTTP | Iniciar pago sobre orden + webhook Stripe + webhook Bold. |
| D37 | UI marca / premium pay | **Out.** |
| D38 | Auth register/login storefront | **Out.** |
| D39 | Shipped / delivered / fulfillment | **Out.** |
| D40 | Partial refund UI, disputes | **Out.** |
| D41 | Naming | Slug `05-payments`, ID **F05**. Áreas de código: **Payments** (+ efectos sobre **Orders** / stock de variantes). |
| D42 | Idioma specs | Español. EARS según [`02-feature-quality.md`](../../_global/02-feature-quality.md). |
| D43 | Schema | Preferir tablas `payments` / `payment_webhook_events` / `orders` existentes. Sin columnas nuevas salvo gap real documentado en design. |

---

## Criterios de aceptación (EARS)

### Happy path — en alcance F05

### R1 — Iniciar pago de orden pending (user)

CUANDO un usuario autenticado dueño de una orden en estado `pending` solicita pagar  
Y la revalidación de stock de las líneas es exitosa,  
EL SISTEMA DEBE crear un intento de pago en estado `pending` por el `total` y la `currency` de la orden  
Y DEBE usar el proveedor Stripe si la moneda es EUR, o Bold si la moneda es COP  
Y DEBE redirigir (o devolver la URL de redirección) al checkout hospedado del proveedor  
SIN marcar la orden como `paid`  
SIN decrementar stock todavía.

### R2 — Iniciar pago de orden pending (guest con acceso firmado)

CUANDO un visitante presenta un acceso firmado válido a una orden guest en estado `pending` y solicita pagar  
Y la revalidación de stock es exitosa,  
EL SISTEMA DEBE comportarse como en R1 (intento `pending`, proveedor por moneda, redirect hosted)  
SIN exigir autenticación de comprador.

### R3 — Webhook de pago aprobado (stock suficiente)

CUANDO el proveedor envía un evento de pago aprobado verificable e inédito para un intento de pago de una orden aún `pending`  
Y hay stock suficiente para todas las cantidades de las líneas de la orden,  
EL SISTEMA DEBE marcar el intento de pago como `approved` (con `paid_at` de pago si aplica)  
Y DEBE pasar la orden a `paid` con `paid_at`  
Y DEBE decrementar el stock de cada variante referenciada por las líneas según la cantidad de la línea  
Y DEBE hacerlo de forma atómica respecto de esos efectos de negocio  
Y DEBE registrar el evento de webhook como procesado  
SIN permitir que una reentrega del mismo evento vuelva a decrementar stock o altere de nuevo la orden.

### R4 — Return URL success (solo UX)

CUANDO el comprador regresa del checkout hospedado por la URL de éxito,  
EL SISTEMA DEBE mostrar la vista de orden/gracias indicando que el pago se está confirmando o el estado actual de la orden  
SIN marcar por sí solo la orden como `paid` basándose únicamente en esa visita.

### R5 — Reintento tras decline o cancelación en pasarela

CUANDO un intento de pago queda declinado (webhook) o el comprador cancela en la pasarela  
Y la orden sigue `pending`,  
EL SISTEMA DEBE permitir iniciar un nuevo intento de pago (nuevo registro)  
Y DEBE seguir usando el mismo proveedor derivado de la moneda de la orden.

### R6 — Admin ve intentos de pago en el pedido

DONDE un administrador autenticado con acceso al panel está en la ficha de un pedido,  
EL SISTEMA DEBE mostrar los intentos de pago asociados (proveedor, estado, monto, moneda, identificadores externos relevantes, fechas)  
SIN requerir un menú/resource global de pagos.

### R7 — Webhook de reembolso

CUANDO el proveedor envía un evento de reembolso verificable e inédito para un pago que había aprobado una orden en estado `paid`,  
EL SISTEMA DEBE marcar el pago como `refunded`  
Y DEBE pasar la orden a `refunded`  
SIN reponer stock de variantes en F05.

---

### Validación y error

### R8 — Rechazo al iniciar pago si la orden no está pending

CUANDO un actor autorizado intenta iniciar pago de una orden cuyo estado no es `pending`,  
EL SISTEMA DEBE rechazar el inicio  
SIN crear un nuevo intento de pago en el proveedor  
SIN cambiar el estado de la orden.

### R9 — Rechazo al iniciar pago por stock insuficiente

CUANDO un actor autorizado intenta iniciar pago de una orden `pending`  
Y alguna línea supera el stock actual de su variante,  
EL SISTEMA DEBE rechazar el inicio e informar el error  
SIN crear sesión de checkout en el proveedor  
SIN decrementar stock.

### R10 — Guest sin acceso firmado válido

CUANDO un visitante intenta iniciar pago o ver la vuelta de pago de una orden guest sin acceso firmado válido (o expirado),  
EL SISTEMA DEBE denegar el acceso  
SIN iniciar cobro.

### R11 — User no dueño

CUANDO un usuario autenticado intenta iniciar pago de una orden que no le pertenece,  
EL SISTEMA DEBE denegar la operación  
SIN iniciar cobro.

### R12 — Webhook con firma inválida

CUANDO llega un webhook con firma inválida o no verificable,  
EL SISTEMA DEBE rechazarlo (sin aplicar efectos de negocio sobre orden, stock ni pago)  
Y DEBE no marcarlo como procesado exitoso de negocio.

### R13 — Webhook reentregado (idempotencia)

CUANDO llega de nuevo un evento ya persistido con el mismo `(provider, event_id)`,  
EL SISTEMA DEBE no reaplicar side-effects de negocio (ni segundo descuento de stock ni segundo cambio de estado de orden)  
Y DEBE responder de forma no errónea al proveedor (p. ej. éxito/ack de recepción).

### R14 — Approved tardío sobre orden cancelada

CUANDO llega un pago aprobado del proveedor para una orden que ya está `cancelled`,  
EL SISTEMA DEBE registrar el intento/evento según reglas de persistencia  
Y DEBE NO pasar la orden a `paid`  
Y DEBE NO decrementar stock.

### R15 — Approved con stock insuficiente (D25)

CUANDO llega un pago aprobado verificable para una orden aún `pending`  
Y no hay stock suficiente para al menos una línea,  
EL SISTEMA DEBE marcar el intento de pago como `approved`  
Y DEBE NO pasar la orden a `paid`  
Y DEBE NO decrementar stock  
Y DEBE dejar una señal observable para operaciones (log y/o error de dominio registrado en el flujo de procesamiento)  
SIN iniciar reembolso automático en F05.

### R16 — Webhook declined

CUANDO el proveedor notifica un pago declinado verificable e inédito,  
EL SISTEMA DEBE marcar el intento de pago como `declined`  
Y DEBE dejar la orden en `pending` (si lo estaba)  
SIN decrementar stock.

### R17 — Monto del intento

CUANDO se crea un intento de pago,  
EL SISTEMA DEBE fijar el monto exactamente al `total` de la orden y la moneda de la orden  
SIN permitir un monto parcial distinto del total.

### R18 — Proveedor incorrecto para la moneda

CUANDO se inicia un pago,  
EL SISTEMA DEBE usar únicamente Bold para COP y Stripe para EUR  
SIN permitir al comprador elegir otro proveedor en F05.

### R19 — Return URL cancel

CUANDO el comprador regresa por la URL de cancelación del checkout hospedado,  
EL SISTEMA DEBE mostrar la orden como pago no completado y permitir reintentar si sigue `pending`  
SIN marcar la orden como `paid` ni `cancelled` solo por esa visita.

### R20 — Sin auto-pago al crear orden

CUANDO se crea una orden en checkout (F04),  
EL SISTEMA NO DEBE iniciar por sí solo una sesión de pago en F05  
(el cobro requiere la acción explícita de pagar).

### R21 — Un solo camino paid por orden

CUANDO una orden ya está `paid`,  
EL SISTEMA DEBE rechazar nuevos inicios de pago  
Y DEBE no volver a aplicar descuento de stock por webhooks de aprobación adicionales del mismo o de otro intento.

### R22 — Dinero en enteros

CUANDO se persisten montos de pago,  
EL SISTEMA DEBE almacenarlos como enteros en la convención de la moneda de la orden (COP pesos; EUR centavos)  
SIN usar floats para el monto.

---

## Definition of Done (producto)

- Decisiones D1–D43 reflejadas en design/tasks y sin contradicción con F04.
- Criterios R1–R22 cubiertos por tasks con `_(cubre Rx)_` y tests planificados.
- DoD de superficie D1 cumplible sin UI de marca ni auth storefront.
- Roadmap F05 actualizable a **Completa** solo tras implementación + tests verdes del alcance.

## Trazabilidad a handoff F04

| Compromiso F04 | Cierre en F05 |
|----------------|---------------|
| Orden nace `pending` sin cobro | R1, R20 |
| Sin descuento de stock en F04 | R3, D23 |
| Over-sell pending→paid aceptado | R9, R15, D25 |
| Guest signed URL | R2, R10 |
| Admin `pending→cancelled` | R14, D22 |
| Sin paso de pago en checkout | R20; pago es feature aparte |
