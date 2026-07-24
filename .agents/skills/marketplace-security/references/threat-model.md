# Threat model — marketplace

Living map of **this** product. Update when new features add surfaces (auth storefront, wallets, public API, etc.).

## Assets

| Asset | Sensitivity | Where |
|-------|-------------|--------|
| Money movement | Critical | Payments, Stripe/Bold sessions, webhook apply |
| **Promo value / coupon inventory** | **High–Critical** | `coupons.used_count`, limits, `coupon_redemptions`, `orders.discount` |
| Stock integrity | High | Product stock decrement on paid |
| Order + shipping PII | High | `orders` snapshot, thank-you page, Filament Orders |
| User credentials | Critical | `users.password`, session cookies |
| Admin power | Critical | `ADMIN_EMAILS` → full Filament (incl. mint 100% coupons) |
| Cart contents | Medium | Session guest cart / user cart |
| Provider secrets | Critical | `STRIPE_*`, `BOLD_*` env |
| Webhook event log | Medium–High | Full JSON payloads in `payment_webhook_events` |
| Coupon codes | Medium–High | Campaign secrets; avoid enumeration via error messages |

## Actors

| Actor | Trust | Notes |
|-------|-------|--------|
| Anonymous browser | Untrusted | Cart/checkout; may probe IDs |
| Guest with signed link | Semi-trusted | Holds bearer capability for order TTL |
| Logged-in buyer | Authenticated | Owner of own orders only |
| Admin operator | Highly trusted | God mode today — compromise = full store |
| Stripe / Bold | External trusted after crypto verify | Never trust without signature |
| Attacker with leaked env | Catastrophic | Rotate keys; treat as incident |
| Malicious skill/dependency | Supply chain | Audit composer + agent skills |

## Trust boundaries

```text
[Browser] --session/CSRF + optional couponCode--> [checkout Actions] --quote/consume--> [DB coupons/orders]
[Browser] --signed URL----> [order thank-you / pay]
[Stripe/Bold] --HMAC raw--> [webhook controllers] --ProcessPaymentWebhookAction--> [DB + stock]
     (refund path must NOT release coupon redemptions)
[Admin browser] --Filament auth + allowlist--> [admin panel] --Coupons/Orders Actions--> [DB]
```

- Anything crossing **webhook** boundary: signature first.
- Anything crossing **order id** without session owner: signature required.
- Admin boundary is **email allowlist**, not fine-grained RBAC.
- Coupon **discount/total** never trusted from the browser — only `couponCode` string.

## STRIDE-style hotspots (practical)

| Category | Hotspots in this app |
|----------|----------------------|
| Spoofing | Forged webhooks; stolen signed URLs; session theft |
| Tampering | Client-supplied totals/provider/**discount/coupon_id**; mass assignment status; raw SQL |
| Repudiation | Missing audit logs on admin cancel / webhook apply / coupon create |
| Information disclosure | Thank-you email; Filament PII; verbose logs; `APP_DEBUG`; **distinct coupon error messages** |
| Denial of service | Unbounded webhook payload processing; no rate limit on pay/cart/**coupon preview** (gap) |
| Elevation of privilege | `canAccessPanel` bypass; policy skip; guest using others’ `addressId`; **admin mint unlimited 100% coupons** |

## Intentional product risks (do not “fix” without product)

Documented in F04/F05/**F06** specs — agents must **surface**, not silently invert:

| Risk | Spec | Security implication |
|------|------|----------------------|
| No stock reservation on pending order | F04/F05 | Over-sell window until paid webhook |
| Multi payment intents while pending | D14/D15 | Possible double charge |
| No auto-refund | F05 | Ops must refund extras / D25 |
| Refund without stock restore | D24 | Inventory drift after refunds |
| Return URL not source of truth | F05 | Correct — keep it that way |
| Admin = full power via email list | F02 | No least privilege inside panel |
| Guest not subject to per-user coupon limit | F06 D33 | Campaign drain via many guest sessions; use **global** limit |
| Abandoned pending holds coupon until admin cancel | F06 D37 | Limit inventory stuck; no auto-release |
| D25: coupon already consumed | F06 D39 | Burn without ship until ops resolves |
| Refund does not release coupon | F06 D38 | Correct for anti-abuse — support must explain |

## Out of scope today (assume unbuilt)

- Public token API (Sanctum multi-client)
- Buyer self-registration hardening beyond defaults
- PCI direct card handling (hosted checkout only — keep it that way)
- Marketplace multi-vendor tenancy

When any of these are built, **extend this file and the skill checklists** before coding.
