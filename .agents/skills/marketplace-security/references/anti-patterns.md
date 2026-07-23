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

## Review red flags (grep mental model)

- `preventRequestForgery(except:` expanded
- `processed_at` set without side effects, or duplicate return without re-apply path
- `PaymentStatusEnum::Approved` assignments outside webhook action
- `raw_response` / secrets in notifications or exceptions messages to clients
- New routes under `/orders/{order}` without signature or policy
- `Http::withToken(config(...))` logging request middleware dumps

## If you find a conflict with specs

1. Do **not** silently “secure” by removing multi-intent or auto-refunding if D-ids forbid it.
2. Open residual risk in the audit table.
3. Propose product decision; then implement.
