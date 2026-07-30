# Payments & webhooks security

Canonical code (verify in tree if moved):

| Piece | Location |
|-------|----------|
| Interface | `app/Contracts/Payments/PaymentGatewayInterface.php` |
| Stripe | `app/Gateways/Payments/StripePaymentGateway.php` |
| Bold | `app/Gateways/Payments/BoldPaymentGateway.php` |
| Fake (tests) | `app/Gateways/Payments/FakePaymentGateway.php` |
| Start pay | `app/Actions/Payments/StartOrderPaymentAction.php` |
| Webhook apply | `app/Actions/Payments/ProcessPaymentWebhookAction.php` |
| HTTP | `app/Http/Controllers/Payments/*` |
| Config | `config/ecommerce.php` → `payments.*` |
| CSRF except | `bootstrap/app.php` |
| Specs | `specs/features/05-payments/*` |

## Happy path (secure)

```text
POST /orders/{order}/pay
  → authorize: OrderPolicy::pay OR valid temporary signature
  → StartOrderPaymentAction:
       lock order; status pending; stock recheck;
       Payment pending amount=order.total;
       gateway.createHostedCheckout → redirect URL
  → user pays on Stripe/Bold hosted page

Browser return (success/cancel)
  → thank-you UX only (processing / cancelled messaging)
  → MUST NOT set paid

POST /webhooks/{stripe|bold}
  → raw body = $request->getContent()
  → gateway.verifyWebhookSignature(raw, header)
  → parse → persist PaymentWebhookEvent (unique provider+event_id)
  → apply approved|declined|refunded side effects
  → 2xx only when safely handled (incl. intentional duplicates)
```

## Signature rules

### Stripe
- Header: Stripe-Signature style `t=…,v1=…`
- HMAC-SHA256 over `t.rawPayload` with `STRIPE_WEBHOOK_SECRET`
- Empty secret or header → **reject**
- Replay window ~300s — keep
- Tests: prefer synthetic signed payload when changing verifier

### Bold
- Header: `x-bold-signature`
- Payload: Base64(raw body) then HMAC-SHA256 hex
- Secret resolution (`webhookSigningSecret`):
  1. If `BOLD_WEBHOOK_SECRET` is **set** (including empty string) → use as-is
  2. If **unset** (`null`) → fall back to `BOLD_SECRET_KEY`
- **Empty secret = Bold test mode** (docs). Valid for local/sandbox only.
- **Production:** non-empty secret required. Empty string is an open relay for “SALE_APPROVED”.

### CSRF
- Except **only**: `webhooks/stripe`, `webhooks/bold`
- Pay route keeps CSRF (`@csrf` on thank-you form)

## Authorization for pay / thank-you

| Caller | Mechanism |
|--------|-----------|
| Owner logged in | `OrderPolicy::pay` / `view` (`user_id` match) |
| Guest | `URL::hasValidSignature($request)` on temporary signed routes |
| Foreign user | 403 always |

TTL conventions (current product):

- Guest thank-you: up to **7 days** (checkout)
- Pay / return links from payment start: ~**1 day**

When changing TTLs: shorter is safer; document product tradeoff.

## Status machine (security-relevant)

| Event outcome | Payment | Order | Stock |
|---------------|---------|-------|-------|
| approved + stock OK + order pending | approved | paid | decrement |
| approved + stock fail (D25) | approved | stays pending | no |
| approved + order cancelled | approved | stays cancelled | no |
| approved + order already paid | may approve extra payment | no second paid/stock | no second − |
| declined | declined | pending | no |
| refunded | refunded | refunded if was paid | **no restore (D24)** |

## Idempotency requirements

1. Unique constraint on `(provider, event_id)`.
2. Same event redelivered after **successful** process → ack, no side effects.
3. If insert succeeded but apply failed (`processed_at` still null):
   - Provider retries with same event_id
   - **Must re-attempt apply**, not return “duplicate success” forever
   - This is a known hardening point — treat regressions as **P0**

## Multi-intent / double charge (D15)

Product allows multiple pending payments / retries.

Security/ops expectations for agents:

- Do not claim “double charge impossible”
- Prefer UX that invalidates previous session when product allows
- Alert or log when second payment reaches **approved** for same order
- Auto-refund only with explicit product + gateway support

## Testing patterns (required)

```php
// Bind fake — never hit real Stripe/Bold in CI
$this->app->instance('payment.gateway.stripe', $fake);
$this->app->instance('payment.gateway.bold', $fake);

// Bold HTTP create may use Http::fake when testing real gateway class
```

Minimum cases:

- Invalid signature → 4xx, no payment/order change
- Valid approved → paid + stock when appropriate
- Duplicate event_id → single stock decrement
- Foreign user pay → 403
- Guest unsigned thank-you/pay → 403
- Bold empty secret accepts HMAC with `""` (sandbox) **and** wrong secret rejects

## Config checklist

| Env | Prod requirement |
|-----|------------------|
| `STRIPE_SECRET_KEY` | live key |
| `STRIPE_WEBHOOK_SECRET` | non-empty `whsec_…` |
| `BOLD_API_KEY` | prod |
| `BOLD_SECRET_KEY` | prod |
| `BOLD_WEBHOOK_SECRET` | non-empty **or** unset to fall back to secret_key — **never empty string in prod** |
| `APP_URL` | public HTTPS (signed URLs + Bold callback rules) |

## Logging

Allowed: `order_id`, `payment_id`, `provider`, `event_id`, outcome, error class.

Avoid: authorization headers, full signed query strings, webhook secrets, complete customer payloads when not needed for debugging.
