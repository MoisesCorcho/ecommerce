# F10 — Wishlist · Tasks

> **ID:** F10 · **Slug:** `10-wishlist`
> **Requirements:** [`requirements.md`](requirements.md) · **Design:** [`design.md`](design.md)
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md) · **Calidad:** [`02-feature-quality.md`](../../_global/02-feature-quality.md)

**Alcance DoD:** `ToggleWishlistAction` como único punto de escritura, refactor de `product-detail.php`, activación real de `favorite-button` (reemplaza su test actual), página `/wishlist` completa, nav link real, i18n, tests.
**Fuera de DoD:** wishlist para invitados, múltiples listas, compartir wishlist, notificaciones de precio/stock (todo diferido en requirements).

**Estado de implementación:** No iniciada.

Sin gate de dependencias (no hay paquetes composer nuevos) y sin fase de esquema (`wishlists` ya existe desde la fundación de dominio).

---

## 1. Domain: Action

- [ ] 1.1 `app/Actions/Wishlist/ToggleWishlistAction.php`: invocable, `__invoke(User $user, Product $product): bool` — `firstOrCreate`/`delete` sobre `Wishlist` scoped por `user_id`+`product_id`; retorna `true` si quedó guardado, `false` si se quitó. _(cubre R1, R2, R3, R13)_

## 2. Refactor: PDP usa la Action (D1)

- [ ] 2.1 `resources/views/components/product-detail/product-detail.php`: `toggleFavorite()` inyecta `ToggleWishlistAction` como parámetro del método (mismo patrón que `saveReview(CreateReviewAction $createReview, ...)`) y elimina el `Wishlist::create()`/`delete()` inline. _(cubre R1, R2, D1)_
- [ ] 2.2 `checkIsFavorited()` en el mismo archivo: reemplaza `Wishlist::where(...)` por `$product->wishlists()->where('user_id', Auth::id())->exists()`. _(prerequisito de R1, R2 — sin cambio de comportamiento)_

## 3. Activar `favorite-button` (D2 — cerrada, se activa en esta feature)

- [ ] 3.1 `resources/views/components/favorite-button/favorite-button.php`: agrega `public bool $isFavorited`, lo calcula en `mount()`, agrega método `toggle(ToggleWishlistAction $toggleWishlist)` que redirige a login si `Auth::guest()`, si no togglea vía la Action y despacha el toast. _(cubre R3, R9)_
- [ ] 3.2 `resources/views/components/favorite-button/favorite-button.blade.php`: agrega `wire:click="toggle"`, quita `disabled`/`aria-disabled="true"` estáticos para usuarios autenticados (guest conserva el look pero el click redirige a login, no queda inerte). _(cubre R3, R9)_

## 4. Página `/wishlist`

- [ ] 4.1 `resources/views/components/wishlist-page/` (Livewire MFC, `layouts.storefront`): carga `Auth::user()->wishlists()->with(['product' => ...])`, misma composición `publishedForStorefront`/`withPriceIn` que ya usa `product-detail.php`. _(cubre R4)_
- [ ] 4.2 Por producto: calcula `isAvailable` (¿sigue `publishedForStorefront`?) y usa `isOutOfStock()` (ya existe en `Product`) para los badges. _(cubre R10, R11)_
- [ ] 4.3 Acción "agregar al carrito" desde la página → reusa `AddCartItemAction` (F03, sin cambios), sin quitar el producto de la wishlist. _(cubre R5)_
- [ ] 4.4 Acción "quitar de la wishlist" desde la página → reusa `ToggleWishlistAction` (1.1), actualiza el listado sin recarga completa. _(cubre R6)_
- [ ] 4.5 Estado vacío: mensaje + enlace a `/products` (shop), según UI brief. _(cubre R7)_

## 5. Integración: ruta y navegación

- [ ] 5.1 `routes/web.php`: agregar `Route::livewire('/wishlist', 'wishlist-page')->name('wishlist')` dentro del grupo `Route::middleware('auth')` ya existente (mismo grupo que `/profile/*`). _(cubre R4, R9)_
- [ ] 5.2 `resources/views/layouts/storefront.blade.php:56`: reemplaza `href="#"` por `route('wishlist')`. **Coordinar con el dev de frontend que trabaja este archivo en paralelo antes de editar.** _(cubre R8)_

## 6. i18n

- [ ] 6.1 `lang/{en,es}/storefront.php`: agregar claves de estado vacío y "producto ya no disponible" para la página nueva; reusar `favorite_login_required`/`added_to_favorites`/`removed_from_favorites` ya existentes. Tono `es` neutro/tuteo, sin voseo. _(cubre R7, R10)_

## 7. Tests (PHPUnit)

- [ ] 7.1 `tests/Feature/Wishlist/ToggleWishlistActionTest.php`: guarda cuando no existe; quita cuando existe; respeta unicidad `[user_id, product_id]` sin duplicar filas. _(cubre R1, R2, R13)_
- [ ] 7.2 `tests/Feature/Storefront/ProductDetailFavoriteTest.php` (o extensión del test existente de PDP): toggle vía Action refleja estado en la vista; guest redirigido a login sin togglear. _(cubre R1, R2, R9)_
- [ ] 7.3 **Reemplazar** (no sumar) `tests/Feature/Storefront/FavoriteButtonTest.php`: los 5 tests actuales asumen `disabled`/`aria-disabled="true"`/sin `wire:click` — se sustituyen por: toggle guarda/quita según estado previo; guest redirigido a login; `data-product-id` se mantiene; label accesible se mantiene. _(cubre R3, R9)_
- [ ] 7.4 `tests/Feature/Storefront/WishlistPageTest.php`: listado solo del propio usuario; estado vacío; producto despublicado marcado "ya no disponible" con botón deshabilitado; producto agotado con badge; agregar al carrito no quita de la wishlist; quitar actualiza el listado; usuario no accede a wishlist ajena. _(cubre R4, R5, R6, R7, R10, R11, R12)_
- [ ] 7.5 Test de navegación (HTTP o Livewire smoke): el enlace del header lleva a `/wishlist`. _(cubre R8)_

## 8. Cierre de calidad

- [ ] 8.1 Tests del alcance F10 en verde vía Sail.
- [ ] 8.2 Pint en PHP tocado (`vendor/bin/sail bin pint --dirty --format agent`).
- [ ] 8.3 Estado F10 = **Completa** en `requirements.md` y roadmap al cerrar implementación.

---

## Mapa de trazabilidad

| Criterio | Tareas |
|----------|--------|
| R1 | 1.1, 2.1, 2.2, 7.2 |
| R2 | 1.1, 2.1, 2.2, 7.2 |
| R3 | 1.1, 3.1, 3.2, 7.3 |
| R4 | 4.1, 5.1, 7.4 |
| R5 | 4.3, 7.4 |
| R6 | 4.4, 7.4 |
| R7 | 4.5, 6.1, 7.4 |
| R8 | 5.2, 7.5 |
| R9 | 3.1, 3.2, 5.1, 7.2, 7.3 |
| R10 | 4.2, 6.1, 7.4 |
| R11 | 4.2, 7.4 |
| R12 | 7.4 |
| R13 | 1.1, 7.1 |

---

## Definition of Done (checklist tasks)

- [ ] Criterios **R1–R13** implementados y testeados.
- [ ] `ToggleWishlistAction` es el único punto de escritura de `Wishlist` — cero `Wishlist::create()`/`delete()` inline fuera de la Action (verificar 2.1, 2.2, 3.1).
- [ ] `FavoriteButtonTest` reemplazado, no ampliado — ya no asume estado deshabilitado (7.3).
- [ ] Ownership verificado en la página `/wishlist` (7.4).
- [ ] Coordinación confirmada con el dev de frontend antes de mergear el cambio de nav (5.2).
- [ ] `lang/{en,es}/storefront.php` completo para la copy nueva, tono tuteo/neutro (sin voseo).
- [ ] PHPUnit del alcance en verde vía Sail; Pint OK.
- [ ] Specs + roadmap con estado **Completa** al cerrar implementación.
