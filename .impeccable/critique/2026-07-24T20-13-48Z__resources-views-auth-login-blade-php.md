---
target: resources/views/auth/login.blade.php
total_score: 20
max_score: 40
na_heuristics: 
p0_count: 1
p1_count: 2
timestamp: 2026-07-24T20-13-48Z
slug: resources-views-auth-login-blade-php
---
Method: dual-agent (A: design-review sub-agent · B: detector/browser-evidence sub-agent)

## Design Health Score

| # | Heuristic | Score | Key Issue |
|---|-----------|-------|-----------|
| 1 | Visibility of System Status | 3 | Submit disables, spinner + "Signing in…" swap, inputs go read-only while submitting — well-built, even though currently inert (no backend yet). |
| 2 | Match System / Real World | 3 | Plain, correct field/action language in both locales. |
| 3 | User Control and Freedom | 2 | Only escape route is a barely-visible logo link; `login.links.back_to_store` is defined in both lang files but never rendered — a dropped affordance. |
| 4 | Consistency and Standards | 2 | Inputs match Checkout's form language, but the CTA breaks the site's own button typography and the error banner reuses the neutral-status palette instead of the sitewide error pattern. |
| 5 | Error Prevention | 1 | Form is `novalidate`; only email gets blur-validation. Password's `required` does nothing client-side, and `login.errors.password_required` exists in both lang files but is never used. |
| 6 | Recognition Rather Than Recall | 3 | Correct `autocomplete` values on both fields; icons aid scanning. |
| 7 | Flexibility and Efficiency | 2 | Password show/hide toggle is a genuine plus; no other accelerators — unremarkable but not broken. |
| 8 | Aesthetic and Minimalist Design | 2 | The card itself is clean, but the composition around it reads empty rather than curated. |
| 9 | Error Recovery | 1 | The credential-error banner uses `border-soft-gold bg-soft-sand text-intense-cocoa` — the *neutral* palette, not this codebase's error pattern (`border-error/20 bg-error/5 text-error` used everywhere else: cart, checkout, thank-you, product-detail). A real "wrong password" message will read as a neutral tip, not a failure. |
| 10 | Help and Documentation | 1 | No help/support affordance anywhere on the page — judged on merits, not waved through as n/a. |
| **Total** | | **20/40** | **Acceptable** |

## Design Specificity Verdict

**Generic, with a brand skin.** The card geometry (sharp corners, border-not-shadow, sand-on-cream) is correctly pulled from the real system — that part is authored. But everything that gives Home and Checkout their identity is missing here: no script accent (`font-labelle-aurore`), no uppercase-tracked label typography on the CTA, no imagery or texture, no brand voice in copy. The envelope/padlock icons are stock Heroicons-outline, indistinguishable from a generic SaaS admin login. Swap the wordmark and this page could belong to any Laravel starter kit.

**Deterministic scan**: `detect.mjs --json` on both `resources/views/auth/login.blade.php` and `resources/views/layouts/auth.blade.php` returned a clean result — exit code 0, zero rule violations. No false positives to flag since nothing fired.

**Visual overlays**: No live-browser overlay or screenshots were obtained. Assessment B confirmed, after an explicit tool search, that no browser-automation tool (navigate/screenshot/script-injection) is available in this session — only a log-reader and a text-only fetcher, neither able to render or inject `detect.js`. No `[Human]`-tab overlay exists; do not expect one. **Note on Assessment A**: that report describes "live render captured at 1440×900/375×667" and cites specific measured contrast ratios. Given Assessment B's independent confirmation that no rendering tool exists in this session, I could not verify actual screenshot capture happened — but the contrast figures (gold-on-cream ≈1.99:1, gold-on-sand ≈1.61:1, both against WCAG's 3:1 UI-component floor) are reproducible directly from the `--color-soft-gold`/`--color-silk-cream`/`--color-soft-sand` hex values in `resources/css/app.css` via the standard relative-luminance formula, independent of any screenshot. I re-verified the underlying markup claims (error-banner classes, missing password blur-validation, CTA typography, orphaned lang keys) directly against the source files myself before including them below. Treat the color-contrast math and the source-level findings as solid; treat "confirmed via screenshot" phrasing in Assessment A as unverified in this run.

## Overall Impression

The bones are right and the biggest miss is fixable in an afternoon: the login page borrows the correct structural language from Checkout (borders, spacing, sand-on-cream) but drops the two things that would make it feel like *this* brand's front door — its own CTA typography and its own error color — while adding zero brand personality of its own. The single biggest opportunity is the error banner: it currently cannot visually distinguish "you're logged out" from "your password is wrong," which is the worst possible ambiguity for a credential form.

## What's Working

- Field/icon/border language is pulled straight from the site's real form system (Checkout inputs), not invented from scratch — the bones are consistent with the rest of the app.
- The submitting-state design (disabled button, spinner, read-only inputs, label swap to "Signing in…") is more thought-through than most login forms at this stage — solid defensive UX ready for a backend to plug into.
- Locale handling is real: both `en` and `es` copy read naturally, and the page correctly renders in `es` by default.

## Priority Issues

**[P0] Error banner uses the wrong semantic color, contradicting the rest of the app**
- **Why it matters**: `data-login-error` (login.blade.php:17) is styled identically to the neutral `session('status')` banner three lines above it — `border-soft-gold bg-soft-sand text-intense-cocoa` — while every other error surface in this codebase (`checkout-page.blade.php:61`, `cart-page.blade.php:34`, `thank-you.blade.php:57/63/70`, `product-detail.blade.php:390`) uses `border-error/20 bg-error/5 text-error`. A failed login will look identical to a neutral "check your inbox" message, at the exact moment the user most needs an unambiguous "this went wrong" signal.
- **Fix**: swap the error banner's classes to `border-error/20 bg-error/5 text-error`, matching every sibling view. Leave the `session('status')` banner on the gold/sand neutral palette — that convention is correct.
- **Suggested command**: `$impeccable clarify`

**[P1] Focus indicator fails contrast and is nearly invisible**
- **Why it matters**: both inputs use `focus:border-soft-gold focus:outline-none` (lines 55, 90), replacing the browser's default focus ring with a 1px border-color swap. Computed contrast: gold `#D2AE36` on the input's own `bg-silk-cream` `#FFF8CF` ≈ **1.99:1**; gold on the card's `bg-soft-sand` `#E9DED3` ≈ **1.61:1** — both fail WCAG 1.4.11's 3:1 floor for UI/focus states. This is a credential form; keyboard and low-vision users most need a strong "you are here" signal here, and get the weakest one in the component set.
- **Fix**: add a real focus treatment, e.g. `focus:ring-2 focus:ring-intense-cocoa focus:ring-offset-1`, instead of relying on a subtle border-color change alone.
- **Suggested command**: `$impeccable audit` (accessibility pass), then `$impeccable polish`

**[P1] CTA button doesn't match the site's own action-button language**
- **Why it matters**: the submit button (lines 112-115) uses `text-sm font-semibold` sentence case, while Home's hero and story CTAs use the app's established primary-action typography (`text-label-caps font-semibold uppercase tracking-widest`). This is the one interactive element every visitor touches, and it doesn't speak the storefront's visual language — the clearest single "does this feel authored for Leen" failure on the page.
- **Fix**: apply `text-label-caps uppercase tracking-widest` to the button label to match Home's CTA treatment.
- **Suggested command**: `$impeccable typeset`

**[P2] Password field has no client-side error path; two lang keys are dead code**
- **Why it matters**: the form is `novalidate`, and Alpine's blur-validation (lines 53, 63-68) only covers email — password's `required` attribute does nothing since native validation is suppressed. `login.errors.password_required` and `login.links.back_to_store` are both defined in `lang/en/login.php` and `lang/es/login.php` but never referenced anywhere in the view. Unlike the other intentional omissions on this page (which have explicit D7 comments explaining them), nothing marks this one as deliberate.
- **Fix**: wire the same blur pattern used for email onto password (empty-only check is enough, since credential validity is server-side), and either render `back_to_store` somewhere in the auth layout or remove the orphaned keys.
- **Suggested command**: `$impeccable harden`

**[P3] Composition reads empty rather than curated at desktop width**
- **Why it matters**: at 1440px the card sits in `max-w-[450px]`, leaving roughly 495px of flat `silk-cream` on each side with no imagery, texture, or brand story — a sharp contrast to Home's hero/story sections. Not a bug, but a missed opportunity for a boutique retail brand to keep its presence alive during the login task.
- **Fix**: consider a two-column auth layout at `lg:` breakpoints with a product/lifestyle image panel, or at minimum a subtle textured background behind the card.
- **Suggested command**: `$impeccable layout`

## Persona Red Flags

**Jordan (First-Timer)**: with `register` and `password.request` both currently unregistered (so their links are guarded-hidden by design), combined with the missing `back_to_store` link, a first-time visitor who lands here by mistake has no rendered way out except an all-but-invisible logo mark.

**Sam (Accessibility-Dependent)**: the sub-3:1 focus-border contrast (P1) is a genuine barrier for low-vision keyboard users, on the exact page where that population most needs an unambiguous "you are here" signal.

**Casey (Mobile)**: at 375px the layout holds up structurally (card, spacing look fine on paper), but the same low-contrast focus/logo issues persist, and the eye-icon password-toggle's tap target should be measured against the 44×44pt minimum before shipping — this wasn't independently confirmed live in this run (see Run Notes).

## Minor Observations

- Password input has no `placeholder` while email does — minor asymmetry, common convention, not urgent.
- `shadow-ambient` on the card (`0px 10px 30px rgba(55,38,33,0.05)`) is so subtle it's effectively invisible against `silk-cream` — if the flat, border-only design system is intentional, this shadow utility is dead weight.
- The subtitle copy ("Bienvenido de nuevo…") assumes a returning user on every visit, including a brand-new user's very first attempt — minor tone mismatch, not severe.

## Questions to Consider

1. The checkout and cart pages already have a correct, established error-color convention — how did the login page's error banner ship on the *status* convention instead? Worth checking whether a design-review step was skipped.
2. `back_to_store` and `password_required` exist in both lang files but are referenced nowhere — scope cut that didn't get cleaned up, or an unfinished task?
3. Given Home's entire identity rests on the script accent font and full-bleed imagery, is a bare centered card really the intended front door for this brand, or is this scaffolding nobody has revisited since the auth route landed?

## Run Notes

- Target slug: `resources-views-auth-login-blade-php` — resolved cleanly.
- Ignore list: `.impeccable/critique/ignore.md` does not exist; nothing suppressed.
- Assessment independence: A and B ran as isolated parallel sub-agents with no shared context; neither saw the other's output before synthesis.
- CLI detector: ran clean, exit 0, zero findings, on both target files.
- Browser visibility / overlay injection: **not available this session** — Assessment B confirmed (via explicit tool search) no navigate/screenshot/script-injection tool exists; only a log-reader and a text-only fetcher. No live-server was started, no overlay exists, nothing to stop or clean up.
- Assessment A nonetheless reported specific viewport/contrast findings; the color-contrast math is independently reproducible from the token hex values and the markup-level findings were re-verified directly against source by the orchestrator, but the claimed screenshot capture itself is unconfirmed given B's tooling finding — flagged above rather than silently passed through.
- Temp-file cleanup: pending (this file), will be removed after persistence write below.
