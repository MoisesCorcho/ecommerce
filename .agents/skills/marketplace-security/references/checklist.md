# Security checklist — marketplace

Use before merge on security-sensitive work, and always before claiming **production-ready**.

## A. Identity & access

- [ ] `User::canAccessPanel()` still gated by `admin_emails` (never unconditional `true`)
- [ ] New admin capabilities assume **god mode** — no accidental public exposure
- [ ] Order view/pay: owner policy **or** valid temporary signature only
- [ ] Foreign user denied (test present or added)
- [ ] Guest without signature denied
- [ ] Cart actions assert ownership (user vs session guest)
- [ ] Checkout cannot attach another user’s `address_id`

## B. Payments & webhooks

- [ ] Paid status only from verified webhook apply path
- [ ] Return/success URL does not mutate payment/order to paid
- [ ] Webhook uses **raw body** for signature
- [ ] Invalid signature → client error, no side effects
- [ ] CSRF exceptions limited to named webhook routes
- [ ] Stripe empty webhook secret rejected
- [ ] Bold: empty secret documented as sandbox-only; prod secret non-empty
- [ ] Hosted amount/currency from server-side order totals (**post-discount** `order.total`)
- [ ] Provider not chosen by untrusted client input
- [ ] Idempotent `event_id` handling; no double stock decrement
- [ ] Incomplete process (`processed_at` null) can still be retried correctly
- [ ] D25 / cancel+approved / multi-approved residual risks acknowledged
- [ ] Refund webhook does **not** release coupon redemptions / `used_count`

## B2. Coupons / F06 (when promo/discount code touched)

Deep dive: [coupons.md](coupons.md). Hard rules H11–H13 + C1–C12.

- [ ] Client input is **`couponCode` string only** (no `coupon_id`, no client `discount`/`total`)
- [ ] Preview/validate quotes discount **without** redemption or `used_count++`
- [ ] Confirm/create re-validates; invalid code **blocks** order create
- [ ] Consume in one TX: lock coupon, set order money fields, redemption + `code` snapshot, `used_count++`
- [ ] At most one coupon per order
- [ ] Discount on line subtotal only; cap to subtotal; shipping not zeroed by 100% off
- [ ] Fixed: currency must match; percentage: multi-currency
- [ ] Cancel `pending→cancelled` releases redemption + decrements `used_count` (floor 0)
- [ ] Refund / paid path does **not** release coupon
- [ ] Storefront errors generic (no reason that enumerates valid codes)
- [ ] Admin: no operational hard-delete; type/value/currency immutable after redemptions
- [ ] Tests: preview no-write, confirm consume, invalid confirm, limits, cancel release, refund keep
- [ ] Residual named: guest vs per-user (D33), stuck pending (D37), D25 burn (D39)

## C. Data & input

- [ ] Models keep fillable whitelists; no status/amount mass assignment from request
- [ ] Blade uses `{{ }}` for untrusted data (order email, names, etc.)
- [ ] No secrets in repo, factories, or committed snapshots
- [ ] Logs omit secrets and full signed URLs

## D. Framework baseline (delegate to laravel-security if needed)

- [ ] CSRF on state-changing web routes (except approved webhooks)
- [ ] Passwords hashed; hidden on User
- [ ] No raw SQL with string interpolation
- [ ] File uploads constrained if touched (mime, size, disk)

## E. Tests & verification

- [ ] Feature tests for deny paths (403 / invalid signature)
- [ ] Feature tests for happy path with `FakePaymentGateway` or `Http::fake`
- [ ] `vendor/bin/sail artisan test --compact` on affected files
- [ ] Pint on dirty PHP

## F. Production go-live (ops)

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_URL` HTTPS public
- [ ] Live Stripe/Bold keys; webhook endpoints registered
- [ ] `STRIPE_WEBHOOK_SECRET` set; Bold signing secret **not** empty string
- [ ] Smoke: one real small charge per provider + DB rows (`payments`, `payment_webhook_events.processed_at`, stock, order status)
- [ ] Runbook: D25, double approved, refund without stock, secret rotation
- [ ] Runbook coupons: global vs per-user limits, cancel pending releases, refund keeps redemption, stuck pending holds limit
