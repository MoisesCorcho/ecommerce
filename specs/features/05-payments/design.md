# F05 — Pagos · Diseño técnico

> **ID:** F05 · **Slug:** `05-payments`  
> **Requirements:** [`requirements.md`](requirements.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md)  
> **Producto:** [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **Dominio:** `Payment`, `PaymentWebhookEvent`, `Order`, `OrderItem`, `ProductVariant`; enums `PaymentProviderEnum`, `PaymentStatusEnum`, `OrderStatusEnum`, `CurrencyEnum`  
> **Layout código:** tipo primero, área **Payments** (`app/Actions/Payments`, `app/DTOs/Payments`, `app/Exceptions/Payments`, `app/Contracts/Payments`, `app/Gateways/Payments`); efectos de orden/stock reutilizan área **Orders** o Actions de Payments que orquestan en transacción  
> **Stack:** Laravel 13, HTTP entrypoints, Filament v5 (solo vista de order), PHPUnit, Sail  
> **Fuera de alcance F05:** auth storefront, refunds iniciados por nosotros, reposición de stock, cupones, fulfillment, emails, UI de marca

Este documento describe el **CÓMO**. El **QUÉ** está en `requirements.md`.

---

## 1. Alcance técnico

| Incluye (F05) | Excluye |
|---------------|---------|
| `PaymentGatewayInterface` + Stripe/Bold gateways | SDKs acoplados en Actions |
| Iniciar pago (Action) + redirect hosted | Embed widgets / Elements UI |
| Webhooks + idempotencia + firma | Procesar disputes UI |
| Marcar `paid` + descontar stock | `shipped` / `delivered` |
| Reintentos multi-`Payment` | Partial capture |
| Payments en Order Filament view | `PaymentResource` global |
| Link/botón Pagar mínimo en thank-you | Brand design system |
| Config + env keys | Secrets en repo |
| Feature tests con fakes | Llamadas reales a Stripe/Bold en CI |

Sin migrations de negocio nuevas salvo gap real. Reutilizar `payments`, `payment_webhook_events`, `orders`, `order_items`, `product_variants`.

---

## 2. Modelo de datos (existente)

### `payments`

| Campo | Uso F05 |
|-------|---------|
| `order_id` | FK orden |
| `provider` | `stripe` \| `bold` (cast enum) |
| `currency` | Copia `order.currency` |
| `external_id` | ID de sesión/intent/pago en el proveedor |
| `payment_method` | Opcional (card, etc. si el webhook lo aporta) |
| `status` | `pending` → `approved` \| `declined` \| `refunded` |
| `amount` | `order.total` (entero) |
| `raw_response` | JSON útil del create session y/o último update |
| `paid_at` / `refunded_at` | Timestamps de negocio del payment |

### `payment_webhook_events`

| Campo | Uso F05 |
|-------|---------|
| `provider` | stripe \| bold |
| `event_id` | ID idempotente del evento (unique con provider) |
| `event_type` | Tipo crudo del proveedor |
| `payload` | JSON completo |
| `processed_at` | Null hasta procesar (éxito o descarte controlado documentado) |

Unique ya existe: `(provider, event_id)`.

### `orders` (writes F05)

| Campo | Uso |
|-------|-----|
| `status` | `pending→paid`, `paid→refunded`; respeto `cancelled` |
| `paid_at` | Set al pasar a `paid` |

### Config

Extender `config/ecommerce.php` **o** añadir `config/payments.php` (preferir un solo lugar claro; si crece, `payments.php`):

```php
'payments' => [
    'stripe' => [
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        // public key solo si el entrypoint mínimo la necesita
    ],
    'bold' => [
        'api_key' => env('BOLD_API_KEY'),
        'webhook_secret' => env('BOLD_WEBHOOK_SECRET'),
        // resto según API Bold documentada al implementar
    ],
],
```

No commitear valores reales. Documentar en `.env.example` las keys.

### Factories

- Reutilizar `PaymentFactory`, `PaymentWebhookEventFactory`, `OrderFactory`, variantes con stock controlado en tests.

---

## 3. Flujo de extremo a extremo

```text
[Order pending F04]
    → (auth owner | signed) POST /orders/{order}/pay
         ↓ authorize
         ↓ lock order
         ↓ status === pending?
         ↓ revalidate stock for order_items (no decrement)
         ↓ resolve provider from currency (COP→Bold, EUR→Stripe)
         ↓ create Payment pending (amount=order.total)
         ↓ gateway.createHostedCheckout(payment, order, return URLs)
         ↓ store external_id + raw_response
         ↓ redirect to hosted URL
    → Comprador paga / cancela en proveedor
    → Return URL (success|cancel) → order/thank-you UX only
    → POST /webhooks/{stripe|bold}
         ↓ verify signature
         ↓ persist PaymentWebhookEvent (unique provider+event_id)
         ↓ map event → domain outcome
         ↓ apply side effects (idempotent)
```

### Mapeo de outcomes (dominio)

| Outcome | Payment | Order | Stock |
|---------|---------|-------|-------|
| Approved + order pending + stock OK | `approved`, `paid_at` | `paid`, `paid_at` | −qty por línea |
| Approved + order pending + stock FAIL | `approved` | **sin cambio** (`pending`) | sin cambio; señal ops |
| Approved + order cancelled | `approved` (registro) | **sin** `paid` | sin cambio |
| Approved + order already paid | no-op side effects | sin cambio | sin segundo decremento |
| Declined | `declined` | sigue `pending` | sin cambio |
| Refunded + order paid | `refunded`, `refunded_at` | `refunded` | **no** reponer (D24) |

---

## 4. Arquitectura de componentes

### Contrato

```text
app/Contracts/Payments/PaymentGatewayInterface.php
```

Métodos orientativos (ajustar nombres al implementar, mantener responsabilidad):

- `createHostedCheckout(Order $order, Payment $payment, HostedCheckoutReturnDTO $returns): HostedCheckoutSessionDTO`
- (opcional) helpers de verificación de firma si se prefiere en el gateway vs Action de webhook

DTOs en `app/DTOs/Payments/`:

- `StartOrderPaymentDTO` / returns
- `HostedCheckoutSessionDTO` (`redirectUrl`, `externalId`, `raw`)
- `HostedCheckoutReturnDTO` (`successUrl`, `cancelUrl`)
- `ProcessWebhookDTO` o payload tipado mínimo

### Gateways

```text
app/Gateways/Payments/StripePaymentGateway.php
app/Gateways/Payments/BoldPaymentGateway.php
```

- Bind: resolver por `PaymentProviderEnum` (factory/service `PaymentGatewayResolver` o match en Action).
- Tests: `FakePaymentGateway` / mock del interface.

### Actions (invokables preferidos)

| Action | Responsabilidad |
|--------|-----------------|
| `StartOrderPaymentAction` | AuthZ ya hecha en borde o Gate al inicio; lock order; validar pending; revalidate stock; crear Payment; llamar gateway; persistir external_id; devolver redirect URL. **No** marca paid. |
| `ProcessPaymentWebhookAction` | Verificar firma (vía gateway o verifier); persistir evento idempotente; mapear; aplicar efectos en `DB::transaction` con locks de order + variants. |
| (interno o action) `MarkOrderPaidFromPaymentAction` | Solo si stock OK y order pending: paid + stock. Separar solo si clarifica; evitar Action+Service 1:1 vacío. |
| (opcional) `ApplyPaymentRefundAction` | paid→refunded payment+order, sin stock. |

### Servicios

- Usar **Service** solo si la resolución de gateway o el mapeo de eventos se reutiliza entre Actions.
- No crear Service que solo reenvíe a un Action.

### Excepciones (`app/Exceptions/Payments/`)

| Excepción | Caso |
|-----------|------|
| `OrderNotPayableException` | status ≠ pending / ya paid |
| `PaymentStockUnavailableException` | fallo revalidación al iniciar |
| `PaymentGatewayException` | error al crear sesión hosted |
| `InvalidPaymentWebhookSignatureException` | firma inválida |
| `PaymentWebhookAlreadyProcessedException` | opcional; o retorno silencioso idempotente |
| `OrderPaidStockConflictException` | D25: approved sin poder marcar paid |

Mensajes i18n: `lang/{en,es}/payments.php` (+ enums ya existentes).

### AuthZ

| Actor | Iniciar pago | Ver return | Webhook |
|-------|--------------|------------|---------|
| Guest | Signed URL válida sobre la orden | Signed | N/A |
| User dueño | Policy order pay/view | Policy | N/A |
| User ajeno | Deny | Deny | N/A |
| Admin panel | No requiere pagar por storefront | View order + payments | N/A |
| Provider | N/A | N/A | Firma válida; CSRF exempt |

Reutilizar/extender `OrderPolicy` (`pay` ability) o middleware signed en rutas guest.

---

## 5. Rutas HTTP (mínimas)

| Método | Ruta | Nombre | Auth |
|--------|------|--------|------|
| POST | `/orders/{order}/pay` | `orders.pay` | `auth` **o** `signed` |
| GET | return success (existente thank-you o query `payment=processing`) | reutilizar `orders.thank-you` | auth o signed |
| GET | return cancel (misma vista + flash) | reutilizar o `orders.pay.cancel` | auth o signed |
| POST | `/webhooks/stripe` | `webhooks.stripe` | firma Stripe; sin session auth |
| POST | `/webhooks/bold` | `webhooks.bold` | firma Bold; sin session auth |

- Webhooks: excluir de CSRF (`ValidateCsrfToken` except o middleware stack API/raw body).
- Respuestas webhook: 2xx tras persistencia idempotente; 4xx firma inválida; 5xx para reintento del proveedor si falla procesamiento recuperable (documentar elección al implementar).

### Botón/link mínimo

En thank-you / order show (Livewire o blade existente):

- Si `status === pending` y actor autorizado → form/button POST `orders.pay` (o link que dispara el POST).
- Sin estilos de marca; suficiente para E2E manual y tests de feature.

---

## 6. Filament

- Extender **Order** view (Infolist / RelationManager `payments`): columnas provider, status badge, amount+currency, external_id, paid_at, created_at.
- Sin create/edit de payments desde admin en F05.
- Cancel pending (F04) se mantiene; documentar interacción D22 en tests.

---

## 7. Reglas de concurrencia

1. `StartOrderPaymentAction`: `Order::lockForUpdate()`; re-check status.
2. Webhook approved: lock order + lock variants (`lockForUpdate` en `product_variants` de las líneas) en una sola transacción.
3. Idempotencia evento: insert `payment_webhook_events` con unique; si duplicate key → cargar existente y salir sin side-effects.
4. Stock: `stock = stock - qty` solo si `stock >= qty` para **todas** las líneas; si alguna falla → D25 (payment approved, order no paid). Preferir validar todas antes de mutar, o mutar y rollback de la TX de orden/stock sin rollback del payment approved (payment se confirma **antes** o **fuera** del rollback de stock).  
   **Diseño recomendado:**  
   - TX1: actualizar payment → approved (si aún no).  
   - TX2: si order pending y no cancelled: intentar paid+stock; si stock fail → commit payment only + log `OrderPaidStockConflict`.  
   - Alternativa single-TX con savepoints: documentar en implementación la opción elegida; tests deben cubrir D25.

---

## 8. Integración proveedores (notas de implementación)

- Consultar docs actuales vía Boost/Context7 al codificar (no asumir API de memoria).
- Stripe: Checkout Session (mode payment) alineado a amount en minor units EUR.
- Bold: API de pago/checkout según doc oficial CO; mapear estados a `approved`/`declined`/`refunded`.
- Normalizar eventos a un enum interno de outcome si ayuda (`PaymentWebhookOutcomeEnum` solo si reduce match hell; no inventar status de payment fuera del enum existente).

---

## 9. i18n

- `lang/en/payments.php`, `lang/es/payments.php`: errores de inicio, stock, no payable, gateway, webhook.
- Labels de enums de payment ya vía `lang/*/enums.php` (verificar keys `payment_provider`, `payment_status`).
- Filament labels de payments en order view con keys, no hardcode.

---

## 10. Plan de tests (PHPUnit)

| Área | Escenarios |
|------|------------|
| Domain start | User dueño pending → payment pending + fake redirect; guest signed OK; foreign user 403; guest sin signed 403 |
| Start guards | Order paid/cancelled → fail; stock insuficiente al start → fail sin payment gateway call |
| Provider routing | COP→Bold, EUR→Stripe; amount = order.total |
| Webhook approved | Stock OK → order paid + stock decremented; idempotent redelivery |
| Webhook D25 | Stock fail → payment approved, order pending, stock intact |
| Webhook cancelled order | No paid, no stock change |
| Webhook declined | Payment declined, order pending |
| Webhook refund | Order paid → refunded, stock no restore |
| Signature | Invalid → no side effects |
| HTTP | POST pay redirects; webhooks endpoints |
| Filament | Order view shows payment rows (si el proyecto ya testea Filament) |

Usar factories; `Http::fake` o fake gateway bound en container.

---

## 11. Riesgos y mitigaciones

| Riesgo | Mitigación |
|--------|------------|
| Doble cobro / doble stock | Idempotencia event_id + locks + reject start si already paid |
| Return URL confunde “pagado” | Copy “confirmando…”; solo webhook marca paid |
| Approved sin stock (D25) | Tests explícitos; log/ops; refund manual fuera de F05 |
| Cancel vs approved race | Lock order; cancelled gana |
| Diferencias API Bold/Stripe | Interface + tests por gateway fake; adapters delgados |
| CSRF en webhooks | Excepción de ruta + raw payload |
| Secrets | Solo env; never commit |

---

## 12. Orden de implementación sugerido

1. Config + i18n + excepciones + interface + fake gateway  
2. `StartOrderPaymentAction` + rutas + botón mínimo  
3. `ProcessPaymentWebhookAction` + rutas webhook + firma  
4. Efectos paid/stock + D25 + cancelled race  
5. Decline + refund webhook  
6. Filament order payments  
7. Tests + Pint  

Alineado a `tasks.md`.
