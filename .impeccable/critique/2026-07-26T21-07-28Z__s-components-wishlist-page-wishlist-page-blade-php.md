---
target: /wishlist page (resources/views/components/wishlist-page)
total_score: 20
max_score: 40
na_heuristics: 
p0_count: 0
p1_count: 2
timestamp: 2026-07-26T21-07-28Z
slug: s-components-wishlist-page-wishlist-page-blade-php
---
Method: dual-agent (A: general-purpose · B: general-purpose)

## Design Health Score

| # | Heuristic | Score | Key Issue |
|---|-----------|-------|-----------|
| 1 | Visibility of System Status | 2 | No `wire:loading` state on add-to-cart/remove during the round trip; EN removal toast read as a command, not a confirmation (now fixed) |
| 2 | Match System / Real World | 2 | Copy drifts between "Wishlist" (`<h1>`) and "Favorites" (breadcrumb, nav aria-label, toast) for the same concept |
| 3 | User Control and Freedom | 2 | `removeFromWishlist` is instant, no undo; the only trace is a 3s toast |
| 4 | Consistency and Standards | 1 | The gold-filled heart means "saved" everywhere else, but here it's the *remove* control and turns red on hover — repurposes the app's one learned icon-language |
| 5 | Error Prevention | 3 | Good: `@disabled(! $canAddToCart)` plus a server-side re-check in `addToCart()` |
| 6 | Recognition Rather Than Recall | 3 | Card shows name/color/size/price/image inline |
| 7 | Flexibility and Efficiency | 1 | No bulk select, no "add all to cart," no sort/filter |
| 8 | Aesthetic and Minimalist Design | 2 | Clean but under-designed vs. sibling `product-card` — reads stripped, not intentionally minimal |
| 9 | Error Recovery | 2 | The error banner sits at the top of the page, disconnected from which card in a multi-row grid triggered it |
| 10 | Help and Documentation | 2 | Only generic tooltips; the "no longer available" badge offers no explanation or next step |
| **Total** | | **20/40** | **Acceptable — significant improvements needed before users are happy** |

## Design Specificity Verdict

**LLM assessment**: The page is painted with the brand's tokens (silk-cream, soft-gold, intense-cocoa, Chillax) but the interaction design is generic-ecommerce-CRUD: no color swatch reuse (`ColorMap`, used one file over in `product-card.blade.php`, is dropped here for plain `Color: {{ $variant->color }}` text), no category label, no hover polish matching sibling cards, and the "remove" action repurposes the app's universal saved-heart icon into a hover-red delete button. Strip the CSS variables and this could be any store's "saved items" page.

**Deterministic scan**: `node .agents/skills/impeccable/scripts/detect.mjs --json` against `wishlist-page.blade.php` and `partials/toast.blade.php` → exit 0, **zero findings**. The mechanical detector has nothing to flag; every issue below is a judgment call the detector isn't built to catch (icon-language consistency, copy tone, missing loading states, information hierarchy vs. a sibling component).

**Visual overlays**: Not available this session — no browser automation/screenshot tool was exposed to either assessment. Both independently confirmed and reported this as a clean fallback signal (not a failure); Assessment A worked from source-code reasoning about the rendered Tailwind output instead.

## Overall Impression

Functionally solid (ownership scoping is airtight, empty state is genuinely well-crafted) but visually and interaction-wise it's the least-finished page in the storefront — it doesn't match the polish of `product-card`, and it breaks the one icon convention (heart = saved) the rest of the app already taught the user. The single biggest opportunity: stop reusing the heart as a delete button, and bring the card grammar up to the same bar as the product grid it's built from.

## What's Working

1. **Ownership scoping is airtight** — every mutation goes through `findOwnWishlistVariant()`, which scopes lookups to `Auth::user()->wishlists()`; a foreign variant id silently resolves to null rather than leaking cross-account state.
2. **Empty state is genuinely well-crafted** — icon + warm copy + CTA, not a bare "no items" message.
3. **Toast mechanism is consistently reused** from `x-partials.toast` — same position, color, and transition as the rest of the storefront.

## Priority Issues

**[P1] Broken EN toast copy for add/remove favorites**
- Why it matters: the toast was dispatching imperative text ("Add to favorites"/"Remove from favorites") as a *confirmation* after the action already happened — English users were told to do the thing they'd just finished doing, on every single interaction. Coincidentally matched the button's own imperative label text, which had also silently broken 5 test assertions that depended on the collision.
- Fix: already applied this session — `lang/en/storefront.php` now reads "Added to favorites" / "Removed from favorites"; the 5 affected tests were corrected to assert the actual imperative label instead of the toast text they were accidentally matching.
- Status: **Fixed** during this critique.

**[P1] Heart icon repurposed as an unlabeled destructive control, no undo**
- Why it matters: breaks the one icon-language users have already learned (gold-filled heart = "this is saved") and turns it into a silent, irreversible delete sitting 8px from "Add to cart" — a mis-tap on mobile permanently removes the item with only a 3s toast as evidence.
- Fix: swap the remove control to a trash/X icon, and add a clickable "Undo" action inside the toast.
- Suggested command: `$impeccable clarify`

**[P2] Card grammar diverges from `product-card.blade.php`**
- Why it matters: padding, type scale, and the color swatch are all inconsistent with the product grid this page is built from — and this is the one page where the saved color truly matters, yet it's the only card that drops the swatch for plain text.
- Fix: align padding/type scale with `product-card`, add the `ColorMap` swatch next to the color label.
- Suggested command: `$impeccable adapt`

**[P2] No truncation on product name, no pagination**
- Why it matters: a long name desyncs card heights in the grid; a large wishlist becomes one long scroll with no filter/sort.
- Fix: `line-clamp-2` on the name; paginate or add an "in stock only" filter past a threshold.
- Suggested command: `$impeccable layout`

**[P3] Unavailable/out-of-stock state has no empathetic guidance**
- Why it matters: this is the page's natural emotional low point (a saved piece is gone) and it's treated identically to a routine "sold out" badge, missing a brand-appropriate reassurance moment.
- Fix: add a short line under unavailable items ("This piece has been retired — explore similar styles") linking to related products.
- Suggested command: `$impeccable delight`

## Persona Red Flags

**Casey (distracted mobile)**: "Add to cart" and the 40×40 remove button sit only 8px apart on a 2-column mobile grid — narrow thumb margin, and a mis-tap on remove has zero confirmation or undo.

**Riley (stress tester)**: Product name has no truncation, breaking row-height alignment for long names. "No longer available" and "Out of stock" badges look nearly identical at thumbnail size despite meaning permanently-gone vs. temporarily-unavailable. Neither button has a `wire:loading` disabled state, so a rapid double-click on a slow connection can double-fire the action before the DOM updates.

**Jordan (first-timer)**: The page is called "Wishlist" in the `<h1>` but "Favorites" everywhere else (nav, breadcrumb, toast) — three names for one concept seeds doubt. The remove icon is a heart, which reads as "save" everywhere else in the app, not "delete."

## Minor Observations

- The unavailable/out-of-stock overlay only dims the image; price and variant text below stay fully undimmed, so the "disabled" feel is inconsistent within one card.
- `data-wishlist-*` attributes are thorough and test-friendly — good practice.

## Questions to Consider

1. If the heart means "save this" everywhere else, what would change if remove used a trash icon instead, freeing the heart to stay consistent app-wide?
2. Given `AddCartItemAction` already has server-side guards, is single-item-at-a-time really the intended experience for a "come back and buy later" page, or does bulk-add belong here?
3. Is "Wishlist" vs "Favorites" a deliberate two-name strategy, or copy drift that should collapse to one term across nav, breadcrumb, title, and toasts?
