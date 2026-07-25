# Design: F10 — Wishlist

> Referencia: criterios R1-R13 y decisiones D1-D5 en [`requirements.md`](./requirements.md). Este documento cubre el CÓMO; no repite el QUÉ.

## Enfoque técnico

No hay migraciones: `wishlists`/`Wishlist`/relaciones `User::wishlists()`/`Product::wishlists()` ya existen y se reusan tal cual. El trabajo es: (1) extraer el toggle ya funcional de `product-detail.php` a `ToggleWishlistAction`, (2) activar el `favorite-button` (hoy placeholder deshabilitado) con esa misma Action, y (3) construir la página `/wishlist` con el patrón `Route::livewire` + MFC ya usado en F08/F09, reusando el query pattern de `publishedForStorefront`/`priceIn` que ya existe en `product-detail.php`.

## Decisiones de arquitectura

### D-A1 — `ToggleWishlistAction` es el único punto de escritura (D1)

**Elección**: `app/Actions/Wishlist/ToggleWishlistAction.php`, invocable, firma `__invoke(User $user, Product $product): bool` (retorna `true` si quedó guardado, `false` si se quitó — el `favorite-button` y la PDP necesitan ese booleano para actualizar su estado local sin una query extra).
**Alternativas consideradas**: dos Actions separadas (`AddToWishlistAction`/`RemoveFromWishlistAction`) — se descarta porque el caso de uso real siempre es "togglear", ambos componentes (PDP y card) ya piensan en términos de toggle, y separar duplicaría la resolución de `firstOrCreate`/`delete` sin beneficio.
**Racional**: replica el `Wishlist::create()`/`delete()` que ya funciona en `product-detail.php:125-138`, solo movido a una Action con `firstOrCreate`+`delete` en vez de dos queries. No hace falta DTO: son 2 argumentos de objeto (User, Product), y project-conventions exime DTO para llamadas triviales de 1-2 argumentos dentro de la misma capa.

### D-A2 — Ownership vive en la firma de la Action, no en un Policy nuevo

**Elección**: la Action recibe `User $user` explícito (siempre `Auth::user()` desde el caller) — no hay "wishlist de otro user" que autorizar porque la Action nunca toma un `user_id` externo ni un `Wishlist $wishlist` ajeno.
**Alternativas consideradas**: `WishlistPolicy` estilo `AddressPolicy` (F09) — se descarta: F09 necesitaba Policy porque el comprador accede a un `Address $address` existente por id (posible IDOR). Acá no hay ruta que reciba un `Wishlist $wishlist` por id; el listado (`/wishlist`) siempre filtra por `Auth::user()->wishlists()`, y el toggle siempre opera sobre `(user, product)` del contexto autenticado. R12 (no acceder a wishlist ajena) se cumple por construcción de la query, no por autorización explícita.
**Racional**: evita una Policy que no protege ningún IDOR real — coherente con "no crear interfaces/policies por las dudas" de project-conventions.

### D-A3 — Refactor de `product-detail.php` sin tocar el resto del componente

**Elección**: `toggleFavorite()` pasa de:
```php
$existing = Wishlist::where('user_id', $user->id)->where('product_id', $product->id)->first();
if ($existing) { $existing->delete(); ... } else { Wishlist::create([...]); ... }
```
a:
```php
public function toggleFavorite(ToggleWishlistAction $toggleWishlist): void
{
    if (Auth::guest()) { $this->redirect(route('login')); return; }

    $product = $this->findPublishedProduct(CurrencyEnum::from($this->currency));
    $saved = $toggleWishlist(Auth::user(), $product);

    $this->dispatch('toast', message: $saved
        ? __('storefront.products.added_to_favorites')
        : __('storefront.products.removed_from_favorites'));
}
```
`checkIsFavorited()` se deja igual (es una lectura, no una escritura — no viola D1) pero puede simplificarse a `$product->wishlists()->where('user_id', Auth::id())->exists()` reusando la relación ya existente en vez de `Wishlist::where(...)`.
**Racional**: cero cambios a reviews/cart/variantes del mismo archivo — la Action se inyecta como parámetro del método (mismo patrón que `saveReview(CreateReviewAction $createReview, ...)` ya usado ahí mismo).

### D-A4 — Activar `favorite-button` (D2 cerrada)

**Elección**: el componente pasa de estático a Livewire real:
```php
// favorite-button.php
public bool $isFavorited = false;

public function mount(int $productId): void
{
    $this->productId = $productId;
    $this->isFavorited = Auth::check()
        && Wishlist::where('user_id', Auth::id())->where('product_id', $productId)->exists();
}

public function toggle(ToggleWishlistAction $toggleWishlist): void
{
    if (Auth::guest()) { $this->redirect(route('login')); return; }

    $this->isFavorited = $toggleWishlist(Auth::user(), Product::findOrFail($this->productId));
    $this->dispatch('toast', message: $this->isFavorited
        ? __('storefront.products.added_to_favorites')
        : __('storefront.products.removed_from_favorites'));
}
```
El blade agrega `wire:click="toggle"`, quita `disabled`/`aria-disabled="true"` para usuarios autenticados (se mantiene visualmente para guests, pero clickeable → redirige a login, no como botón deshabilitado sin acción — así R9 se cumple igual para guests sin necesitar dos variantes de markup).
**`FavoriteButtonTest` — impacto**: los 5 tests actuales quedan obsoletos porque afirman `disabled`/`aria-disabled="true"`/`assertDontSeeHtml('wire:click')`. Se reemplazan por: toggle guarda cuando no hay estado previo, toggle quita cuando ya estaba guardado, guest se redirige a login sin togglear, `data-product-id` sigue presente, label accesible se mantiene. No se agregan tests nuevos de más — se corrige el archivo existente porque el escenario que probaba ("está deshabilitado") ya no es el comportamiento real.

### D-A5 — Página `/wishlist`: mismo query pattern que `product-detail.php`

**Elección**: `Route::livewire('/wishlist', 'wishlist-page')->name('wishlist')` dentro del grupo `Route::middleware('auth')` de `routes/web.php` (mismo grupo que `/profile/*`). El componente carga:
```php
Auth::user()->wishlists()
    ->with(['product' => fn ($q) => $q->with(['images', 'variants' => fn ($v) => $v->active()->withPriceIn($currency)->with(['prices' => fn ($p) => $p->where('currency', $currency->value)])])])
    ->get()
    ->pluck('product');
```
Para cada producto, `isAvailable = $product->publishedForStorefront($currency)->whereKey($product->id)->exists()` marca R10 ("ya no disponible" si false) e `isOutOfStock()` (ya existe en el modelo) marca R11. Sin scope nuevo: es la misma composición de scopes que ya usa `product-detail.php`, no una query nueva a mantener en paralelo.
**Racional**: cero necesidad de un Service — es una lectura de un solo caller, mismo criterio que D-A5 de F09 (no crear Action/Service que solo reenvía una query).

## Cambios de archivos

| Archivo | Acción | Descripción |
|---|---|---|
| `app/Actions/Wishlist/ToggleWishlistAction.php` | Nuevo | `__invoke(User $user, Product $product): bool` |
| `resources/views/components/product-detail/product-detail.php` | Modificar | `toggleFavorite()` usa la Action; `checkIsFavorited()` reusa `$product->wishlists()` |
| `resources/views/components/favorite-button/favorite-button.php` | Modificar | Estado real + método `toggle()` con la Action |
| `resources/views/components/favorite-button/favorite-button.blade.php` | Modificar | `wire:click`, quita `disabled` estático |
| `resources/views/components/wishlist-page/` | Nuevo | Livewire MFC full-page, listado según UI brief |
| `routes/web.php` | Modificar | 1 ruta nueva `/wishlist` dentro del grupo `auth` |
| `resources/views/layouts/storefront.blade.php:56` | Modificar | `href="#"` → `route('wishlist')` (coordinar con el dev de frontend activo en este archivo) |
| `lang/{en,es}/storefront.php` | Modificar | Copy de estado vacío / "ya no disponible" para la página nueva |
| `tests/Feature/Wishlist/ToggleWishlistActionTest.php` | Nuevo | Cobertura de la Action |
| `tests/Feature/Storefront/FavoriteButtonTest.php` | Modificar | Reemplaza aserciones de "deshabilitado" por comportamiento real |
| `tests/Feature/Storefront/WishlistPageTest.php` | Nuevo | Listado, vacío, ownership, no disponible, agotado |

## Contratos

```php
final class ToggleWishlistAction
{
    public function __invoke(User $user, Product $product): bool
    {
        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);
        return true;
    }
}
```

## Estrategia de pruebas

| Capa | Qué | Cómo |
|---|---|---|
| Feature (Action) | Guarda/quita; unicidad `[user_id, product_id]` (R13) | `ToggleWishlistActionTest` con factories |
| Feature (Livewire) | R1, R2 — toggle en PDP | `livewire('product-detail', ['slug' => ...])->call('toggleFavorite')` |
| Feature (Livewire) | R3, R9 — `favorite-button` real, guest redirigido | Reemplaza `FavoriteButtonTest` actual |
| Feature (Livewire) | R4-R7, R10-R12 — página `/wishlist` | `livewire('wishlist-page')`; casos: vacío, ownership, despublicado, agotado |
| Feature (HTTP) | R8 — nav link | Test de `storefront.blade.php` o smoke test de ruta |

## Migración / Rollout

No requiere migraciones. Todo en `feature/10-wishlist`, aislado de `develop`. Coordinar con el dev de frontend antes de tocar `storefront.blade.php` (edita ese archivo en paralelo) y antes de reemplazar `FavoriteButtonTest` (es su test, activarlo cambia su contrato).

## Preguntas abiertas

Ninguna — D2 (activación del `favorite-button`) quedó cerrada en requirements antes de este design.
