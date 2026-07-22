# F02 — Cuentas y direcciones (admin Filament)

> **Estado:** Completa  
> **ID:** F02 · **Slug:** `02-accounts-addresses`  
> **Prerequisitos:** Fundación de dominio (models, migrations, factories) — ver [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **Dependencia blanda:** F01 (gate de panel `admin_emails` reutilizable; no bloquea si el panel ya existe)

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Producto y alcance F02 | [`specs/_global/01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md) |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Convenciones de código | [`AGENTS.md`](../../../AGENTS.md) / project-conventions |
| Esquema de dominio | `app/Models/User.php`, `app/Models/Address.php`, migrations de `users` y `addresses` |

## User stories

1. **Como** administrador de la tienda, **quiero** listar y buscar cuentas de usuario, **para** localizar compradores y dar soporte operativo.
2. **Como** administrador, **quiero** crear y editar cuentas (nombre, email, teléfono y contraseña cuando corresponda), **para** mantener los datos necesarios para la compra y el acceso.
3. **Como** administrador, **quiero** gestionar las direcciones de un usuario (crear, editar, eliminar y marcar predeterminada), **para** dejar listas las direcciones que usará el checkout (F04).
4. **Como** operador de seguridad, **quiero** que solo cuentas de administración autorizadas accedan a esta gestión, **para** proteger datos personales de compradores.

## Alcance de esta feature

**Incluye:** CRUD admin (Filament) de usuarios y de sus direcciones; invariante de dirección predeterminada por usuario; Actions/DTOs de dominio necesarios; reutilizar gate de panel existente (`admin_emails`).

**No incluye (F02):**

- Componentes Livewire de tienda (login, registro, perfil, libreta de direcciones del comprador).
- Checkout, carrito, pagos, cupones, wishlist, reviews.
- Spatie Permission / roles granulares.
- Métodos de pago guardados de terceros.
- Campos o tablas nuevas de schema salvo gap real documentado en design/tasks (el grafo `User` + `Address` ya existe).

### Fuera de alcance por decisión de slice

La **experiencia de cuenta del comprador** (UI pública) queda **fuera del DoD de F02**.  
Se documenta aquí solo para no mezclarla con el admin; se reactivará en una slice de storefront de cuentas cuando el equipo la priorice.

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | Alcance F02 | **Solo admin Filament** de usuarios y direcciones. **Sin** Livewire storefront. |
| D2 | AuthZ admin | Reutilizar gate F01: email ∈ `config('ecommerce.admin_emails')`. Sin Spatie. |
| D3 | Schema | No se agregan columnas nuevas por defecto. Se usan `users` (`name`, `email`, `phone`, `password`, soft deletes) y `addresses` existentes. |
| D4 | Dirección predeterminada | Como máximo **una** dirección con `is_default = true` **por usuario**. Al marcar una como default, cualquier otra default del mismo usuario deja de serlo. |
| D5 | Soft delete de usuarios | El admin puede soft-deletear usuarios. Las direcciones se eliminan en cascada solo si el user se borra en duro a nivel BD; con soft delete el user no se elimina físicamente y conserva direcciones hasta hard delete (comportamiento Eloquent + FK actual). Listado admin: por defecto **excluye** soft-deleted; filtro opcional “solo eliminados” / restore **no** es obligatorio en F02. |
| D6 | Contraseña | En **create**: contraseña obligatoria. En **edit**: opcional; si se deja vacía, no se cambia. Nunca se muestra el hash en UI. |
| D7 | Email | Único entre users (ignore self en update). Formato email válido. |
| D8 | Teléfono de usuario | Opcional en admin (nullable en schema). Teléfono de **dirección** es obligatorio. |
| D9 | País de dirección | Código de país de 2 letras (ISO 3166-1 alpha-2), default de negocio `CO` si no se indica. |
| D10 | Ownership de dirección | Toda dirección pertenece a un `user_id`. No hay direcciones huérfanas. En UI se gestionan en contexto del usuario (RelationManager o equivalente). |
| D11 | Idioma de specs | Español. EARS según [`02-feature-quality.md`](../../_global/02-feature-quality.md). |
| D12 | Naming | Feature slug `02-accounts-addresses`, ID **F02**. |
| D13 | Áreas de código | `Users` y `Addresses` (tipo primero: `app/Actions/Users`, `app/Actions/Addresses`, etc.). |

---

## Criterios de aceptación (EARS)

### Happy path — en alcance F02

### R1 — Listado de usuarios en admin

DONDE un administrador autenticado con acceso al panel está en la gestión de usuarios,  
EL SISTEMA DEBE mostrar un listado de usuarios no eliminados (soft delete) con al menos nombre, email y teléfono (si existe)  
Y DEBE permitir buscar o filtrar por nombre o email de forma usable para soporte.

### R2 — Alta de usuario en admin

DONDE un administrador autenticado con acceso al panel está en el alta de usuarios,  
CUANDO envía un alta con nombre, email único válido y contraseña válidos (teléfono opcional),  
EL SISTEMA DEBE persistir el usuario  
Y el usuario DEBE aparecer en el listado de usuarios del panel.

### R3 — Edición de usuario en admin

DONDE un administrador autenticado con acceso al panel,  
CUANDO actualiza nombre, email, teléfono y/o contraseña de un usuario existente con valores válidos,  
EL SISTEMA DEBE persistir los cambios  
Y, SI la contraseña se deja vacía en la edición, EL SISTEMA DEBE conservar la contraseña anterior.

### R4 — Soft delete de usuario

DONDE un administrador autenticado con acceso al panel,  
CUANDO elimina un usuario desde el panel (soft delete),  
EL SISTEMA DEBE dejar de mostrar ese usuario en el listado por defecto de usuarios activos  
Y el registro DEBE permanecer soft-deleted en base de datos (no hard delete obligatorio en F02).

### R5 — Alta de dirección asociada a un usuario

DONDE un administrador autenticado con acceso al panel gestiona las direcciones de un usuario existente,  
CUANDO envía un alta válida con nombre completo, teléfono, línea 1, ciudad, estado/departamento y país (código de 2 letras), y opcionalmente etiqueta, línea 2, código postal y marca de predeterminada,  
EL SISTEMA DEBE persistir la dirección asociada a ese usuario  
Y mostrarla en el listado de direcciones de ese usuario.

### R6 — Edición de dirección

DONDE un administrador autenticado con acceso al panel,  
CUANDO actualiza una dirección existente de un usuario con datos válidos,  
EL SISTEMA DEBE persistir los cambios y reflejarlos al reabrir la dirección o el listado del usuario.

### R7 — Eliminación de dirección

DONDE un administrador autenticado con acceso al panel,  
CUANDO elimina una dirección de un usuario,  
EL SISTEMA DEBE remover esa dirección del listado de direcciones de ese usuario  
Y no debe permanecer disponible para selección futura en el admin de ese usuario.

### R8 — Una sola dirección predeterminada por usuario

CUANDO se marca una dirección de un usuario como predeterminada (`is_default = true`),  
EL SISTEMA DEBE dejar como máximo una dirección con marca de predeterminada para ese usuario  
(si había otra predeterminada del mismo usuario, deja de serlo).

### R9 — Acceso de administrador autorizado

DONDE un usuario autenticado cuyo email está en la lista de administradores configurada,  
CUANDO accede al panel de administración,  
EL SISTEMA DEBE permitir el acceso a la gestión de usuarios y direcciones del panel.

---

### Validación y error — en alcance F02

### R10 — Acceso denegado para no administradores

DONDE un usuario autenticado cuyo email **no** está en la lista de administradores configurada, o un visitante no autenticado,  
CUANDO intenta acceder a la gestión de usuarios o direcciones del panel,  
EL SISTEMA DEBE denegar el acceso  
SIN permitir crear, editar o eliminar usuarios o direcciones.

### R11 — Validación de campos obligatorios de usuario

DONDE un administrador envía un alta o edición de usuario sin nombre, o sin email, o (en alta) sin contraseña,  
EL SISTEMA DEBE rechazar el guardado e informar los errores de validación  
SIN crear o actualizar el registro con esos campos obligatorios incompletos.

### R12 — Email de usuario único

CUANDO se intenta crear o actualizar un usuario con un email que ya pertenece a otro usuario,  
EL SISTEMA DEBE rechazar la operación e informar el conflicto de unicidad  
SIN sobrescribir el otro usuario.

### R13 — Validación de campos obligatorios de dirección

DONDE un administrador envía un alta o edición de dirección sin nombre completo, o sin teléfono, o sin línea 1, o sin ciudad, o sin estado/departamento, o sin país,  
EL SISTEMA DEBE rechazar el guardado e informar los errores de validación  
SIN persistir la dirección incompleta.

### R14 — País de dirección inválido

CUANDO se intenta persistir una dirección con un país que no es un código de 2 letras,  
EL SISTEMA DEBE rechazar la operación e informar el error de validación  
SIN guardar ese valor de país inválido.

---

### Fuera de DoD F02 — storefront de cuentas (documentado, no implementado)

> **Estado:** Fuera de alcance F02 por decisión de slice. No bloquea cierre.

- Login / registro / perfil / libreta de direcciones del comprador en Livewire.
- Self-service del usuario final sobre sus propias direcciones.
- Verificación de email como requisito de compra.
