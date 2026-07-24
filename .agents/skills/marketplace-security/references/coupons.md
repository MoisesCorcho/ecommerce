# Coupons / F06 — security

Domain security for **cupones y redenciones**. Product decisions live in
`specs/features/06-coupons/` (D/R ids). This file is the **security bar** agents
must apply when changing coupon code or auditing money + promo abuse.

Load with parent skill `marketplace-security` + `laravel-security` for baseline.

## Scope boundary

| In security scope | Out (do not invent without product) |
|-------------------|-------------------------------------|
| Preview quote without consume | Coupon on cart persistence |
| Confirm consume + redemption snapshot | Multi-coupon / stacking |
| Cancel pending release | Free-shipping coupons, BOGO, gift cards |
| Refund must **not** release | Catalog pivots / product eligibility |
| Global + per-user limits + races | Auto-release abandoned pending |
| Client must not send `coupon_id` / discount | Skip-pay free-order flow (total 0) |
| Admin immutables after redemption | Brand storefront UI polish |

---

## Money lifecycle (trust path)

```text
[Browser] couponCode (string only)
    │
    ▼
ValidateCartForCheckout  ──quote only──► discount in preview (NO redemption, NO used_count++)
    │
    ▼
CreateOrderFromCart (TX)
    lock coupon (lockForUpdate)
    re-quote + re-validate
    set order.coupon_id, order.discount, order.total
    create coupon_redemptions (code snapshot, discount_amount)
    used_count++
    clear cart
    │
    ▼
StartOrderPayment ── amount = order.total (never re-quote coupon)
    │
    ▼
Webhook approved ── paid (coupon stays consumed)
    │
Cancel pending ── DELETE/void redemption + used_count-- (floor 0)
Refund (F05)   ── redemption STAYS; used_count unchanged
```

**Source of truth after create:** `orders.discount` + `orders.coupon_id` +
`coupon_redemptions` row (with `code` snapshot). F05 must not recompute discount.

---

## Non-negotiable coupon invariants (P0 for F06)

| # | Invariant | Why |
|---|-----------|-----|
| C1 | Client sends **`couponCode` string only** — never `coupon_id`, never `discount` / `total` from request | Prevents IDOR and price forgery |
| C2 | **Preview does not write** redemption or `used_count` | Prevents “probe and burn” / state pollution |
| C3 | **Confirm re-validates** in same TX as consume; invalid code **blocks** order create (no silent full-price order when code was sent) | Fail closed on abuse / race |
| C4 | Consume only on **create `pending`**: redemption + `used_count++` + order money fields in **one TX** with coupon **`lockForUpdate`** | Double-spend / over-limit races |
| C5 | **At most one coupon per order** (`orders.coupon_id` singular; unique redemption per order/coupon as schema allows) | No stacking bypass |
| C6 | Discount base = **line subtotal only**; cap `min(calculated, subtotal)`; shipping **not** zeroed by 100% off | Undercharge shipping |
| C7 | Fixed coupons: **currency must match** cart/order; percentage: currency null, any market | Cross-currency fixed theft |
| C8 | **Cancel `pending→cancelled`**: release redemption + `used_count--` (≥ 0) | Fair reuse + inventory of limits |
| C9 | **Refund / paid→refunded: do NOT release** coupon | Abuse loop: buy → refund → reuse campaign codes |
| C10 | Payment amount = locked **`order.total`** after discount — gateways never re-apply coupon | Pay wrong amount |
| C11 | Storefront errors are **generic** (“invalid code”); specific reason only log/admin | Code enumeration via messages |
| C12 | Admin: **no hard-delete** operativo; with redemptions **immutable** type/value/currency | Rewrite history of money |

---

## High-value attacks

| Attack | Impact | Control |
|--------|--------|---------|
| Double redeem under global `usage_limit` (2 parallel confirms) | Over-limit discounts | `lockForUpdate` on coupon + TX + re-check count |
| Authenticated user races `usage_limit_per_user` | Extra uses | Same lock + count by `user_id` inside TX |
| Guest farm to bypass per-user limits | Campaign drain (global only) | **Accepted residual** (D33); only global limit applies to guests — ops/campaign design |
| Send `coupon_id` / crafted discount | Free or inflated discount | Ignore client money fields; only `couponCode` |
| Preview vs confirm TOCTOU (code expires / limit hits between) | Unexpected create or free ride | Re-validate on confirm; fail closed |
| Invalid code on confirm but order created anyway | Wrong UX / partial burn | Block create (C3) |
| Cancel after paid releases coupon | Double use after real purchase | Release only on **pending** cancel |
| Refund releases coupon | Buy→refund→reuse | C9 |
| Fixed coupon in EUR applied to COP cart | Currency arbitrage | C7 |
| Percentage without floor / wrong base (incl. shipping) | Under/over charge | Floor % on subtotal only (C6) |
| Enumerate valid codes via distinct errors / timing | Code discovery | Generic storefront errors (C11); consider rate limit (P2) |
| Admin edits type/value after redemptions | Historical money lies | C12 + Action guard |
| Mass-assign `orders.discount` / `coupon_id` from request | Price forgery | Fillable + only Actions set money |
| Coupon still consumed on D25 (pay approved, order pending) | Limit burned without ship | **Accepted** product (D39); ops path |

---

## Surface map (coupons)

| Surface | Paths | Check |
|---------|-------|-------|
| Pricing | `CouponPricingService` | normalize code; rules; floor %; currency; min order; limits **read**; optional lock |
| Admin write | `CreateCouponAction`, `UpdateCouponAction`, Filament `CouponResource` | C12; fixed requires currency; % 1–100; no hard-delete |
| Preview | `ValidateCartForCheckoutAction` | C2; optional code; no writes |
| Confirm | `CreateOrderFromCartAction` | C3–C7, C1; TX + lock |
| Cancel | `CancelOrderAction` | C8 only when pending→cancelled |
| Refund path | `ProcessPaymentWebhookAction` refund | C9 — no coupon touch |
| Pay | `StartOrderPaymentAction` | C10 `amount = order.total` |
| Checkout UI | Livewire checkout-page | `couponCode` only; generic errors |
| Schema | `coupons`, `coupon_redemptions.code`, `orders.coupon_id/discount` | snapshot code; constraints |
| Tests | `tests/Feature/Coupons/**` | limits, race path, cancel/refund, invalid confirm |

---

## Authorization notes

| Actor | Coupon capability |
|-------|-------------------|
| Guest | May apply code; redemption `user_id` null; **global** limit only |
| Buyer | Same + **per-user** limit counts their `user_id` |
| Admin | CRUD cupones + view redemptions; cancel pending (releases); **god mode** for 100% campaigns |
| Client JS | Must not choose discount amount or coupon PK |

Cancel/release is admin (or domain cancel path already authorized) — do not expose “release my coupon” without cancelling a pending order you own per existing order policies.

---

## Required tests (security-sensitive)

Minimum when touching coupon money paths:

- [ ] Preview valid code → discount > 0, **no** redemption, `used_count` unchanged
- [ ] Confirm valid → redemption + `code` snapshot + `used_count++` + order fields
- [ ] Confirm invalid → **no** order, cart intact, no consume
- [ ] Global limit exhausted → reject; lock path does not exceed limit
- [ ] Per-user limit blocks user; guest not subject to per-user
- [ ] Cancel pending → release + `used_count` down; coupon reusable after
- [ ] Refund → redemption remains
- [ ] Fixed currency mismatch rejected
- [ ] Admin: fixed without currency rejected; immutables after redemption
- [ ] Payment start uses `order.total` (discounted), not raw subtotal

---

## Pre-merge checklist (coupons)

- [ ] C1–C12 hold in code paths touched
- [ ] No `coupon_id` / discount / total accepted from untrusted input
- [ ] Preview is read-only for coupon state
- [ ] Create uses TX + `lockForUpdate` when consuming
- [ ] Cancel pending releases; refund does not
- [ ] Generic storefront errors only
- [ ] Admin immutables + no operational hard-delete
- [ ] Tests above green (or justified N/A)
- [ ] Residual risks listed (guest vs per-user, abandoned pending holds limit, D25 burn)

---

## Residual / accepted product risks

| Risk | Spec | Security implication |
|------|------|----------------------|
| Guest ignores per-user limit | D33 | Campaigns must size **global** limit for guest traffic |
| Abandoned pending holds redemption until admin cancel | D37 | Limit inventory “stuck”; no auto-release job |
| D25 pay approved / order pending | D39 | Coupon already consumed |
| No rate limit on preview/confirm codes | — | Enumeration / brute force residual (P2 harden) |
| Admin god mode can mint 100% coupons | F02/F06 | Protect `ADMIN_EMAILS`; no RBAC yet |
| total = 0 allowed in domain | D40 | Skip-pay out of scope — ensure pay path does not explode |

---

## Specs

- `specs/features/06-coupons/requirements.md` — D1–D49, R1–R23
- `specs/features/06-coupons/design.md`
- `specs/features/06-coupons/tasks.md`
- Handoff: F04 checkout/orders, F05 payments (`order.total` locked)
