# F07 — Reviews (opiniones de producto)

> **Estado:** Lista para implementar  
> **ID:** F07 · **Slug:** `07-reviews`  
> **Prerequisitos:** F01 (catálogo), F04/F05 (órdenes pagadas para elegibilidad de compra) — ver [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **Desbloquea:** moderación de UGC; promedio/listado de opiniones en PDP; base para filtro “mejor valorados” (F01-S / shop)

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Producto y alcance F07 | [`specs/_global/01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md) |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Convenciones de código | [`AGENTS.md`](../../../AGENTS.md) / project-conventions |
| Esquema de dominio | `app/Models/Review.php`, `Product.php`, `User.php`, `Order.php`, `OrderItem.php`; `OrderStatusEnum` |
| Doc de dominio reviews | `esquema-bd-bolsos.md` §12 |
| UI / marca | [`specs/ui-briefs/00-design-tokens.md`](../../ui-briefs/00-design-tokens.md), [`03-producto.md`](../../ui-briefs/03-producto.md) |
| Storefront existente | `resources/views/components/product-detail/`, `layouts/storefront.blade.php` |

## User stories

1. **Como** comprador autenticado que pagó un producto, **quiero** dejar una valoración (1–5) y un comentario opcional, **para** compartir mi experiencia.
2. **Como** comprador, **quiero** editar o borrar mi opinión, **para** corregirla o retirarla; al editar, vuelve a moderación.
3. **Como** visitante de la tienda, **quiero** ver solo opiniones aprobadas en la ficha de producto (y un resumen de promedio/conteo), **para** confiar en el catálogo.
4. **Como** administrador, **quiero** listar, aprobar, desaprobar y eliminar reviews, **para** proteger la marca.
5. **Como** front / producto, **quiero** Actions y un enganche Livewire en el PDP existente con el estilo de marca, **para** no reprocesar UI al cerrar el dominio.

## Alcance de esta feature

**Incluye:**

- Reutilizar tabla/modelo `reviews` (sin migración salvo gap real).
- Crear / actualizar / eliminar review por el **dueño** (user autenticado).
- Elegibilidad: solo si el user tiene al menos una orden elegible del producto (ver D8).
- Moderación: `is_approved` default `false`; admin aprueba / desaprueba / elimina.
- Listado público: solo `is_approved = true` a nivel **producto**.
- Resumen: promedio y conteo de reviews **approved** (cálculo live, sin denormalizar en `products`).
- `is_verified_purchase` seteado por el Action al escribir (no por el cliente).
- Unique `(user_id, product_id)`: una review por usuario y producto.
- Filament: resource de moderación (list/view/actions).
- i18n `lang/{en,es}/reviews.php` + keys storefront.
- Enganche en **product detail** existente (Livewire MFC) con tokens de marca.
- Feature tests PHPUnit (dominio, policy, admin esencial, PDP).

**No incluye (F07):**

- Reviews de guests / anónimas.
- Reviews por variante (solo producto).
- Fotos, título, respuesta del vendedor, votes “útil”.
- Auto-approve.
- Filtro shop “mejor valorados” / testimonios home (pueden consumir el resumen después).
- Columnas denormalizadas de rating en `products`.
- Admin inventando reviews de marketing.
- Emails / notificaciones de “nueva review”.
- Flujo completo de login/registro storefront (si no existe: form gated por `Auth`; tests con `actingAs`).
- Wishlist (F08), cambios de fulfillment a `delivered`.

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | DoD superficie | Dominio Reviews + Filament moderación + enganche PDP (estilo marca existente) + tests. |
| D2 | UI storefront | **Sí, mínima y alineada al manual de marca** ya usado en home/catalog/product-detail. No HTML crudo de prueba. |
| D3 | Schema | Reutilizar `reviews` tal cual. **Sin migración** en F07 salvo bug de schema. |
| D4 | Nivel | Review a **producto** (`product_id`), no a variante. |
| D5 | Guests | **No** pueden crear/editar/borrar. |
| D6 | Quién crea | Solo user autenticado **con compra elegible** del producto (D8). |
| D7 | Unique | Una review por `(user_id, product_id)`. Create duplicado → error de dominio. |
| D8 | Compra elegible (opción A) | Existe orden del mismo `user_id` con `status ∈ {paid, processing, shipped, delivered}` y un `order_item` cuya variante pertenece a ese `product_id`. **Excluir** `pending`, `cancelled`, `refunded`. |
| D9 | Delivered only | **No.** `paid+` alcanza (fulfillment post-pago no es DoD operativo hoy). |
| D10 | `is_verified_purchase` | Lo calcula el Action al create/update; cliente **no** lo envía. Con D6, en flujo normal queda `true`. |
| D11 | `order_id` en review | **No** se agrega. |
| D12 | Refund posterior | **No** se recalcula ni se borra la review; el flag queda como al escribir. |
| D13 | Default `is_approved` | `false` al create (y al update del autor). |
| D14 | Auto-approve | **No.** |
| D15 | Público | Solo reviews `is_approved = true`. |
| D16 | Autor ve pendiente | Sí: su propia review (pendiente o no) en el PDP / respuesta de mutación; **no** cuenta en promedio público hasta approve. |
| D17 | Admin actions | Approve, Unapprove, Delete. **Sin** create admin de reviews. **Sin** editar texto del user (delete o unapprove). |
| D18 | Edit del user | Permitido (rating + comment). **Re-modera:** `is_approved = false`. Recalcula verified. |
| D19 | Delete del user | Hard delete de la propia. Admin también hard delete. |
| D20 | Rating | Entero **1–5** inclusive. |
| D21 | Comment | Opcional. Trim; vacío → `null`; max **2000** chars; plain text (strip tags). |
| D22 | Fotos / título / reply | **Out.** |
| D23 | Agregados | `average_rating` + `reviews_count` sobre approved, **live** (query), no columnas en products. |
| D24 | Producto inactivo / no publicable | Si hubo compra, puede crear/editar review. Listado público en PDP solo si el producto se muestra en storefront (mismas reglas de publicación del detalle actual). |
| D25 | AuthZ | Policy: dueño muta las suyas; list público approved; admin modera vía gate panel existente. |
| D26 | Arquitectura | Actions + DTO en área **Reviews**; eligibility compartida en Service o Concern si create/update la reutilizan. Sin gateways. |
| D27 | Entrypoint | Livewire en `product-detail` (primario) + Actions invocables; throttle en mutaciones. |
| D28 | i18n | `lang/{en,es}/reviews.php` + keys en `storefront` si aplica UI. |
| D29 | Naming | Slug `07-reviews`, ID **F07**, área **Reviews**. Specs en español. |
| D30 | Out bloque | Guests, auto-approve, fotos, title, merchant reply, denormalize rating, admin invent, emails, shop sort by rating, login feature formal. |

---

## Criterios de aceptación (EARS)

### Happy path

### R1 — Crear review como comprador elegible

DONDE un usuario autenticado tiene al menos una orden elegible (D8) del producto P y **no** tiene review de P,  
CUANDO envía rating 1–5 y comment opcional válido,  
EL SISTEMA DEBE persistir la review con `user_id` del actor, `product_id` = P, `is_approved = false`, `is_verified_purchase = true`,  
Y DEBE **no** incluirla en el listado/promedio público hasta aprobación.

### R2 — Listado público solo approved

CUANDO un visitante (guest o user) consulta las opiniones públicas del producto P,  
EL SISTEMA DEBE devolver únicamente reviews con `is_approved = true`  
Y DEBE calcular promedio y conteo solo sobre ese conjunto.

### R3 — Admin aprueba review

DONDE un administrador con acceso al panel ve una review pendiente,  
CUANDO ejecuta la acción de aprobar,  
EL SISTEMA DEBE marcar `is_approved = true`  
Y DEBE hacerla visible en el listado/promedio público del producto.

### R4 — Admin desaprueba review

DONDE una review está aprobada,  
CUANDO el administrador la desaprueba,  
EL SISTEMA DEBE marcar `is_approved = false`  
Y DEBE dejar de incluirla en listado/promedio público.

### R5 — Autor edita y re-modera

DONDE el autor tiene una review (approved o no) del producto P,  
CUANDO actualiza rating y/o comment con datos válidos,  
EL SISTEMA DEBE guardar los nuevos valores,  
DEBE forzar `is_approved = false`,  
Y DEBE recalcular `is_verified_purchase` según D8/D10.

### R6 — Autor elimina su review

DONDE el autor tiene una review del producto P,  
CUANDO solicita eliminarla,  
EL SISTEMA DEBE borrarla (hard delete)  
Y DEBE dejar de contarla en agregados públicos.

### R7 — Resumen de producto (approved)

CUANDO se solicita el resumen de opiniones del producto P,  
EL SISTEMA DEBE exponer `reviews_count` = cantidad approved  
Y `average_rating` = promedio aritmético de ratings approved (o null/0 documentado si count = 0),  
SIN incluir pendientes.

### R8 — Enganche PDP con marca

DONDE un visitante está en el detalle de producto publicado (storefront existente),  
CUANDO la página carga,  
EL SISTEMA DEBE mostrar la sección de opiniones (listado approved + resumen) con el estilo del layout storefront / tokens de marca  
Y, si el usuario está autenticado y es elegible sin review previa (o con la suya), DEBE ofrecer el formulario de create/edit coherente con esa UI.

### R9 — Admin lista y filtra moderación

DONDE un administrador está en el resource de reviews,  
CUANDO lista reviews,  
EL SISTEMA DEBE mostrar producto, usuario, rating, approved, verified, fechas  
Y DEBE permitir filtrar al menos por `is_approved` (y búsqueda útil por producto/usuario si el stack lo permite de forma idiomática).

### Validación y error

### R10 — No comprador no puede crear

CUANDO un usuario autenticado **sin** orden elegible (D8) del producto intenta crear una review,  
EL SISTEMA DEBE rechazar la operación con error de dominio/i18n  
SIN persistir fila.

### R11 — Guest no puede mutar

CUANDO un guest intenta create/update/delete,  
EL SISTEMA DEBE denegar (auth required)  
SIN persistir cambios.

### R12 — Unique user+product

CUANDO un usuario que ya tiene review de P intenta **crear** otra,  
EL SISTEMA DEBE rechazar (debe usar update)  
SIN duplicar filas.

### R13 — Rating inválido

CUANDO se envía rating fuera de 1–5 o no entero,  
EL SISTEMA DEBE rechazar la validación  
SIN persistir.

### R14 — Comment demasiado largo / HTML

CUANDO el comment supera 2000 caracteres,  
EL SISTEMA DEBE rechazar.  
CUANDO incluye markup,  
EL SISTEMA DEBE persistir solo texto plano (strip tags) o rechazar según validación de borde; el resultado almacenado NO debe ejecutar HTML.

### R15 — User no edita/borra review ajena

CUANDO un usuario autenticado intenta update/delete de review de otro user,  
EL SISTEMA DEBE denegar  
SIN modificar la fila.

### R16 — Admin elimina

DONDE un administrador,  
CUANDO elimina una review,  
EL SISTEMA DEBE hard-delete  
Y DEBE actualizar de facto los agregados públicos (la review deja de existir).

### R17 — Create sin auth en entrypoint

CUANDO un guest envía el formulario de review en el PDP,  
EL SISTEMA DEBE no crear review  
Y DEBE informar que se requiere autenticación (mensaje i18n).

### R18 — Estados de orden no elegibles

CUANDO el user solo tiene órdenes del producto en `pending`, `cancelled` o `refunded` (y ninguna elegible),  
EL SISTEMA DEBE tratarlo como no comprador (R10).

### R19 — No admin create

DONDE un administrador en Filament,  
EL SISTEMA DEBE **no** exponer create de reviews como flujo de alta de contenido  
(solo moderación sobre reviews existentes de compradores).

### R20 — i18n estable

CUANDO fallan validaciones o reglas de dominio de reviews,  
EL SISTEMA DEBE exponer mensajes vía keys de localización (`reviews.*` / storefront), no copy hardcodeado ES/EN en Actions.

---

## Mapa de pruebas (orientativo)

| Área | Escenarios mínimos |
|------|-------------------|
| Domain create | elegible OK; no elegible; guest; duplicate; rating bounds; comment null/long |
| Domain update | owner re-moderate; foreign deny; recalc verified |
| Domain delete | owner; foreign; admin |
| Moderate | approve; unapprove; list filters |
| Aggregates | count/avg only approved |
| HTTP/Livewire PDP | list approved; create as buyer; guest blocked |
| Filament | list + approve action (convención repo) |

---

## Definition of Done (producto)

- [ ] R1–R20 cubiertos por tests o verificación Filament según convención.
- [ ] Sin migración innecesaria; unique y flags respetados.
- [ ] Filament moderación operativa.
- [ ] PDP muestra sección reviews con estilo de marca existente.
- [ ] Roadmap F07 → **Completa** al cerrar implementación.
- [ ] Pint + tests Sail del alcance en verde.
