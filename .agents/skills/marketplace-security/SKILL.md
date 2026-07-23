---
name: marketplace-security
description: >
  Project security hard rules and threat model for this Laravel marketplace
  (catalog, cart, checkout, orders, Stripe/Bold payments, Filament admin).
  Use when implementing or reviewing authz, policies, payments, webhooks,
  signed URLs, cart ownership, admin panel access, secrets, logging, or
  production readiness; when auditing for IDOR, forgery, double-charge,
  or money/stock races; when the user says "seguridad", "security audit",
  "harden", "threat model", "listo para prod", or runs /marketplace-security.
  Complements laravel-security (generic Laravel) — this skill owns project
  domain risks; do not restate generic OWASP essays here.
metadata:
  short-description: "Marketplace domain security (payments, authz, webhooks)"
  version: "1.0"
  stack: "laravel v13 · filament v5 · livewire v4 · stripe · bold · php 8.5"
---

# Marketplace Security (Project)

Actionable **domain security** for this repo. Generic Laravel hardening lives in
**`laravel-security`** and `laravel-best-practices/rules/security.md` — load those
for mass assignment, XSS, SQLi, session basics. **This skill owns what those do not:**
admin gate, guest signed access, cart tenancy, Stripe/Bold webhooks, money/stock
invariants, and residual product risks (D14/D15/D24/D25).

| Concern | Source of truth |
|---------|-----------------|
| Architecture (Actions/DTOs/Gateways) | `AGENTS.md` / project conventions |
| Feature acceptance (EARS, D-ids) | `specs/features/**` especially `05-payments` |
| Generic Laravel secure coding | skill `laravel-security` |
| Filament UI quality | skill `filament-admin-standards` |
| **Domain threat model & hard rules** | **This skill** |
| API syntax (version-specific) | Laravel Boost `search-docs` / Context7 |

## When to activate (mandatory)

Load **before** writing or approving code when any of these apply:

- Payments, gateways, webhooks, `Payment*` Actions/Models, order pay/thank-you
- Policies, `canAccessPanel`, `ADMIN_EMAILS`, Filament destructive ops
- Cart ownership, session guest identity, checkout address resolution
- Signed URLs, temporary signed middleware, CSRF exceptions
- Secrets/env for Stripe/Bold, logging of payloads, production go-live
- User asks for security audit, harden, prod readiness, or `/marketplace-security`

Also activate **after** implementing a feature that touches money, identity, or
admin power — run the DoD checklist even if the user did not say “security”.

## Relationship to other skills

1. **`laravel-security`** — framework baseline (fillable, CSRF default, XSS, hashing).
2. **This skill** — project invariants and attack surface map.
3. **Specs** — product decisions that intentionally accept risk (e.g. no auto-refund).
   Security work **must not silently reverse** D-ids; escalate residual risk instead.

If guidance conflicts: **specs + this skill win for domain money/authz**; generic
skill wins for pure framework hygiene.

---

## Non-negotiable hard rules (P0)

Violate any of these → **block merge / block prod**, do not “fix later”.

| # | Rule | Why |
|---|------|-----|
| H1 | **Never mark order/payment paid from return/success URL** | Attacker controls browser redirects. Source of truth = verified webhook. |
| H2 | **Always verify webhook signature on raw body** before parse/side effects | CSRF-exempt endpoints are public write surface. |
| H3 | **Stripe webhook secret must never be empty** | Fail closed (`InvalidPaymentWebhookSignatureException`). |
| H4 | **Bold empty signing secret is sandbox-only** | `BOLD_WEBHOOK_SECRET=""` → forgeable HMAC. **Forbidden in production.** |
| H5 | **CSRF exceptions only for named webhook routes** | Today: `webhooks/stripe`, `webhooks/bold`. No wildcards, no `api/*`. |
| H6 | **Order view/pay without login requires valid temporary signed URL** | Unsigned order id = IDOR. Policy owner OR signature — nothing else. |
| H7 | **Treat signed URLs as bearer secrets** | Do not log full signed URLs; keep TTLs tight; never put signature in analytics. |
| H8 | **Admin panel: never `canAccessPanel(): true`** | Access = email in `config('ecommerce.admin_emails')` / `ADMIN_EMAILS`. Empty list = lockout. |
| H9 | **Cart mutations must assert ownership** | User cart → `user_id`; guest → `session_id` + null user. Use existing trait/pattern. |
| H10 | **Depend on `PaymentGatewayInterface` / binds, never hardcode live keys** | Keys only via `config/ecommerce.php` ← env. Fake gateway in tests. |

---

## Threat model (this product)

Read [references/threat-model.md](references/threat-model.md) for actors and assets.
Summary of **highest-value attacks**:

| Attack | Impact | Primary control |
|--------|--------|-----------------|
| Forged Stripe/Bold webhook | Free goods / false paid | Signature + raw body + fail closed |
| Empty Bold webhook secret in prod | Anyone mints “approved” | Non-empty prod secret; optional env assert |
| IDOR on `/orders/{id}/thank-you` or pay | PII + start payment for others | Signed URL or `OrderPolicy` |
| Shared/leaked signed thank-you link | Email/status/pay for TTL | Short TTL; no logging; no long-lived links in emails without product decision |
| Double hosted checkout (multi-intent) | Double charge | Product residual D15 — document; prefer UX/ops mitigation; no silent second stock− |
| Return URL “paid” UI trust | Fraud UX / support chaos | Return = `processing` only |
| Admin email list takeover | Full catalog/orders/PII | Protect `ADMIN_EMAILS`; strong account passwords; no self-serve admin |
| Mass assignment on Order/Payment | Status/amount tampering | Fillable whitelist; never accept status from request |
| Guest cart session fixation / leak | Cart theft | Session config; ownership asserts; regenerate on login (framework) |

---

## Surface map (where bugs hide)

| Surface | Paths | Agent must check |
|---------|-------|------------------|
| Webhooks | `PaymentWebhookController`, `*PaymentGateway::verifyWebhookSignature`, `ProcessPaymentWebhookAction`, `bootstrap/app.php` CSRF except | H1–H5, idempotency, status machines |
| Start pay | `StartOrderPaymentController`, `StartOrderPaymentAction`, `OrderPolicy::pay` | AuthZ, pending-only, stock recheck, amount=`order.total` |
| Guest access | signed `orders.thank-you`, `orders.pay`, thank-you blade | H6–H7, TTL |
| Admin | `User::canAccessPanel`, Filament Resources, cancel order | H8, no IDOR across users in admin forms |
| Cart | `AssertsCartOwnership`, `CartController` `api/cart/*` | H9, CSRF stays on |
| Gateways | `app/Gateways/Payments/*`, `config/ecommerce.php` | H3–H4, H10, timeouts, no secret logs |
| Specs | `specs/features/05-payments/*` | Align with R/D ids; call out residual risk |

Deep dive payments: [references/payments-webhooks.md](references/payments-webhooks.md).

---

## Authorization model (project reality)

| Actor | Can | Cannot |
|-------|-----|--------|
| Guest (session) | Cart, checkout, create pending order, access own order via **signed** URL, start pay via signed URL | Admin, other users’ carts/orders/addresses |
| Authenticated buyer | Same + `OrderPolicy` owner view/pay without signature when logged in as owner | Admin panel (unless email allowlisted) |
| Admin (`ADMIN_EMAILS`) | Full Filament: catalog, users, addresses, orders, cancel pending, see payments | Fine-grained roles (none today — treat admin as **god mode**) |
| Payment provider | POST webhooks only; trust only after signature | Anything else |

**Rules for agents:**

- Do **not** add Spatie/Shield/roles without explicit product approval.
- New order/payment endpoints **must** reuse owner policy and/or signed URLs — do not invent “token in query” ad hoc.
- Filament: panel gate is necessary but not always sufficient for future multi-tenant; prefer policies when operations diverge.
- Guests: `addressId` only if owned by `Auth::id()`; guests use snapshot fields only (existing checkout pattern).

---

## Payments & money invariants

| Invariant | Required behavior |
|-----------|-------------------|
| Amount | Hosted checkout amount = locked `order.total` (and currency) at start pay |
| Provider routing | Currency → provider (`EUR`→Stripe, `COP`→Bold) via existing resolver/enum — no client-chosen provider |
| Start pay | Order `pending` only; revalidate stock; create `Payment` `pending`; redirect to hosted URL |
| Paid transition | **Only** verified webhook approved path marks payment approved and (when stock OK) order paid + stock− |
| Declined | Payment final declined; order stays pending (retry allowed per product) |
| Refunded | Per D24: mark refunded; **do not** auto-restore stock unless product changes |
| D25 stock conflict | Payment may be approved while order stays pending — log + ops path; **no** silent free ship without process |
| Idempotency | Unique `(provider, event_id)`; redelivery must not double stock− |
| Retry correctness | If event row exists with `processed_at` null after failure, **re-apply** — do not treat unique hit as “done” forever |
| Multi-intent | Multiple pending payments allowed (D15) → **double charge residual**; never invent auto-refund without product decision |

---

## Secrets, config, logging

| Do | Don't |
|----|-------|
| Read secrets via `config('ecommerce.payments.*')` | Hardcode keys; commit `.env` |
| Log payment/order **ids**, provider, event type | Log full signed URLs, API keys, card data (we should never have PAN) |
| Prefer structured context on webhook failures | Dump entire provider body if it may contain PII beyond need — minimize |
| Document env in `.env.example` without real values | Copy live keys into tests/fixtures |
| Bold prod: non-empty signing secret | Ship `BOLD_WEBHOOK_SECRET=` empty to production |

Production assert (recommended when hardening):

```php
// Conceptual — place only if product approves boot-time fail-fast
if (app()->isProduction() && config('ecommerce.payments.bold.webhook_secret') === '') {
    throw new RuntimeException('BOLD_WEBHOOK_SECRET must not be empty in production.');
}
```

---

## Implementation workflow (agents)

1. **Map surface** — which row in Surface map is touched?
2. **Load references** — payments → `references/payments-webhooks.md`; full audit → `threat-model.md` + `checklist.md`.
3. **Baseline Laravel** — if generic issue (XSS, fillable), apply `laravel-security` / best-practices security rule.
4. **`search-docs` / Context7** — for framework APIs (signed URLs, CSRF, Http client); for Stripe/Bold contract details use Context7 when verifying provider behavior.
5. **Implement minimal change** — preserve Actions/Gateways/DI patterns; no new top-level `app/` folders.
6. **Tests required** for security-sensitive paths:
   - Foreign user → 403 on pay/view
   - Guest without signature → 403
   - Guest with valid signature → allowed
   - Invalid webhook signature → 4xx, no side effects
   - Duplicate event_id → no double stock
   - Fake gateway binds (`payment.gateway.stripe|bold`) — never real network in unit/feature tests
7. **Pint** dirty PHP; run **narrow** `vendor/bin/sail artisan test --compact` on affected tests.
8. **Self-check** [references/checklist.md](references/checklist.md) + [references/anti-patterns.md](references/anti-patterns.md).
9. **Report residual risk** in the PR/reply: accepted product risks vs new holes.

### Audit / review output format

When auditing (not just coding), return:

```markdown
## Security findings
| Sev | Finding | Evidence | Fix |
|-----|---------|----------|-----|
| P0/P1/P2 | … | file:line or behavior | … |

## Residual accepted risks
- (from specs / product)

## Tests / verification
- …
```

Severities:

- **P0** — exploitable money/authz/forgery in prod config
- **P1** — reliable abuse or serious data exposure with realistic conditions
- **P2** — defense-in-depth, ops gap, low likelihood

---

## Definition of Done (security bar)

### Always (any security-touching change)
- [ ] Hard rules H1–H10 not violated
- [ ] Authorization path explicit (policy and/or signed URL and/or admin gate)
- [ ] No secrets in code, tests, logs, or committed env
- [ ] Feature tests cover deny + allow cases for the surface
- [ ] Residual risks named if product accepts them

### Payments / webhooks additionally
- [ ] Signature on raw body; CSRF except list unchanged or justified
- [ ] Paid only via verified webhook apply path
- [ ] Idempotent event handling; stock decrement once
- [ ] Bold empty secret not recommended/documented for prod
- [ ] Amount/currency not client-controlled

### Admin additionally
- [ ] `canAccessPanel` still allowlist-based
- [ ] Destructive actions confirmed; domain via Actions

### Prod go-live additionally
- [ ] Live keys + non-empty webhook secrets
- [ ] `APP_DEBUG=false`, HTTPS `APP_URL`
- [ ] Webhook endpoints registered at providers
- [ ] Smoke charge test plan per provider
- [ ] Ops notes for D25 / double-charge / refund without stock

---

## References

- [references/threat-model.md](references/threat-model.md) — actors, assets, trust boundaries
- [references/payments-webhooks.md](references/payments-webhooks.md) — Stripe/Bold controls and residual risks
- [references/checklist.md](references/checklist.md) — pre-merge / pre-prod checklist
- [references/anti-patterns.md](references/anti-patterns.md) — forbidden patterns with project examples
- Specs: `specs/features/05-payments/`, `specs/_global/01-product-and-roadmap.md`
- Conventions: `AGENTS.md`
