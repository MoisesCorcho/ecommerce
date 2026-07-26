# F10 — Wishlist · Tasks

> **ID:** F10 · **Slug:** `10-wishlist`
> **Requirements:** [`requirements.md`](requirements.md) · **Design:** [`design.md`](design.md)
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md) · **Calidad:** [`02-feature-quality.md`](../../_global/02-feature-quality.md)

**Alcance DoD:** `ToggleWishlistAction` como único punto de escritura, refactor de `product-detail.php`, activación real de `favorite-button` (reemplaza su test actual), página `/wishlist` completa, nav link real, i18n, tests.
**Fuera de DoD:** wishlist para invitados, múltiples listas, compartir wishlist, notificaciones de precio/stock (todo diferido en requirements).

**Estado de implementación:** Completa — reapertura por D6 (wishlist por variante) cerrada. Todas las secciones (1-10) completas y en verde.

Sin gate de dependencias (no hay paquetes composer nuevos) y sin fase de esquema (`wishlists` ya existe desde la fundación de dominio).

---

## 1. Domain: Action

- [x] 1.1 `app/Actions/Wishlist/ToggleWishlistAction.php`: invocable, `__invoke(User $user, Product $product): bool` — `firstOrCreate`/`delete` sobre `Wishlist` scoped por `user_id`+`product_id`; retorna `true` si quedó guardado, `false` si se quitó. _(cubre R1, R2, R3, R13)_

## 2. Refactor: PDP usa la Action (D1)

- [x] 2.1 `resources/views/components/product-detail/product-detail.php`: `toggleFavorite()` inyecta `ToggleWishlistAction` como parámetro del método (mismo patrón que `saveReview(CreateReviewAction $createReview, ...)`) y elimina el `Wishlist::create()`/`delete()` inline. _(cubre R1, R2, D1)_
- [x] 2.2 `checkIsFavorited()` en el mismo archivo: reemplaza `Wishlist::where(...)` por `$product->wishlists()->where('user_id', Auth::id())->exists()`. _(prerequisito de R1, R2 — sin cambio de comportamiento)_

## 3. Activar `favorite-button` (D2 — cerrada, se activa en esta feature)

- [x] 3.1 `resources/views/components/favorite-button/favorite-button.php`: agrega `public bool $isFavorited`, lo calcula en `mount()`, agrega método `toggle(ToggleWishlistAction $toggleWishlist)` que redirige a login si `Auth::guest()`, si no togglea vía la Action y despacha el toast. _(cubre R3, R9)_
- [x] 3.2 `resources/views/components/favorite-button/favorite-button.blade.php`: agrega `wire:click="toggle"`, quita `disabled`/`aria-disabled="true"` estáticos para usuarios autenticados (guest conserva el look pero el click redirige a login, no queda inerte). _(cubre R3, R9)_
- [x] 3.3 `resources/views/components/product-card/product-card.blade.php`: reemplaza el corazón estático muerto por `<livewire:favorite-button :product-id="$product->id" wire:key="favorite-{{ $product->id }}" />` (D-A6). _(cubre R3)_

## 4. Página `/wishlist`

- [x] 4.1 `resources/views/components/wishlist-page/` (Livewire MFC, `layouts.storefront`): carga `Auth::user()->wishlists()->with(['product' => ...])`, misma composición `publishedForStorefront`/`withPriceIn` que ya usa `product-detail.php`. _(cubre R4)_
- [x] 4.2 Por producto: calcula `isAvailable` (¿sigue `publishedForStorefront`?) y usa `isOutOfStock()` (ya existe en `Product`) para los badges. _(cubre R10, R11)_
- [x] 4.3 Acción "agregar al carrito" desde la página → reusa `AddCartItemAction` (F03, sin cambios), sin quitar el producto de la wishlist. _(cubre R5)_
- [x] 4.4 Acción "quitar de la wishlist" desde la página → reusa `ToggleWishlistAction` (1.1), actualiza el listado sin recarga completa. _(cubre R6)_
- [x] 4.5 Estado vacío: mensaje + enlace a `/products` (shop), según UI brief. _(cubre R7)_

## 5. Integración: ruta y navegación

- [x] 5.1 `routes/web.php`: agregar `Route::livewire('/wishlist', 'wishlist-page')->name('wishlist')` dentro del grupo `Route::middleware('auth')` ya existente (mismo grupo que `/profile/*`). _(cubre R4, R9)_
- [x] 5.2 `resources/views/layouts/storefront.blade.php:56`: reemplaza `href="#"` por `route('wishlist')`. **Coordinar con el dev de frontend que trabaja este archivo en paralelo antes de editar.** _(cubre R8)_

## 6. i18n

- [x] 6.1 `lang/{en,es}/storefront.php`: agregar claves de estado vacío y "producto ya no disponible" para la página nueva; reusar `favorite_login_required`/`added_to_favorites`/`removed_from_favorites` ya existentes. Tono `es` neutro/tuteo, sin voseo. _(cubre R7, R10)_

## 7. Tests (PHPUnit)

- [x] 7.1 `tests/Feature/Wishlist/ToggleWishlistActionTest.php`: guarda cuando no existe; quita cuando existe; respeta unicidad `[user_id, product_id]` sin duplicar filas. _(cubre R1, R2, R13)_
- [x] 7.2 `tests/Feature/Storefront/ProductDetailFavoriteTest.php` (o extensión del test existente de PDP): toggle vía Action refleja estado en la vista; guest redirigido a login sin togglear. _(cubre R1, R2, R9)_
- [x] 7.3 **Reemplazar** (no sumar) `tests/Feature/Storefront/FavoriteButtonTest.php`: los 5 tests actuales asumen `disabled`/`aria-disabled="true"`/sin `wire:click` — se sustituyen por: toggle guarda/quita según estado previo; guest redirigido a login; `data-product-id` se mantiene; label accesible se mantiene. _(cubre R3, R9)_
- [x] 7.4 `tests/Feature/Storefront/WishlistPageTest.php`: listado solo del propio usuario; estado vacío; producto despublicado marcado "ya no disponible" con botón deshabilitado; producto agotado con badge; agregar al carrito no quita de la wishlist; quitar actualiza el listado; usuario no accede a wishlist ajena. _(cubre R4, R5, R6, R7, R10, R11, R12)_
- [x] 7.5 Test de navegación (HTTP o Livewire smoke): el enlace del header lleva a `/wishlist`. _(cubre R8)_

## 8. Cierre de calidad

- [x] 8.1 Tests del alcance F10 en verde vía Sail.
- [x] 8.2 Pint en PHP tocado (`vendor/bin/sail bin pint --dirty --format agent`).
- [x] 8.3 Estado F10 = **Completa** en `requirements.md` y roadmap al cerrar implementación.

---

## Mapa de trazabilidad (histórico, pre-D6 — superado por el mapa al final de este archivo)

| Criterio | Tareas |
|----------|--------|
| R1 | 1.1, 2.1, 2.2, 7.2 |
| R2 | 1.1, 2.1, 2.2, 7.2 |
| R3 | 1.1, 3.1, 3.2, 3.3, 7.3 |
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

## 9. Correcciones post-revisión visual

> Defectos detectados por el usuario probando la feature en navegador (no son nuevos R-ids, son correcciones de implementación sobre tareas ya cerradas).

- [x] 9.1 Reutilización incorrecta de claves i18n: los botones de favoritos (`favorite-button.blade.php`, `product-detail.blade.php`) mostraban el texto de confirmación en pasado del toast (`added_to_favorites`/`removed_from_favorites`) como etiqueta persistente del botón. Se agregaron claves nuevas en modo imperativo (`products.add_to_favorites_label`/`products.remove_from_favorites_label`) en `lang/{en,es}/storefront.php` y se actualizaron los 3 call sites de etiqueta/aria-label; los `dispatch('toast', ...)` que sí usaban las claves en pasado quedaron intactos.
- [x] 9.2 `favorite-button.blade.php` (embebido en `product-card.blade.php`) perdía el chroma circular (`h-10 w-10 bg-soft-sand shadow-sm`) que sí tiene el botón de carrito hermano, quedando un ícono flotante sin fondo. Se igualó el chroma manteniendo el color activo (`text-soft-gold`) y el estado deshabilitado de invitado.
- [x] 9.3 `/wishlist` despachaba `toast` en `addToCart`/`removeFromWishlist` pero la vista no tenía listener/renderer — no se veía ninguna confirmación. Se extrajo el bloque Alpine de toast (duplicado en `product-detail.blade.php` y `catalog-list.blade.php`) a `resources/views/components/partials/toast.blade.php` (`<x-partials.toast>`, sin props, escucha `toast` en `window`) y se envolvió el contenido de `wishlist-page.blade.php` con él. No se tocaron las otras dos vistas.
- [x] 9.4 `favorite-button.blade.php` tenía `hover:text-soft-gold` fijo (sin condicionar al estado) junto a `hover:bg-soft-gold` — en hover, el corazón favorito (ya dorado por `$isFavorited`) se volvía invisible contra el fondo también dorado. Se reemplazó por `hover:text-intense-cocoa`, igualando el patrón que ya usa su botón hermano de agregar al carrito en `product-card.blade.php` (fondo dorado en hover, ícono cocoa para mantener contraste).

## 10. D6 — Wishlist por variante (schema + Action + componentes)

> Reabre R1-R5, R10, R11, R13. Decisión y contratos completos en `design.md` D-A1, D-A3 a D-A7. No son bugs — es una revisión de alcance confirmada con el usuario.

- [x] 10.1 Migración nueva: `wishlists` gana `product_variant_id` (FK, `cascadeOnDelete`, NOT NULL tras backfill), pierde `product_id`; unique pasa a `[user_id, product_variant_id]`. Verificar con `database-query` que no hay filas de usuarios reales antes de truncar (D-A7). _(cubre R13)_
- [x] 10.2 `app/Models/Wishlist.php`: `#[Fillable(['user_id', 'product_variant_id'])]`; `product()` → `productVariant(): BelongsTo<ProductVariant>`.
- [x] 10.3 `app/Models/Product.php`: elimina `wishlists(): HasMany` (ya no hay FK directa).
- [x] 10.4 `app/Models/ProductVariant.php`: agrega `wishlists(): HasMany` y `isOutOfStock(): bool` (`! $this->product->is_preorder && $this->stock <= 0`).
- [x] 10.5 `app/Actions/Wishlist/ToggleWishlistAction.php`: firma pasa a `__invoke(User $user, ProductVariant $variant): bool`, scoped por `product_variant_id`. _(cubre R1, R2, R3, R13)_
- [x] 10.6 `product-detail.php`: `toggleFavorite()` resuelve `ProductVariant` desde `$this->selectedVariantId` (no el producto); `checkIsFavorited()` usa `$variant->wishlists()->where('user_id', Auth::id())->exists()`. _(cubre R1, R2)_
- [x] 10.7 `favorite-button.php`/`.blade.php`: prop `productVariantId` (antes `productId`); `mount()`/`toggle()` operan sobre `ProductVariant`; atributo `data-product-variant-id`. _(cubre R3, R9)_
- [x] 10.8 `product-card.blade.php`: `<livewire:favorite-button :product-variant-id="$variant->id" wire:key="favorite-{{ $variant->id }}" />`, mismo `$variant` que ya resuelve `product-card.php::with()` para el botón de agregar al carrito (D-A6 revisado). _(cubre R3)_
- [x] 10.9 `wishlist-page.php`: query carga `Auth::user()->wishlists()->with(['productVariant...'])->pluck('productVariant')` (una fila = una variante); `isAvailable`/`isOutOfStock` a nivel de variante; `addToCart(int $variantId)`/`removeFromWishlist(int $variantId)` usan el id exacto recibido, sin `->variants->first()`. _(cubre R4, R5, R10, R11 — cierra también el bug de "agrega variante arbitraria al carrito")_
- [x] 10.10 `wishlist-page.blade.php`: una card por variante; imagen específica de la variante si existe (`ProductImage` vinculada a `product_variant_id`), si no, `primaryImage()` del producto; color/talla visibles. _(cubre R4)_
- [x] 10.11 Tests — actualizar (no reemplazar de más, ajustar el sujeto de Product→ProductVariant): `ToggleWishlistActionTest`, `ProductDetailFavoriteTest` (agrega caso: cambiar de variante cambia el estado del corazón), `FavoriteButtonTest`, `ProductCardTest`, `WishlistPageTest` (agrega caso: dos variantes del mismo producto guardadas = dos entradas; add-to-cart agrega la variante exacta guardada).
- [x] 10.12 Cierre: tests del alcance F10 completo en verde vía Sail; Pint; `requirements.md` DoD actualizado y **Estado: Completa**; `tasks.md` este archivo con estado de implementación **Completa**.

---

## Mapa de trazabilidad (R1-R13, tras D6)

| Criterio | Tareas |
|----------|--------|
| R1 | 10.5, 10.6, 7.2 (revisar) |
| R2 | 10.5, 10.6, 7.2 (revisar) |
| R3 | 10.5, 10.7, 10.8, 7.3 (revisar) |
| R4 | 10.9, 10.10, 5.1, 7.4 (revisar) |
| R5 | 10.9, 7.4 (revisar) |
| R6 | 4.4 (sin cambio) |
| R7 | 4.5, 6.1 (sin cambio) |
| R8 | 5.2, 7.5 (sin cambio) |
| R9 | 10.6, 10.7, 5.1, 7.2, 7.3 (revisar) |
| R10 | 10.9, 6.1, 7.4 (revisar) |
| R11 | 10.4, 10.9, 7.4 (revisar) |
| R12 | 7.4 (sin cambio) |
| R13 | 10.1, 10.5, 7.1 (revisar) |

## 11. Mejoras post-crítica impeccable

> Batch de pulido tras una corrida del skill Impeccable sobre `/wishlist`. No introduce R-ids nuevos — es implementación de mejoras UX/visuales sobre tareas ya cerradas.

- [x] 11.1 [P1] Ícono del botón "quitar" reemplazado (corazón relleno → papelera outline, Heroicons "trash") en `wishlist-page.blade.php`, sin tocar el resto del chrome del botón.
- [x] 11.2 [P1] Toast con "Deshacer": `toast.blade.php` extendido con contrato genérico `undoEvent`/`undoPayload` (nueva clave i18n de nivel superior `storefront.undo`); `removeFromWishlist()` despacha el payload de undo; nuevo método `restoreWishlistVariant()` en `wishlist-page.php`, idempotente ante doble invocación (verifica existencia antes de togglear) y ownership-safe (scoped por `Auth::user()->wishlists()`).
- [x] 11.3 [P2] Gramática de card alineada con `product-card.blade.php`: padding `px-6 pb-6 pt-4`, tipografía `text-xl`/`text-2xl`; swatch de color (`ColorMap::HEX`) junto a la etiqueta de color.
- [x] 11.4 [P2] `line-clamp-2` en el nombre del producto; paginación real en `loadWishlistItems()` (12 por página, patrón `WithPagination` de `catalog-list.php`, `->through()` para mapear cada fila); badge de conteo usa `$items->total()`.
- [x] 11.5 [P3] Mensajes empáticos: nuevas claves `wishlist.unavailable_message` (+ enlace `explore_similar` a `/products`) y `wishlist.out_of_stock_message` en `lang/{en,es}/storefront.php`, renderizados en la card según el estado de disponibilidad/stock.
- [x] 11.6 Verificado: naming "favoritos"/"favorites" consistente en `lang/{en,es}/storefront.php` (grupo `wishlist`), sin literales "Wishlist"/"lista de deseados" residuales en las vistas tocadas (identificadores de código, rutas y atributos `data-wishlist-*` quedan sin cambios, fuera de alcance).
- [x] 11.7 Tests: 7 tests nuevos en `WishlistPageTest.php` (undo dispatch + restore, doble-restore idempotente, restore ownership-safe, ícono de papelera, paginación con total real, mensajes de disponibilidad/stock, swatch de color). Suite completa 387/387 verde vía Sail, Pint limpio.

## Definition of Done (checklist tasks)

- [x] Criterios **R1–R13** implementados y testeados a nivel de variante (D6).
- [x] `ToggleWishlistAction` es el único punto de escritura de `Wishlist` — cero `Wishlist::create()`/`delete()` inline fuera de la Action.
- [x] `FavoriteButtonTest` reemplazado, no ampliado — ya no asume estado deshabilitado.
- [x] Ownership verificado en la página `/wishlist`.
- [x] Coordinación confirmada con el dev de frontend antes de mergear el cambio de nav.
- [x] `lang/{en,es}/storefront.php` completo para la copy nueva, tono tuteo/neutro (sin voseo).
- [x] `wishlists.product_variant_id` migrado, sin `product_id` residual, unique `[user_id, product_variant_id]` (D-A7).
- [x] PHPUnit del alcance en verde vía Sail tras D6; Pint OK.
- [x] Specs + roadmap con estado **Completa** al cerrar la implementación de D6.
