# F10 — Wishlist

> **Estado:** Completa
> **ID:** F10 · **Slug:** `10-wishlist`
> **Prerequisitos:** F01 (catálogo), F02 (cuentas), F08 (auth storefront) — ver [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)
> **Desbloquea:** —

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Producto y alcance F10 | [`specs/_global/01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md) |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Convenciones de código (Actions, DTOs) | `AGENTS.md` / project-conventions |
| Esquema de dominio | `app/Models/{User,Product,ProductVariant,Wishlist}.php`; base: migration `2026_07_19_184505_create_wishlists_table.php` — **migrado por D6**, ver `design.md` D-A7 |
| UI brief de la página de listado | [`specs/ui-briefs/09-lista-de-deseados.md`](../../ui-briefs/09-lista-de-deseados.md) |
| Requisito de cuenta identificada (ya cerrado) | `specs/features/08-auth/requirements.md` D9 |
| Storefront existente | `resources/views/components/{product-detail,product-card,favorite-button}/`, `layouts/storefront.blade.php` |

> Nota de estado de partida: **no se arranca de cero**. El dominio (`wishlists` table/model/relations) ya existe, y hay trabajo ad-hoc de frontend ya en el repo: un toggle funcional en `product-detail.php` (bypassa la convención de Actions/DTO), un componente `favorite-button` deshabilitado a propósito en las product cards (con test que lo garantiza), y un link de nav muerto (`href="#"`). Esta feature formaliza y completa ese trabajo — ver `design.md` para el detalle de refactor.

## User stories

1. **Como** comprador autenticado, **quiero** guardar un producto en mi wishlist desde su página de detalle, **para** encontrarlo después sin tener que buscarlo de nuevo.
2. **Como** comprador autenticado, **quiero** guardar o quitar un producto de mi wishlist directamente desde la card del listado/shop, **para** no tener que entrar al detalle de cada producto.
3. **Como** comprador autenticado, **quiero** ver todos mis productos guardados en una sola página, **para** decidir cuáles comprar más adelante.
4. **Como** comprador autenticado, **quiero** agregar al carrito o quitar de mi wishlist directamente desde esa página, **para** avanzar mi compra sin pasos extra.
5. **Como** visitante sin sesión iniciada, **quiero** que se me pida iniciar sesión al intentar guardar un favorito, **para** entender por qué esa acción no está disponible sin cuenta.
6. **Como** negocio, **quiero** que la wishlist solo muestre productos todavía publicables, **para** no confundir al comprador con productos descontinuados o agotados sin aviso.

## Alcance de esta feature

**Incluye:**

- `ToggleWishlistAction` (dominio) que reemplaza el `Wishlist::create()`/`delete()` inline hoy en `product-detail.php`.
- Activar el componente `favorite-button` de las product cards: pasa de deshabilitado/estático a togglear la wishlist en vivo, usando la misma Action.
- Página de listado `/wishlist` (Livewire full-page), según `specs/ui-briefs/09-lista-de-deseados.md`: grid de productos guardados, estado vacío, badge de disponibilidad/stock, botón agregar al carrito, botón quitar de la wishlist, producto ya no disponible.
- Wire real del enlace de navegación de favoritos (hoy `href="#"` en `storefront.blade.php`) a la nueva ruta.
- Redirección a login cuando un visitante sin sesión intenta togglear un favorito (ya implementado en la PDP; se extiende al `favorite-button`).
- Autorización por ownership: un comprador solo ve/modifica su propia wishlist.

**No incluye (F10):**

- Wishlist para invitados — excluido explícitamente; `wishlists.user_id` es `NOT NULL` en el esquema ya existente (D9 de F08).
- Múltiples listas de deseados — mejora futura (ya diferido en el UI brief).
- Compartir wishlist — mejora futura.
- Notificaciones de baja de precio o reingreso de stock — mejora futura.
- Agregar todos los productos de la wishlist al carrito con una sola acción — mejora futura.
- Selector de variante interactivo en la product card — la card sigue sin selector; favoritear desde ahí usa la variante por defecto (ver D6), igual que su botón de agregar al carrito ya existente.

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | Corrección de convención | El toggle de favoritos deja de escribir Eloquent inline en el componente Livewire; pasa por `ToggleWishlistAction` como toda escritura de dominio en este proyecto. |
| D2 | Activación del `favorite-button` | **Cerrada:** se activa como parte de F10 (deja de ser un placeholder deshabilitado). Se actualiza `FavoriteButtonTest` para reflejar el comportamiento real. |
| D3 | Alcance sin cuenta | Hereda D9 de F08: la wishlist requiere usuario identificado, sin modo invitado. |
| D4 | Producto ya no disponible | Un producto guardado que deja de ser publicable (despublicado, sin variantes con precio) se sigue listando en `/wishlist` marcado como "ya no disponible", no se oculta ni se borra automáticamente de la wishlist. |
| D5 | Sincronización entre dispositivos | La wishlist es por cuenta (no por sesión/dispositivo); al iniciar sesión en cualquier dispositivo el comprador ve la misma lista — consecuencia directa del modelo de datos, no requiere trabajo adicional. |
| D6 | Granularidad por variante (revierte la premisa original del UI brief) | La wishlist guarda la **variante específica** (`product_variant_id`: color/talla), no el producto genérico. Reemplaza la decisión inicial ("por producto, se ven las variantes al entrar al detalle") tras revisión visual post-implementación: el comprador espera guardar el color/talla exacto que vio, no un producto ambiguo. Requiere migración — ver `design.md` D-A7. Guardar desde la card (sin selector visible) usa la misma variante por defecto que ya usa su botón de agregar al carrito (`$product->variants->first()`), por consistencia con ese patrón ya existente. |

---

## Criterios de aceptación (EARS)

### Happy path

### R1 — Guardar una variante en la wishlist desde la PDP _(revisado por D6)_

CUANDO un comprador autenticado togglea el estado de favorito de la variante actualmente seleccionada de un producto publicado desde su página de detalle,
EL SISTEMA DEBE guardar esa variante específica (color/talla) en su wishlist
Y DEBE reflejar el nuevo estado ("guardado") de inmediato en la interfaz.

### R2 — Quitar una variante de la wishlist desde la PDP _(revisado por D6)_

CUANDO un comprador autenticado togglea el estado de favorito de la variante actualmente seleccionada, ya guardada, desde su página de detalle,
EL SISTEMA DEBE quitar esa variante de su wishlist
Y DEBE reflejar el nuevo estado ("no guardado") de inmediato en la interfaz.

### R3 — Guardar o quitar la variante por defecto desde la card del listado _(revisado por D6)_

CUANDO un comprador autenticado togglea el `favorite-button` de una product card (fuera de la PDP, sin selector de variante visible),
EL SISTEMA DEBE guardar o quitar la variante por defecto de ese producto (la misma que ya usa el botón de agregar al carrito de la card) de su wishlist según su estado actual,
usando la misma lógica de dominio que R1/R2.

### R4 — Listado de mi wishlist _(revisado por D6)_

CUANDO un comprador autenticado accede a la página `/wishlist`,
EL SISTEMA DEBE mostrar únicamente las variantes que él mismo guardó (una entrada por variante, con su imagen específica cuando exista una imagen vinculada a esa variante, color/talla, nombre del producto, precio en la moneda de contexto, slug y estado de disponibilidad/stock de esa variante),
SIN agrupar por producto — dos variantes distintas del mismo producto son dos entradas independientes.

### R5 — Agregar al carrito desde la wishlist _(revisado por D6)_

CUANDO un comprador autenticado agrega al carrito una variante disponible desde la página `/wishlist`,
EL SISTEMA DEBE agregar exactamente esa variante guardada a su carrito (no una variante distinta del mismo producto)
SIN quitarla automáticamente de la wishlist.

### R6 — Quitar de la wishlist desde el listado

CUANDO un comprador autenticado quita un producto desde la página `/wishlist`,
EL SISTEMA DEBE eliminarlo de su wishlist
Y DEBE actualizar el listado sin recargar la página completa.

### R7 — Wishlist vacía

CUANDO un comprador autenticado sin productos guardados accede a `/wishlist`,
EL SISTEMA DEBE mostrar un estado vacío con mensaje claro y un enlace para explorar el catálogo.

### R8 — Enlace de navegación a la wishlist

DONDE un comprador autenticado está en cualquier pantalla del storefront,
CUANDO hace clic en el ícono/enlace de favoritos del encabezado,
EL SISTEMA DEBE llevarlo a `/wishlist`.

### Validación y error

### R9 — Toggle de favorito sin sesión iniciada

CUANDO un visitante sin sesión iniciada intenta togglear un favorito (desde la PDP o desde una product card),
EL SISTEMA DEBE redirigirlo al inicio de sesión
SIN guardar ni modificar ninguna wishlist.

### R10 — Variante ya no disponible en el listado _(revisado por D6)_

CUANDO la variante guardada en la wishlist ya no es publicable (variante inactiva, o producto despublicado, o sin precio en la moneda de contexto) al momento de listar `/wishlist`,
EL SISTEMA DEBE mostrarla marcada como "ya no disponible"
Y DEBE deshabilitar el botón de agregar al carrito para esa variante
SIN eliminarla automáticamente de la wishlist.

### R11 — Variante agotada en el listado _(revisado por D6)_

CUANDO la variante guardada en la wishlist está activa/publicable pero sin stock disponible,
EL SISTEMA DEBE mostrar el badge de "agotado"
Y DEBE deshabilitar el botón de agregar al carrito para esa variante.

### R12 — Acceso a la wishlist de otro comprador

CUANDO un comprador autenticado intenta acceder, listar o modificar la wishlist de otra cuenta,
EL SISTEMA DEBE denegar la operación
SIN exponer las variantes guardadas de la cuenta ajena.

### R13 — Toggle duplicado (misma acción dos veces) _(revisado por D6)_

CUANDO un comprador autenticado togglea la misma variante dos veces seguidas (guardar y quitar, o viceversa),
EL SISTEMA DEBE reflejar el resultado neto correcto (guardado o no guardado) sin crear registros duplicados,
respetando la restricción única `[user_id, product_variant_id]` del esquema (ver D-A7 en `design.md`).

---

## Mapa de pruebas (orientativo)

| Área | Escenarios mínimos |
|------|-------------------|
| `ToggleWishlistAction` | guarda cuando no existe; elimina cuando existe; respeta unicidad `[user_id, product_variant_id]`; no permite togglear en nombre de otro usuario |
| PDP (`product-detail.php`) | toggle guarda/quita vía Action (no Eloquent inline); estado reflejado en la vista; guest redirigido a login |
| `favorite-button` (product card) | wire:click real habilitado; refleja estado guardado/no guardado; guest redirigido a login; reemplaza aserciones actuales de "deshabilitado" en `FavoriteButtonTest` |
| Página `/wishlist` | listado propio; estado vacío; producto despublicado marcado "ya no disponible"; producto agotado con botón deshabilitado; agregar al carrito sin quitar de la wishlist; quitar actualiza el listado; ownership (no accede a wishlist ajena) |
| Navegación | enlace del header lleva a `/wishlist` |

---

## Definition of Done (producto)

- [x] R1–R5, R10, R11, R13 (revisados por D6) cubiertos por tests tras la migración a granularidad por variante.
- [x] R6–R9, R12 sin cambio de comportamiento — se mantienen verdes.
- [x] `ToggleWishlistAction` es el único punto de escritura de `Wishlist` en el storefront — cero `Wishlist::create()`/`delete()` inline fuera de la Action.
- [x] `favorite-button` activado en producción, con `FavoriteButtonTest` actualizado (ya no asume estado deshabilitado).
- [x] Página `/wishlist` funcional a nivel de variante según `specs/ui-briefs/09-lista-de-deseados.md` + D6, enlazada desde el nav real.
- [x] Ownership verificado — ningún comprador accede a la wishlist de otra cuenta.
- [x] `lang/{en,es}` completos para toda la copy nueva de esta feature (reutilizar claves ya existentes en `storefront.php` cuando aplique).
- [x] Migración D-A7 aplicada (`wishlists.product_variant_id`, unique `[user_id, product_variant_id]`), sin `product_id` residual.
- [x] Roadmap F10 → **Completa** al cerrar la implementación de D6.
- [x] Pint + tests Sail del alcance en verde tras D6.
