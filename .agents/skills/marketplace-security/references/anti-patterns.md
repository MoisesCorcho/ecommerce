# Anti-patterns — marketplace security

## Absolute no

| Anti-pattern | Correct approach |
|--------------|------------------|
| `canAccessPanel(): return true` | Email allowlist via `config('ecommerce.admin_emails')` |
| Mark paid in thank-you / success controller | Webhook-only paid transition |
| Trust query `?paid=1` or provider redirect params | Ignore for money state; UX messaging only |
| CSRF except `*` or `api/*` | Except only `webhooks/stripe` and `webhooks/bold` |
| Verify signature on parsed JSON re-encoded body | Use `$request->getContent()` raw bytes |
| Skip signature “because sandbox” | Always verify; use Fake gateway in tests |
| `BOLD_WEBHOOK_SECRET=` empty in production | Non-empty secret or unset→`BOLD_SECRET_KEY` fallback |
| Client sends `amount` / `provider` for pay | Server derives from order + currency routing |
| `Order::where('id', $id)->first()` then pay without authz | Policy or signed URL first |
| Log `URL::temporarySignedRoute(...)` full string | Log route name + order id only |
| Bind real Stripe gateway in unit tests with live keys | `FakePaymentGateway` / container instance bind |
| Auto-restore stock on refund without product change | D24: no restore unless specs change |
| Silent second stock decrement on redelivery | Unique event + status guards |
| Treat unique event insert as “fully processed” when `processed_at` is null | Re-apply or claim-lock incomplete events |
| Accept client `discount`, `total`, or `coupon_id` on checkout | Only `couponCode`; server quotes via `CouponPricingService` |
| Increment `used_count` / write redemption on **preview** | Preview is read-only (H12 / C2) |
| Create order without re-validating coupon in same TX as consume | TOCTOU / over-limit / expired code slip |
| Consume coupon without `lockForUpdate` on coupon row | Double-spend under `usage_limit` |
| Release coupon on refund webhook | Abuse loop buy→refund→reuse (H13 / C9) |
| Fail to release coupon on **pending** cancel | Unfair burn; stuck limits |
| Persist coupon on cart (`carts.coupon_id`) in F06 | Out of scope; wrong application point |
| Distinct storefront messages per reject reason | Code enumeration |
| Admin hard-delete coupon or edit type/value after redemptions | Money history rewrite |

## Subtle / easy to regress

| Anti-pattern | Why it hurts here |
|--------------|-------------------|
| Long-lived signed pay links (weeks) | Bearer token for payment start |
| Putting signed links in third-party analytics | Token leakage |
| Admin Filament form that edits `payment.status` freely | Bypasses webhook invariants |
| Comparing webhook amounts as floats | Use integer minor units / stored ints consistently |
| HTTP provider call holding `lockForUpdate` too long | Availability + lock contention (hardening) |
| Verbose `Log::debug($payload)` of full webhook | PII retention in logs |
| Copying `.env.example` `APP_DEBUG=true` to prod | Stack traces + env leakage |
| New JSON “API” without CSRF/session model thought | Cart is session+CSRF today — don’t invent token API by accident |
| Guest checkout accepting `address_id` without `user_id` check | IDOR on addresses |
| Percentage applied including shipping | Undercharge shipping (C6) |
| Fixed coupon currency ignored | Cross-currency arbitrage (C7) |
| `StartOrderPayment` re-quotes coupon or uses subtotal | Charge wrong amount vs order snapshot |
| Applying per-user limit to guests | Spec says no (D33) — but then **global** must be sized |
| Creating order when invalid code was submitted (silent ignore) | User thinks discount applied; or attacker probes | 

## Review red flags (grep mental model)

- `preventRequestForgery(except:` expanded
- `processed_at` set without side effects, or duplicate return without re-apply path
- `PaymentStatusEnum::Approved` assignments outside webhook action
- `raw_response` / secrets in notifications or exceptions messages to clients
- New routes under `/orders/{order}` without signature or policy
- `Http::withToken(config(...))` logging request middleware dumps
- Request/DTO fields named `discount`, `coupon_id`, `total` filled from client on checkout
- `used_count` updates outside create/cancel coupon TX
- Refund / `PaymentWebhookOutcomeEnum::Refunded` path touching `CouponRedemption` or `used_count`
- Filament coupon form allowing type/value edit when redemptions exist

## If you find a conflict with specs

1. Do **not** silently “secure” by removing multi-intent or auto-refunding if D-ids forbid it.
2. Open residual risk in the audit table.
3. Propose product decision; then implement.
