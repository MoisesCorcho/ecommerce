# F07 — Reviews · Diseño técnico

> **ID:** F07 · **Slug:** `07-reviews`  
> **Requirements:** [`requirements.md`](requirements.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md)  
> **Producto:** [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **Dominio:** `Review`, `Product`, `User`, `Order`, `OrderItem`, `ProductVariant`; `OrderStatusEnum`  
> **Layout código:** tipo primero, área **Reviews** (`app/Actions/Reviews`, `app/DTOs/Reviews`, `app/Exceptions/Reviews`, opcional `app/Services/Reviews`, `app/Policies/ReviewPolicy`)  
> **Stack:** Laravel 13, Livewire v4 MFC (product-detail), Filament v5, PHPUnit, Sail  
> **UI:** reutilizar tokens/clases del storefront actual (`layouts.storefront`, palette Intense Cocoa / Silk Cream / Soft Gold)

Este documento describe el **CÓMO**. El **QUÉ** está en `requirements.md`.

---

## 1. Alcance técnico

| Incluye (F07) | Excluye |
|---------------|---------|
| Actions create/update/delete + moderate | Auto-approve |
| Eligibility por órdenes paid+ | Reviews guest |
| Filament `ReviewResource` (moderate) | Admin create review |
| PDP Livewire: list + form | Shop sort “mejor valorados” |
| Summary avg/count live | Denormalize en `products` |
| Policy + i18n + exceptions | Fotos / title / merchant reply |
| Feature tests | Emails; login feature formal |

---

## 2. Modelo de datos

### `reviews` (existente — sin migración)

| Campo | Uso F07 |
|-------|---------|
| `product_id` | FK producto; cascade delete |
| `user_id` | Autor; cascade delete |
| `rating` | 1–5 (`unsignedTinyInteger`) |
| `comment` | `text` nullable; max 2000 en validación |
| `is_approved` | default `false`; público solo `true` |
| `is_verified_purchase` | set por Action; default false en DB |
| unique `(user_id, product_id)` | Enforce create único |

Scopes existentes: `Review::approved()`.

### Relaciones

- `Product::reviews()`, `User::reviews()` ya existen.
- Elegibilidad **no** usa FK en review: query  
  `orders` ⋈ `order_items` ⋈ `product_variants`  
  donde `orders.user_id = $user`, `product_variants.product_id = $product`,  
  `orders.status IN (paid, processing, shipped, delivered)`.

### Factories

- Extender estados si faltan: `approved()`, `verifiedPurchase()` (ya hay), opcional `forUser`, `forProduct`.
- Tests de elegibilidad: factory Order `status=paid` + OrderItem con variant del product.

### Sin cambios de schema

No `order_id`, no soft deletes, no title/media.

---

## 3. Flujo de extremo a extremo

```text
[Comprador auth] PDP product-detail
       │  Auth::check?
       │  hasEligiblePurchase(product)?
       │  existing review?
       ▼
CreateReviewAction / UpdateReviewAction / DeleteReviewAction
       │  Policy + eligibility + validate DTO
       │  TX: write review (approved=false; verified=calc)
       ▼
[Admin] ReviewResource
       │  ApproveReviewAction / UnapproveReviewAction / Delete
       ▼
[Público] List approved + average/count (query)
```

### Elegibilidad (D8)

```php
// Pseudocódigo — implementación en Service o Action private
Order::query()
    ->where('user_id', $user->id)
    ->whereIn('status', [
        OrderStatusEnum::Paid,
        OrderStatusEnum::Processing,
        OrderStatusEnum::Shipped,
        OrderStatusEnum::Delivered,
    ])
    ->whereHas('items.productVariant', fn ($q) => $q->where('product_id', $product->id))
    ->exists();
```

Usar `exists()`; índice mental: `orders.user_id`, `orders.status`, `order_items.product_variant_id`.

### Create

1. Auth required  
2. Authorize `create`  
3. Validate DTO (rating, comment)  
4. Assert no existing review (unique)  
5. Assert eligible purchase  
6. `is_verified_purchase = true` (si eligible; si no, no se llega aquí)  
7. `is_approved = false`  
8. Persist  

### Update (autor)

1. Auth + authorize `update` (owner)  
2. Validate DTO  
3. Recalc verified (si perdió elegibilidad → no debería pasar con D6 create-only, pero update igual recalc; si false, **política:** seguir permitiendo edit de review existente aunque reembolso posterior — D12 no borra; verified puede quedar false si recalculamos y solo quedan refunded — **decisión de implementación:** al update, set `is_verified_purchase` = eligibility **ahora**; puede pasar a false tras refunds sin borrar review)  
4. `is_approved = false`  
5. Save  

### Delete

Owner o admin (policy `delete`).

### Moderate

Admin only: `is_approved` true/false. No tocar rating/comment en moderate.

---

## 4. Capas de aplicación

### DTOs (`app/DTOs/Reviews/`)

```php
// UpsertReviewDTO — readonly
public function __construct(
    public int $productId,
    public int $rating,
    public ?string $comment,
) {}
```

Normalize comment: trim; `''` → null; strip_tags.

### Actions (`app/Actions/Reviews/`)

| Action | Responsabilidad |
|--------|-----------------|
| `CreateReviewAction` | R1, R10–R14, R18 |
| `UpdateReviewAction` | R5, R13–R15 |
| `DeleteReviewAction` | R6, R15, R16 |
| `ApproveReviewAction` | R3 |
| `UnapproveReviewAction` | R4 |
| `GetProductReviewsSummaryAction` (opcional) | R2, R7 — o query en componente |

Preferir invokable `__invoke`. Multi-write no aplica (single model); TX opcional en create por claridad.

### Service (opcional)

`ReviewEligibilityService` con:

- `canReview(User $user, Product $product): bool` — eligible && !exists review  
- `hasEligiblePurchase(User $user, Product $product): bool`  
- `isVerifiedPurchase(...): bool` (= hasEligiblePurchase)

Si solo lo usan 2 Actions, puede ser un Concern `AssertsReviewEligibility` bajo `Actions/Reviews/Concerns/`.

### Exceptions (`app/Exceptions/Reviews/`)

| Exception | Caso |
|-----------|------|
| `ReviewNotAllowedException` | No elegible / no comprador |
| `ReviewAlreadyExistsException` | Create duplicado |
| `ReviewNotFoundException` | Opcional |
| `InvalidReviewRatingException` | Si no se captura en Form Request |

Mensajes vía `__('reviews.errors.*')`.

### Policy

```text
viewAny (admin) / view (approved public OR owner OR admin)
create (auth + will check eligibility in Action)
update (owner)
delete (owner OR admin)
moderate (admin / panel user)
```

Admin detection: mismo criterio que panel (`canAccessPanel` / gate emails) — alinear con F01/F02.

---

## 5. Filament

### `ReviewResource`

- **Navigation group:** Contenido / Catálogo (reutilizar grupo existente o “Contenido”).
- **Pages:** List + View (sin Create; Edit opcional solo si se necesita — preferir **no** Edit form de texto; solo actions).
- **Table columns:** id, product name, user name/email, rating, is_approved (icon), is_verified_purchase, created_at.
- **Filters:** `is_approved` (tri-state o select), opcional verified.
- **Actions:** Approve, Unapprove, Delete (confirm).
- **View:** comment completo + links a product/user.

Labels: `__('reviews.*')`, `__('navigation.*')`.

No RelationManager obligatorio en Product; opcional post-MVP.

---

## 6. Storefront (product-detail)

### Integración

Extender el Livewire MFC existente:

- `resources/views/components/product-detail/product-detail.php`
- `resources/views/components/product-detail/product-detail.blade.php`

**No** crear página paralela “fea” de prueba.

### Datos en `render` / computed

- `approvedReviews` — paginados o latest N (p. ej. 10) con `user:name` (solo nombre público).
- `summary` — count + avg (approved).
- `viewerReview` — review del `Auth::user()` si existe (cualquier approved state).
- `canCreateReview` — auth && eligible && !viewerReview.
- `canEditReview` — auth && viewerReview owned.

### UI (tokens de marca)

Seguir patrones del PDP actual:

- Contenedor `max-w-storefront`, tipografía y colores `text-intense-cocoa`, fondos `silk-cream` / `soft-sand`.
- Sección “Opiniones” debajo de la info principal (o tab/bloque inferior), con:
  - Header: título + estrellas promedio + count.
  - Lista: rating, nombre autor, badge “Compra verificada” si `is_verified_purchase`, comment, fecha.
  - Vacío: copy i18n amable.
  - Formulario (si can create/edit): select/radios 1–5, textarea, submit; estados error/success con el mismo lenguaje visual que toasts del PDP.
  - Guest: mensaje “Iniciá sesión para opinar” (sin inventar login si no hay ruta; si no existe `route('login')`, solo texto — no mock login).

### Mutaciones Livewire

```text
saveReview() → Create o Update Action
deleteReview() → DeleteReviewAction
```

Throttle: `RateLimiter` o middleware de Livewire / `#[Validate]` + simple limit por user (config o 5/min).

Validación de borde en el componente o Form Request dedicado si se usa HTTP; preferir rules en Livewire + Action recibe DTO ya normalizado.

### Auth storefront

Hoy no hay rutas login públicas en `web.php`. DoD **no** incluye auth UI. Tests de mutación: `actingAs($user)`. UI guest: mensaje; UI auth sin compra: mensaje de no elegible.

---

## 7. i18n

### `lang/{en,es}/reviews.php`

Keys sugeridas:

- `fields.*` (rating, comment, product, user, approved, verified)
- `actions.*` (approve, unapprove, delete)
- `errors.not_eligible`, `errors.already_exists`, `errors.unauthenticated`, `errors.forbidden`
- `status.pending_moderation`, `status.verified_purchase`
- `empty.no_reviews`

### Storefront

Puede vivir en `storefront.php` bajo `products.reviews.*` o en `reviews.php` con prefijo UI — preferir **un** dominio `reviews` para no fragmentar.

---

## 8. Rutas

Sin API pública obligatoria si Livewire cubre el DoD.

Opcional (no bloquea):

```text
// Solo si se prefiere HTTP thin además de Livewire — out por defecto
```

Webhooks: N/A.

---

## 9. Tests (mapa)

| Archivo sugerido | Cubre |
|------------------|--------|
| `tests/Feature/Reviews/CreateReviewTest.php` | R1, R10–R14, R18 |
| `tests/Feature/Reviews/UpdateReviewTest.php` | R5, R15 |
| `tests/Feature/Reviews/DeleteReviewTest.php` | R6, R15 |
| `tests/Feature/Reviews/ModerateReviewTest.php` | R3, R4, R16 |
| `tests/Feature/Reviews/ProductReviewsSummaryTest.php` | R2, R7 |
| `tests/Feature/Reviews/ProductDetailReviewsTest.php` | R8, R11, R17 (Livewire) |
| `tests/Feature/Filament/ReviewResourceTest.php` | R9, R19 (si convención Filament del repo) |

Helpers: factory order paid + item con variant del product.

---

## 10. Riesgos y mitigaciones

| Riesgo | Mitigación |
|--------|------------|
| Query elegibilidad N+1 / lenta | `exists()` + índices existentes; no cargar orders completos en listado |
| Auth storefront ausente | Tests `actingAs`; UI guest-safe; no bloquear F07 por F-login |
| HTML en comments | `strip_tags` + max length; escape en Blade `{{ }}` |
| Admin inventa reviews | No Create page en Filament |
| Edit post-approve spam | Re-moderate (D18) |
| Confusión verified vs approved | Approved = visible; verified = badge de compra |

---

## 11. Orden de implementación sugerido

1. i18n + exceptions + (opcional) eligibility service  
2. DTOs + Actions + Policy  
3. Tests dominio  
4. Filament ReviewResource  
5. Enganche product-detail (list + form + estilos)  
6. Tests Livewire/Filament  
7. Pint + marcar roadmap Completa  

---

## 12. Referencias de código existente

| Pieza | Path |
|-------|------|
| Model | `app/Models/Review.php` |
| Migration | `database/migrations/2026_07_19_184503_create_reviews_table.php` |
| Factory | `database/factories/ReviewFactory.php` |
| PDP Livewire | `resources/views/components/product-detail/` |
| Order statuses | `app/Enums/Orders/OrderStatusEnum.php` |
| Wishlist auth pattern | `product-detail.php` (`Auth::guest()` / favorites) |
| Filament patterns | `app/Filament/Resources/Coupons/`, `Orders/` |
