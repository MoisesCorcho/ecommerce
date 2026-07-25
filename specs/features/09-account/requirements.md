# F09 — Cuenta/perfil del comprador

> **Estado:** Lista para implementar
> **ID:** F09 · **Slug:** `09-account`
> **Prerequisitos:** F08 (auth storefront) — ver [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)
> **Desbloquea:** —

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Producto y alcance F09 | [`specs/_global/01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md) |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Convenciones de código | [`AGENTS.md`](../../../AGENTS.md) / project-conventions |
| Esquema de dominio | `app/Models/{User,Address,Order,Review}.php` |
| Invariante de dirección predeterminada (ya existente) | `specs/features/02-accounts-addresses/requirements.md` D4 |
| "Mis pedidos" diferido desde F04 | `specs/features/04-checkout-orders/requirements.md` D2 |
| Reglas de reseña propia (ya existentes) | `specs/features/07-reviews/requirements.md` |
| Auth y verificación de email (ya existente) | `specs/features/08-auth/requirements.md` |
| Storefront existente | `resources/views/components/{login,register,checkout}-page/`, `layouts/storefront.blade.php` |

> Nota de interfaz: `layouts/storefront.blade.php` ya referencia un enlace de cuenta condicionado a la existencia de una ruta llamada `profile`. El nombre de esa ruta queda fijado por esa dependencia externa; se detalla en `design.md`, no es un criterio de comportamiento.

## User stories

1. **Como** comprador autenticado, **quiero** editar mi nombre, email y teléfono, **para** mantener mis datos de cuenta actualizados.
2. **Como** comprador autenticado, **quiero** cambiar mi contraseña estando logueado, **para** proteger mi cuenta sin depender del flujo de "olvidé mi contraseña".
3. **Como** negocio, **quiero** que un cambio de email vuelva a exigir verificación, **para** no confiar en una dirección que el comprador ya no controla.
4. **Como** comprador autenticado, **quiero** administrar mi libreta de direcciones (alta, edición, eliminación, marcar predeterminada), **para** no reescribir mis datos de envío en cada compra.
5. **Como** comprador autenticado, **quiero** ver el historial de mis pedidos pagados y su detalle, **para** hacerle seguimiento a mis compras.
6. **Como** comprador autenticado, **quiero** ver, editar y eliminar mis propias reseñas desde mi cuenta, **para** corregir o retirar mi opinión sin ir producto por producto.
7. **Como** negocio, **quiero** que cada comprador solo pueda ver o modificar sus propios datos, **para** proteger la privacidad de las demás cuentas.

## Alcance de esta feature

**Incluye:**

- Edición de datos básicos de perfil (nombre, email, teléfono).
- Cambio de email con re-verificación obligatoria (reutiliza el mecanismo de verificación de F08).
- Cambio de contraseña estando autenticado, con confirmación de la contraseña actual.
- Libreta de direcciones del comprador: alta, edición, eliminación y marcar predeterminada, respetando la invariante de una sola dirección predeterminada por cuenta (ya definida en F02).
- "Mis pedidos": listado de pedidos propios con estado pagado, en procesamiento, enviado o entregado, y detalle de solo lectura de cada uno.
- "Mis reseñas": listado de reseñas propias con su estado de moderación, edición y eliminación (reutiliza las reglas ya cerradas en F07: editar vuelve la reseña a pendiente de moderación).
- Autorización por ownership en cada pantalla y operación de esta feature.
- Mensajes en español (i18n) para los flujos de esta feature.

**No incluye (F09):**

- Eliminación de cuenta (self-service) — diferido a una feature futura.
- Wishlist — F10, se especifica aparte.
- Login social, 2FA — ya excluidos en F08, no se reabren acá.
- Vincular pedidos hechos como invitado a la cuenta — ya excluido en F08.
- Cambios al esquema de datos — el grafo de dominio (`User`, `Address`, `Order`, `Review`) ya cubre lo necesario.
- Moderación de reseñas (aprobar/rechazar) — sigue siendo exclusiva del panel admin (F07); esta feature solo expone la vista/edición del propio autor.
- Pedidos propios pendientes de pago — no aparecen en "mis pedidos" (ver D3).

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | Alcance de edición de perfil | Nombre, email y teléfono, más cambio de contraseña estando logueado (requiere contraseña actual). |
| D2 | Cambio de email | Exige re-verificación: el email queda como no verificado y se reenvía el enlace, igual que en el registro de F08. |
| D3 | Alcance de "mis pedidos" | Solo pedidos con estado pagado, en procesamiento, enviado o entregado. Los pendientes de pago no se listan acá. |
| D4 | Eliminación de cuenta | Fuera de alcance de F09; diferido. |
| D5 | Dirección predeterminada eliminada | Al eliminar la única dirección marcada como predeterminada, el sistema NO reasigna automáticamente otra como predeterminada; el comprador debe marcarla explícitamente en su próxima acción. |
| D6 | Edición de reseña propia desde la cuenta | Hereda la regla ya cerrada en F07: toda edición del autor vuelve la reseña a pendiente de moderación. No es una decisión nueva, se aplica igual desde este listado. |

---

## Criterios de aceptación (EARS)

### Happy path

### R1 — Edición de datos básicos de perfil

CUANDO un comprador autenticado envía el formulario de perfil con nombre, teléfono y/o email válidos y distintos a los actuales,
EL SISTEMA DEBE actualizar esos datos en su cuenta
Y, si el email cambió, DEBE marcarlo como no verificado y reenviar el enlace de verificación.

### R2 — Cambio de contraseña desde el perfil

CUANDO un comprador autenticado envía su contraseña actual correcta junto con una nueva contraseña válida y su confirmación coincidente,
EL SISTEMA DEBE actualizar la contraseña de la cuenta.

### R3 — Alta de dirección en la libreta

CUANDO un comprador autenticado envía el formulario de nueva dirección con todos los campos requeridos válidos,
EL SISTEMA DEBE agregarla a la libreta de direcciones de su propia cuenta.

### R4 — Marcar dirección como predeterminada

CUANDO un comprador autenticado marca una dirección propia como predeterminada,
EL SISTEMA DEBE dejarla como la única dirección predeterminada de esa cuenta
Y DEBE quitar la marca de predeterminada a cualquier otra dirección del mismo comprador que la tuviera.

### R5 — Edición de dirección existente

CUANDO un comprador autenticado edita una dirección propia con datos válidos,
EL SISTEMA DEBE actualizar esa dirección.

### R6 — Eliminación de dirección

CUANDO un comprador autenticado elimina una dirección propia de su libreta,
EL SISTEMA DEBE quitarla de la libreta
SIN afectar los pedidos ya realizados que la referenciaron.

### R7 — Listado de mis pedidos

CUANDO un comprador autenticado accede a su historial de pedidos,
EL SISTEMA DEBE mostrar únicamente sus propios pedidos con estado pagado, en procesamiento, enviado o entregado,
SIN incluir pedidos propios pendientes de pago ni pedidos de otros compradores.

### R8 — Detalle de un pedido propio

CUANDO un comprador autenticado abre el detalle de un pedido propio elegible,
EL SISTEMA DEBE mostrar los ítems, montos, dirección de envío y estado de ese pedido en modo de solo lectura.

### R9 — Listado de mis reseñas

CUANDO un comprador autenticado accede a su listado de reseñas,
EL SISTEMA DEBE mostrar únicamente las reseñas que él mismo creó, junto con su estado de moderación.

### R10 — Edición de reseña propia desde la cuenta

CUANDO un comprador autenticado edita una reseña propia desde su listado de reseñas con calificación y/o comentario válidos,
EL SISTEMA DEBE actualizar la reseña
Y DEBE volver a marcarla como pendiente de moderación.

### R11 — Eliminación de reseña propia desde la cuenta

CUANDO un comprador autenticado elimina una reseña propia desde su listado de reseñas,
EL SISTEMA DEBE eliminarla.

### R12 — Sección de cuenta con estilo de marca

DONDE un comprador autenticado está en cualquier pantalla de la sección de cuenta (perfil, direcciones, pedidos, reseñas),
CUANDO la página carga,
EL SISTEMA DEBE mostrarla con el mismo estilo visual del storefront existente.

### Validación y error

### R13 — Perfil con datos inválidos o email duplicado

CUANDO el formulario de perfil se envía con un email ya usado por otra cuenta, un formato de email o teléfono inválido, o un nombre vacío,
EL SISTEMA DEBE rechazar la operación
SIN modificar los datos de la cuenta.

### R14 — Cambio de contraseña con contraseña actual incorrecta

CUANDO el formulario de cambio de contraseña se envía con la contraseña actual incorrecta, la nueva contraseña sin cumplir la política mínima, o la confirmación sin coincidir,
EL SISTEMA DEBE rechazar la operación
SIN modificar la contraseña vigente.

### R15 — Dirección con datos inválidos o incompletos

CUANDO el formulario de dirección (alta o edición) se envía con campos requeridos vacíos o con formato inválido,
EL SISTEMA DEBE rechazar la operación
SIN crear ni modificar la dirección.

### R16 — Acceso a dirección, pedido o reseña de otro comprador

CUANDO un comprador autenticado intenta ver, editar o eliminar una dirección, un pedido o una reseña que no le pertenece,
EL SISTEMA DEBE denegar la operación
SIN exponer datos de la cuenta ajena.

### R17 — Acceso a la sección de cuenta sin autenticar

CUANDO un visitante sin sesión iniciada intenta acceder a cualquier pantalla de la sección de cuenta,
EL SISTEMA DEBE redirigirlo al inicio de sesión
SIN mostrar ningún dato de cuenta.

### R18 — Eliminación de la única dirección predeterminada

CUANDO un comprador autenticado elimina la única dirección marcada como predeterminada de su libreta,
EL SISTEMA DEBE permitir la eliminación
Y NO DEBE marcar automáticamente otra dirección como predeterminada sin una acción explícita del comprador.

### R19 — Intento de acceso a la sección de cuenta con email no verificado

CUANDO un comprador autenticado con email no verificado accede a la sección de cuenta,
EL SISTEMA DEBE permitir ver y editar su perfil para poder re-verificar el email,
Y DEBE aplicar las mismas restricciones de F08 (R13) sobre acciones de compromiso donde correspondan.

---

## Mapa de pruebas (orientativo)

| Área | Escenarios mínimos |
|------|-------------------|
| Perfil | edición válida de nombre/teléfono; cambio de email dispara re-verificación; email duplicado rechazado; datos inválidos rechazados |
| Contraseña | cambio válido con contraseña actual correcta; contraseña actual incorrecta rechazada; nueva contraseña inválida rechazada |
| Direcciones | alta válida; edición válida; eliminación; marcar predeterminada reemplaza a la anterior; eliminar la única predeterminada no reasigna automáticamente; datos inválidos rechazados; ownership (no accede a dirección ajena) |
| Mis pedidos | listado solo paid+; pendiente de pago excluido; detalle de pedido propio; ownership (no accede a pedido ajeno) |
| Mis reseñas | listado propio con estado; edición vuelve a pendiente; eliminación; ownership (no accede a reseña ajena) |
| AuthZ transversal | visitante sin sesión redirigido; comprador con email no verificado puede editar perfil |

---

## Definition of Done (producto)

- [ ] R1–R19 cubiertos por tests o verificación manual documentada.
- [ ] Ownership verificado en las cuatro áreas (perfil, direcciones, pedidos, reseñas) — ningún comprador accede a datos de otra cuenta.
- [ ] Invariante de dirección predeterminada (D5) verificada también desde el storefront, sin duplicar la regla del admin (F02).
- [ ] Reseñas editadas desde la cuenta vuelven a pendiente de moderación (D6), sin regresión en el flujo de moderación de F07.
- [ ] `lang/{en,es}` completos para toda la copy nueva de esta feature.
- [ ] Roadmap F09 → **Completa** al cerrar implementación.
- [ ] Pint + tests Sail del alcance en verde.
