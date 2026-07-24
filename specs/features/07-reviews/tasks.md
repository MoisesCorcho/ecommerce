# F07 — Reviews · Tasks

> **ID:** F07 · **Slug:** `07-reviews`  
> **Requirements:** [`requirements.md`](requirements.md) · **Design:** [`design.md`](design.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md) · **Calidad:** [`02-feature-quality.md`](../../_global/02-feature-quality.md)

**Alcance DoD:** dominio Reviews + Policy + Filament moderación + enganche PDP (estilo marca) + i18n + tests.  
**Fuera de DoD:** guests, auto-approve, fotos/título/reply, denormalize rating, admin create, emails, shop sort, login feature formal.

**Estado de implementación:** Código implementado — tests pendientes de ejecución (Sail/Docker no disponible en el entorno).

---

## 1. Fundación i18n, errores y eligibility

- [x] 1.1 `lang/en/reviews.php` + `lang/es/reviews.php` (fields, actions, errors, status, empty). _(cubre R20)_
- [x] 1.2 Keys storefront de sección opiniones si se separan (`storefront` o `reviews` UI). _(cubre R8, R17)_
- [x] 1.3 Excepciones en `app/Exceptions/Reviews/` (`ReviewNotAllowed`, `ReviewAlreadyExists`, …). _(cubre R10, R12, R20)_
- [x] 1.4 `ReviewEligibilityService` **o** Concern `AssertsReviewEligibility` (paid/processing/shipped/delivered + variant→product). _(cubre R1, R10, R18, D8)_

## 2. Domain Actions + Policy + DTO

- [x] 2.1 `UpsertReviewDTO` (productId, rating, comment?) con normalización comment. _(cubre R13, R14, D20, D21)_
- [x] 2.2 `CreateReviewAction`: auth, eligible, unique, verified=true, approved=false. _(cubre R1, R10, R11, R12, R18)_
- [x] 2.3 `UpdateReviewAction`: owner, re-moderate approved=false, recalc verified. _(cubre R5, R15)_
- [x] 2.4 `DeleteReviewAction`: owner o admin. _(cubre R6, R15, R16)_
- [x] 2.5 `ApproveReviewAction` + `UnapproveReviewAction`. _(cubre R3, R4)_
- [x] 2.6 `ReviewPolicy` (+ register si aplica). _(cubre R11, R15, D25)_
- [x] 2.7 Summary query helper o `GetProductReviewsSummaryAction` (count + avg approved only). _(cubre R2, R7)_

## 3. Filament moderación

- [x] 3.1 `ReviewResource` list/view (sin Create). _(cubre R9, R19)_
- [x] 3.2 Table: product, user, rating, approved, verified, dates + filter `is_approved`. _(cubre R9)_
- [x] 3.3 Actions Approve / Unapprove / Delete → Actions de dominio. _(cubre R3, R4, R16)_
- [x] 3.4 Navigation group + labels i18n. _(cubre R20)_

## 4. Storefront PDP

- [x] 4.1 Cargar approved reviews + summary + viewerReview en `product-detail`. _(cubre R2, R7, R8, R16)_
- [x] 4.2 Bloque UI opiniones (lista, vacío, promedio) con tokens de marca del layout storefront. _(cubre R8, D2)_
- [x] 4.3 Form create/edit (rating + comment) + mensajes pending moderation; guest/no-elegible copy. _(cubre R1, R5, R8, R17, R10)_
- [x] 4.4 Mutaciones Livewire → Actions; throttle razonable. _(cubre R1, R5, R6, R11)_
- [x] 4.5 Delete propio desde PDP (si UX mínima lo incluye; si no, al menos Action testeada). _(cubre R6)_

## 5. Tests (PHPUnit)

- [x] 5.1 Create: comprador paid elegible OK; verified + not approved. _(cubre R1)_
- [x] 5.2 Create: sin compra / solo pending|cancelled|refunded → fail. _(cubre R10, R18)_
- [x] 5.3 Create: guest fail; duplicate fail; rating inválido; comment > 2000. _(cubre R11, R12, R13, R14)_
- [x] 5.4 Update: owner re-modera; foreign deny. _(cubre R5, R15)_
- [x] 5.5 Delete: owner OK; foreign deny. _(cubre R6, R15)_
- [x] 5.6 Approve / unapprove afectan listado y avg/count. _(cubre R2, R3, R4, R7)_
- [x] 5.7 Admin delete. _(cubre R16)_
- [x] 5.8 Livewire product-detail: ve approved; create como buyer; guest no crea. _(cubre R8, R11, R17)_
- [x] 5.9 Filament list + approve action (convención del repo). _(cubre R9, R3)_

## 6. Cierre de calidad

- [ ] 6.1 Tests del alcance F07 en verde vía Sail. *(bloqueado: Docker Desktop no disponible en el entorno de implementación)*
- [ ] 6.2 Pint en PHP tocado (`vendor/bin/sail bin pint --dirty --format agent`). *(pendiente de Sail)*
- [ ] 6.3 Estado F07 = **Completa** en requirements + roadmap al cerrar implementación. *(tras 6.1/6.2 en verde)*

---

## Mapa de trazabilidad (DoD F07)

| Criterio | Tareas | DoD F07 |
|----------|--------|---------|
| R1 | 2.2, 4.3, 4.4, 5.1, 5.8 | Sí |
| R2 | 2.7, 4.1, 5.6 | Sí |
| R3 | 2.5, 3.3, 5.6, 5.9 | Sí |
| R4 | 2.5, 3.3, 5.6 | Sí |
| R5 | 2.3, 4.3, 5.4 | Sí |
| R6 | 2.4, 4.5, 5.5 | Sí |
| R7 | 2.7, 4.1, 5.6 | Sí |
| R8 | 4.1–4.4, 5.8 | Sí |
| R9 | 3.1–3.3, 5.9 | Sí |
| R10 | 1.4, 2.2, 4.3, 5.2 | Sí |
| R11 | 2.2, 2.6, 4.4, 5.3, 5.8 | Sí |
| R12 | 2.2, 5.3 | Sí |
| R13 | 2.1, 5.3 | Sí |
| R14 | 2.1, 5.3 | Sí |
| R15 | 2.3, 2.4, 2.6, 5.4, 5.5 | Sí |
| R16 | 2.4, 3.3, 5.7 | Sí |
| R17 | 4.3, 5.8 | Sí |
| R18 | 1.4, 5.2 | Sí |
| R19 | 3.1, 5.9 | Sí |
| R20 | 1.1–1.3, 3.4 | Sí |

---

## Definition of Done (checklist tasks)

- [ ] Criterios **R1–R20** implementados y testeados.
- [x] Sin migración innecesaria; unique `(user_id, product_id)` respetado.
- [x] Actions/DTO/Policy/Exceptions en área **Reviews**.
- [x] Filament: moderación sin Create de marketing.
- [x] PDP: sección opiniones con estilo de marca del storefront existente.
- [x] Elegibilidad D8 (paid|processing|shipped|delivered).
- [x] Edit re-modera; público solo approved.
- [ ] PHPUnit del alcance en verde vía Sail; Pint OK.
- [ ] Specs + roadmap con estado **Completa** (al cerrar implementación).
