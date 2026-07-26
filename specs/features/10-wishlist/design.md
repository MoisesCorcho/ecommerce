# Design: F10 — Wishlist

> Referencia: criterios R1-R13 y decisiones D1-D5 en [`requirements.md`](./requirements.md). Este documento cubre el CÓMO; no repite el QUÉ.

## Enfoque técnico

**Revisión D6 (post-implementación, tras revisión visual en navegador)**: la primera versión de este documento asumía "sin migraciones" porque la wishlist era por producto. D6 en `requirements.md` revierte esa premisa — la wishlist ahora guarda la variante específica (`product_variant_id`), no el producto. Esto sí requiere una migración (D-A7) y toca la Action, los 3 componentes Livewire, y el listado. El resto del enfoque original se mantiene: (1) `ToggleWishlistAction` sigue siendo el único punto de escritura, (2) `favorite-button` sigue activándose igual, solo que ahora opera sobre una variante en vez de un producto, y (3) `/wishlist` sigue usando el patrón `Route::livewire` + MFC de F08/F09, reusando `withPriceIn`/`priceIn` ya existentes en `ProductVariant`.

## Decisiones de arquitectura

### D-A1 — `ToggleWishlistAction` es el único punto de escritura (D1) — _firma revisada por D6_

**Elección**: `app/Actions/Wishlist/ToggleWishlistAction.php`, invocable, firma `__invoke(User $user, ProductVariant $variant): bool` (retorna `true` si quedó guardado, `false` si se quitó). Antes de D6 tomaba `Product $product`; ahora toma `ProductVariant $variant` — el `product_id` que antes se guardaba se deriva de `$variant->product_id` cuando haga falta (no vive más en la tabla `wishlists`, ver D-A7).
**Alternativas consideradas**: dos Actions separadas (`AddToWishlistAction`/`RemoveFromWishlistAction`) — se descarta por lo mismo que en la versión original (siempre es "togglear"). Mantener `product_id` además de `product_variant_id` en la tabla como columna redundante — se descarta: no hay ninguna consulta real que necesite `product_id` sin pasar por la variante (ver D-A7), y una columna redundante es una fuente de inconsistencia sin beneficio.
**Racional**: mismo patrón `firstOrCreate`+`delete`, ahora scoped por `product_variant_id`. Sigue sin DTO — 2 argumentos de objeto (User, ProductVariant) en la misma capa.

### D-A2 — Ownership vive en la firma de la Action, no en un Policy nuevo

**Elección**: la Action recibe `User $user` explícito (siempre `Auth::user()` desde el caller) — no hay "wishlist de otro user" que autorizar porque la Action nunca toma un `user_id` externo ni un `Wishlist $wishlist` ajeno.
**Alternativas consideradas**: `WishlistPolicy` estilo `AddressPolicy` (F09) — se descarta: F09 necesitaba Policy porque el comprador accede a un `Address $address` existente por id (posible IDOR). Acá no hay ruta que reciba un `Wishlist $wishlist` por id; el listado (`/wishlist`) siempre filtra por `Auth::user()->wishlists()`, y el toggle siempre opera sobre `(user, product)` del contexto autenticado. R12 (no acceder a wishlist ajena) se cumple por construcción de la query, no por autorización explícita.
**Racional**: evita una Policy que no protege ningún IDOR real — coherente con "no crear interfaces/policies por las dudas" de project-conventions.

### D-A3 — Refactor de `product-detail.php` — _revisado por D6: opera sobre la variante seleccionada, no el producto_

**Elección**: `toggleFavorite()` ya no resuelve el producto publicado — resuelve la **variante actualmente seleccionada** (`$this->selectedVariantId`, ya trackeado por el componente para el flujo de carrito) y la pasa a la Action:
```php
public function toggleFavorite(ToggleWishlistAction $toggleWishlist): void
{
    if (Auth::guest()) { $this->redirect(route('login')); return; }

    $variant = ProductVariant::query()->active()->findOrFail($this->selectedVariantId);
    $saved = $toggleWishlist(Auth::user(), $variant);

    $this->dispatch('toast', message: $saved
        ? __('storefront.products.added_to_favorites')
        : __('storefront.products.removed_from_favorites'));
}
```
`checkIsFavorited()` deja de consultar `$product->wishlists()` (eso respondía "¿alguna variante de este producto está guardada?", ya no es la pregunta correcta) y pasa a `ProductVariant::wishlists()` (relación nueva, ver D-A7) scoped a la variante seleccionada: `$variant->wishlists()->where('user_id', Auth::id())->exists()`. Consecuencia visible: el corazón de la PDP ahora puede cambiar de estado al cambiar de color/talla, reflejando que esa variante puntual está o no guardada — es el comportamiento esperado de D6, no un efecto secundario.
**Racional**: cero cambios a reviews/cart del mismo archivo — la Action sigue inyectada como parámetro del método (mismo patrón que `saveReview`).

### D-A4 — Activar `favorite-button` (D2 cerrada) — _prop revisado por D6: recibe una variante, no un producto_

**Elección**: el componente pasa de estático a Livewire real, recibiendo `productVariantId` en vez de `productId`:
```php
// favorite-button.php
public bool $isFavorited = false;

public function mount(int $productVariantId): void
{
    $this->productVariantId = $productVariantId;
    $this->isFavorited = Auth::check()
        && Wishlist::where('user_id', Auth::id())->where('product_variant_id', $productVariantId)->exists();
}

public function toggle(ToggleWishlistAction $toggleWishlist): void
{
    if (Auth::guest()) { $this->redirect(route('login')); return; }

    $this->isFavorited = $toggleWishlist(Auth::user(), ProductVariant::findOrFail($this->productVariantId));
    $this->dispatch('toast', message: $this->isFavorited
        ? __('storefront.products.added_to_favorites')
        : __('storefront.products.removed_from_favorites'));
}
```
El atributo `data-product-id` en el blade (usado por `FavoriteButtonTest`) pasa a `data-product-variant-id` — actualizar el test junto con el componente, no es un cambio de contrato público hacia afuera (nada más lo consume).
El blade mantiene `wire:click="toggle"` y el chrome circular agregado en la corrección post-revisión visual (batch 3: `flex h-10 w-10 items-center justify-center bg-soft-sand shadow-sm`) — D6 no toca estilos, solo el prop de identidad.
**`FavoriteButtonTest` — impacto adicional por D6**: los tests ya actualizados en batches anteriores deben re-parametrizarse para construir con una `ProductVariant` (vía factory) en vez de un `Product`, y verificar unicidad a nivel de variante — no reemplaza el comportamiento ya probado (toggle, guest-redirect, label), solo el sujeto sobre el que opera.

### D-A6 — `product-card.blade.php` embebe `favorite-button` (cierra hueco encontrado en exploración) — _prop revisado por D6_

**Hallazgo**: `product-card.blade.php` (usado en el listado/shop, fuente de R3) nunca incluye `<livewire:favorite-button>` — tiene su propio corazón estático, sin `wire:click`, 100% muerto, no cubierto por la tabla de archivos original de este documento.
**Elección**: `product-card.blade.php` reemplaza ese botón muerto por `<livewire:favorite-button :product-variant-id="$variant->id" wire:key="favorite-{{ $variant->id }}" />`, usando el `$variant` que `product-card.php::with()` ya resuelve (`$this->product->variants->first()`) — es la MISMA variante que ya usa el botón de agregar al carrito de la card (línea `wire:click="$dispatch('add-to-cart', { variantId: {{ $variant->id }} })"`), así que favoritear y agregar al carrito desde la card apuntan siempre a la misma variante por defecto (D6). `wire:key` obligatorio por estar dentro de un `@foreach`/grid.
**Alternativas consideradas**: exponer un método de toggle en el componente padre del grid — se descarta por lo mismo que en la versión original de esta decisión (violaría D-A1). Dejar que la card muestre un mini-selector de color antes de favoritear — se descarta explícitamente por requirements.md ("No incluye": selector interactivo en la card queda fuera de F10/D6, la card sigue sin selector).
**Racional**: una sola fuente de verdad para el estado y el toggle de favoritos (D-A1), y consistencia entre "agregar al carrito" y "agregar a favoritos" desde la card — ambos actúan sobre la misma variante resuelta una sola vez en `with()`.

### D-A5 — Página `/wishlist`: una entrada por variante — _revisado por D6_

**Elección**: `Route::livewire('/wishlist', 'wishlist-page')->name('wishlist')` sin cambios en la ruta. El componente carga (ya no agrupa por producto):
```php
Auth::user()->wishlists()
    ->with(['productVariant' => fn ($q) => $q->with([
        'product.category',
        'images',
        'prices' => fn ($p) => $p->where('currency', $currency->value),
    ])])
    ->get()
    ->pluck('productVariant');
```
Para cada variante: `isAvailable = $variant->is_active && $variant->product->publishedForStorefront($currency)->whereKey($variant->product_id)->exists()` (R10). `isOutOfStock` reusa la misma regla que `Product::isOutOfStock()` pero a nivel de una sola variante: `! $variant->product->is_preorder && $variant->stock <= 0` (los productos en preorder nunca se marcan agotados, igual que hoy a nivel de producto) (R11). No existe hoy un `ProductVariant::isOutOfStock()` — se agrega como método nuevo en el modelo (mismo criterio, ámbito de variante) para no repetir esta regla inline en el componente. La **imagen mostrada es la de la variante** cuando existe una vinculada (`ProductImage::where('product_variant_id', $variant->id)`, ya construido en el trabajo reciente de "imágenes por variante"); si la variante no tiene imagen propia, cae a `$variant->product->primaryImage()`.
`addToCart(int $variantId)` y `removeFromWishlist(int $variantId)` ya no necesitan adivinar la variante (`$product->variants->first()` desaparece — esto cierra, de paso, el bug real encontrado en revisión visual: antes se agregaba al carrito una variante arbitraria distinta de la guardada). Ambos reciben directamente el id de la variante guardada porque cada fila del listado ES una variante.
**Racional**: cero necesidad de un Service — sigue siendo una lectura de un solo caller. El cambio de "producto" a "variante" es una sustitución mecánica del mismo patrón de query, no una arquitectura nueva.

### D-A7 — Migración: `wishlists.product_id` → `wishlists.product_variant_id`

**Elección**: nueva migración (no se edita `2026_07_19_184505_create_wishlists_table.php` — ya forma parte de la fundación de dominio, potencialmente corrida en otros entornos; editar una migración ya aplicada es inseguro). La nueva migración:
```php
Schema::table('wishlists', function (Blueprint $table) {
    $table->dropUnique(['user_id', 'product_id']);
    $table->foreignId('product_variant_id')->after('user_id')->nullable()->constrained()->cascadeOnDelete();
});

// Backfill: no hay dato real de usuarios todavía (proyecto pre-lanzamiento, solo seeders/pruebas).
// Se trunca la tabla en vez de backfillear una variante arbitraria por fila — más simple y honesto
// que inventar cuál variante "era" la guardada cuando el dato original no la registraba.
DB::table('wishlists')->truncate();

Schema::table('wishlists', function (Blueprint $table) {
    $table->foreignId('product_variant_id')->nullable(false)->change();
    $table->dropColumn('product_id');
    $table->unique(['user_id', 'product_variant_id']);
});
```
`Wishlist` model: `#[Fillable(['user_id', 'product_variant_id'])]`, se quita `product()` (`BelongsTo<Product>`), se agrega `productVariant(): BelongsTo<ProductVariant>`. Si algún caller necesita el producto, pasa por `$wishlist->productVariant->product`.
`Product::wishlists(): HasMany` se elimina — ya no hay FK directa `wishlists.product_id`. Se agrega `ProductVariant::wishlists(): HasMany` (nueva relación, análoga a la de `User`).
`User::wishlists(): HasMany` no cambia de forma (sigue apuntando a `Wishlist`), solo cambia lo que cada fila representa.
**Alternativas consideradas**: mantener `product_id` como columna redundante junto a `product_variant_id` — descartada en D-A1 (sin beneficio real, riesgo de inconsistencia). Migración con backfill "inteligente" (ej. asignar la primera variante activa de cada producto ya guardado) — descartada: no hay filas reales que backfillear en este momento del proyecto (verificar con `database-query` antes de aplicar en cualquier entorno con datos; si alguna vez hubiera datos reales, el truncate dejaría de ser válido y habría que revisar esta decisión).
**Racional**: unicidad correcta a nivel de variante (R13), sin columna muerta, sin ambigüedad sobre qué representa cada fila.

## Cambios de archivos

_Batches 1-2 (implementados con esta tabla original) ya están hechos y en verde. La columna "Revisado por D6" marca lo que esta reapertura vuelve a tocar._

| Archivo | Acción | Descripción | Revisado por D6 |
|---|---|---|---|
| `database/migrations/xxxx_change_wishlists_to_variant_granularity.php` | Nuevo | Ver D-A7 | Sí |
| `app/Models/Wishlist.php` | Modificar | `product_id`→`product_variant_id`; `product()`→`productVariant()` | Sí |
| `app/Models/Product.php` | Modificar | Elimina `wishlists(): HasMany` (ya no hay FK directa) | Sí |
| `app/Models/ProductVariant.php` | Modificar | Agrega `wishlists(): HasMany`, `isOutOfStock(): bool` | Sí |
| `app/Actions/Wishlist/ToggleWishlistAction.php` | Modificar | Firma pasa a `__invoke(User $user, ProductVariant $variant): bool` | Sí |
| `resources/views/components/product-detail/product-detail.php` | Modificar | `toggleFavorite()`/`checkIsFavorited()` operan sobre `$this->selectedVariantId` | Sí |
| `resources/views/components/favorite-button/favorite-button.php` | Modificar | Prop `productVariantId` en vez de `productId` | Sí |
| `resources/views/components/favorite-button/favorite-button.blade.php` | Modificar | `data-product-variant-id` en vez de `data-product-id` | Sí |
| `resources/views/components/product-card/product-card.blade.php` | Modificar | `:product-variant-id="$variant->id"` en vez de `:product-id="$product->id"` | Sí |
| `resources/views/components/wishlist-page/wishlist-page.php` | Modificar | Query por variante, `addToCart`/`removeFromWishlist` reciben `variantId` exacto | Sí |
| `resources/views/components/wishlist-page/wishlist-page.blade.php` | Modificar | Una card por variante, imagen específica de la variante | Sí |
| `routes/web.php` | — | Sin cambios (batch 1-2) | No |
| `resources/views/layouts/storefront.blade.php:56` | — | Sin cambios (batch 1-2) | No |
| `lang/{en,es}/storefront.php` | — | Sin cambios de copy necesarios por D6 | No |
| `tests/Feature/Wishlist/ToggleWishlistActionTest.php` | Modificar | Factories de `ProductVariant` en vez de `Product`; unicidad `[user_id, product_variant_id]` | Sí |
| `tests/Feature/Storefront/ProductDetailFavoriteTest.php` | Modificar | Toggle sobre la variante seleccionada, cambio de color cambia el estado del corazón | Sí |
| `tests/Feature/Storefront/FavoriteButtonTest.php` | Modificar | `productVariantId`, `data-product-variant-id` | Sí |
| `tests/Feature/Storefront/ProductCardTest.php` | Modificar | Embed usa `variant-id` | Sí |
| `tests/Feature/Storefront/WishlistPageTest.php` | Modificar | Una entrada por variante; add-to-cart agrega la variante exacta (regresión del bug encontrado) | Sí |

## Contratos

```php
final class ToggleWishlistAction
{
    public function __invoke(User $user, ProductVariant $variant): bool
    {
        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_variant_id', $variant->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        Wishlist::create(['user_id' => $user->id, 'product_variant_id' => $variant->id]);
        return true;
    }
}
```

## Estrategia de pruebas

| Capa | Qué | Cómo |
|---|---|---|
| Feature (Action) | Guarda/quita; unicidad `[user_id, product_variant_id]` (R13) | `ToggleWishlistActionTest` con `ProductVariant` factories |
| Feature (Livewire) | R1, R2 — toggle en PDP opera sobre la variante seleccionada | `livewire('product-detail', ['slug' => ...])->call('selectVariant', ...)->call('toggleFavorite')` |
| Feature (Livewire) | R3, R9 — `favorite-button` real sobre la variante por defecto de la card | `FavoriteButtonTest` |
| Feature (Livewire) | R4-R7, R10-R12 — página `/wishlist`, una entrada por variante | `WishlistPageTest`; casos: vacío, ownership, variante despublicada, variante agotada, add-to-cart agrega la variante exacta |
| Feature (HTTP) | R8 — nav link | Sin cambios por D6 |

## Migración / Rollout

D-A7 agrega una migración real (antes no había ninguna). Todo en `feature/ui/09-lista-de-deseados`, aislado de `main`. Antes de aplicar la migración en cualquier entorno compartido: confirmar con `database-query` que `wishlists` no tiene filas de usuarios reales (en este punto del proyecto solo debería haber datos de seeders/pruebas) — si las hubiera, el `truncate()` de D-A7 dejaría de ser una decisión trivial y habría que revisarla con el equipo antes de aplicar.

## Preguntas abiertas

Ninguna. D6 (granularidad por variante) se decidió con el usuario tras revisión visual en navegador — ver `sdd/wishlist/state` en Engram para el registro de la conversación. La variante por defecto para favoritear desde la card (sin selector) usa la misma variante que ya resuelve `product-card.php::with()` para su botón de agregar al carrito — no es una decisión nueva, es reusar una ya tomada.
