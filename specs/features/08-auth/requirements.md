# F08 — Auth (autenticación de cliente storefront)

> **Estado:** Completa
> **ID:** F08 · **Slug:** `08-auth`
> **Prerequisitos:** F01 (catálogo), F02 (cuentas — admin) — ver [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)
> **Desbloquea:** F09 (cuenta/perfil del comprador), F10 (wishlist — requiere usuario identificado)

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Producto y alcance F08 | [`specs/_global/01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md) |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Convenciones de código | [`AGENTS.md`](../../../AGENTS.md) / project-conventions |
| Esquema de dominio | `app/Models/User.php`, `config/auth.php`, `database/migrations/0001_01_01_000000_create_users_table.php` |
| Patrón guest/user ya existente | `app/Models/Cart.php`, `app/Listeners/Cart/MergeGuestCartOnLoginListener.php` (carrito ya soporta invitado + usuario) |
| Alcance excluido en F02 | `specs/features/02-accounts-addresses/requirements.md` §"Fuera de DoD F02 — storefront de cuentas" |
| Storefront existente | `resources/views/components/checkout-page/`, `layouts/storefront.blade.php` |

## User stories

1. **Como** visitante del storefront, **quiero** crear una cuenta con nombre, email y contraseña, **para** acceder a funciones que requieren identidad.
2. **Como** cliente registrado, **quiero** iniciar sesión y cerrar sesión, **para** controlar el acceso a mi cuenta.
3. **Como** cliente que olvidó su contraseña, **quiero** restablecerla vía un enlace enviado a mi email, **para** recuperar el acceso sin intervención manual.
4. **Como** negocio, **quiero** que ciertas acciones (wishlist, reseñas, checkout autenticado) requieran email verificado, **para** reducir cuentas falsas y contenido no confiable.
5. **Como** comprador, **quiero** seguir pudiendo comprar sin crear cuenta, **para** que la fricción de checkout no aumente.
6. **Como** administrador existente, **quiero** conservar mi acceso al panel Filament durante y después de esta migración, **para** no quedar bloqueado el día del despliegue.

## Alcance de esta feature

**Incluye:**

- Registro de cliente (nombre, email, contraseña + confirmación, aceptación de términos).
- Inicio y cierre de sesión de cliente.
- Verificación de email, con enlace reenviable.
- Restablecimiento de contraseña (solicitud + confirmación por enlace).
- Asignación de rol: todo registro público obtiene el rol **cliente**; el rol **administrador** nunca se autoasigna.
- Migración del acceso al panel administrativo: de lista de correos (`admin_emails`) a un rol asignado explícitamente, sin interrumpir el acceso de administradores existentes.
- Limitación de intentos de inicio de sesión (protección contra fuerza bruta).
- Mensajes de error en español (i18n) para los flujos de esta feature.

**No incluye (F08):**

- Perfil, libreta de direcciones, historial de pedidos, gestión de reseñas propias del comprador (F09).
- Wishlist (F10) — depende de esta feature pero se especifica aparte.
- Cambios al checkout de invitado: sigue aceptando compras sin cuenta, sin excepción.
- Vincular pedidos hechos como invitado a una cuenta creada después con el mismo email.
- Login social (Google u otros proveedores externos).
- Autenticación de dos factores (2FA).
- Confirmación de contraseña para acciones sensibles (no hay acciones que lo requieran hoy).

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | Alcance MVP | Registro, login, logout, verificación de email, reset de contraseña. Perfil/direcciones/pedidos/reseñas → F09. Wishlist → F10. |
| D2 | Checkout de invitado | Se mantiene sin cambios de comportamiento; comprar nunca exige cuenta. |
| D3 | Campos de registro | Nombre, email, contraseña, confirmación de contraseña, aceptación de términos. Teléfono queda fuera del registro (se recolecta en F09 si aplica). |
| D4 | Verificación de email | Bloquea acciones de compromiso (wishlist, reseñas, checkout autenticado). No bloquea login ni navegación general. |
| D5 | Rol por defecto | Todo registro público asigna el rol **cliente**. El rol **administrador** solo se asigna por una vía interna, nunca por autoservicio. |
| D6 | Acceso al panel admin | Pasa a evaluarse por rol asignado en vez de lista de correos. La asignación de rol a administradores existentes debe completarse **antes** de activar la nueva regla de acceso, para no bloquear a nadie. |
| D7 | Pedidos previos de invitado | Fuera de alcance vincularlos a una cuenta nueva por coincidencia de email. |
| D8 | Login social | Fuera de alcance. |
| D9 | Wishlist exige cuenta | Confirmado: wishlist (F10) requiere usuario identificado, sin modo invitado — es la razón de ser de esta feature. |

---

## Criterios de aceptación (EARS)

### Happy path

### R1 — Registro exitoso de nuevo cliente

CUANDO un visitante envía el formulario de registro con nombre, email no registrado previamente, contraseña válida, confirmación coincidente y aceptación de términos,
EL SISTEMA DEBE crear la cuenta con el rol **cliente**
Y DEBE dejar al visitante en sesión iniciada.

### R2 — Verificación de email

CUANDO un cliente hace clic en el enlace de verificación enviado a su email registrado,
EL SISTEMA DEBE marcar el email como verificado
Y DEBE habilitar las acciones de compromiso (wishlist, reseñas, checkout autenticado) para ese cliente.

### R3 — Inicio de sesión exitoso

CUANDO un cliente envía email y contraseña correctos,
EL SISTEMA DEBE iniciar sesión
Y, si existía un carrito de invitado en la sesión anterior, DEBE fusionarlo con el carrito del cliente.

### R4 — Cierre de sesión

CUANDO un cliente autenticado solicita cerrar sesión,
EL SISTEMA DEBE finalizar la sesión
Y DEBE tratarlo como invitado en las siguientes solicitudes.

### R5 — Solicitud de restablecimiento de contraseña

CUANDO un visitante solicita restablecer contraseña indicando un email registrado,
EL SISTEMA DEBE enviar un enlace de restablecimiento de un solo uso con vencimiento
Y NO DEBE revelar si el email existe o no en la respuesta visible.

### R6 — Restablecimiento de contraseña con enlace válido

CUANDO un visitante usa un enlace de restablecimiento vigente y define una nueva contraseña válida,
EL SISTEMA DEBE actualizar la contraseña
Y DEBE invalidar el enlace usado para evitar reutilización.

### R7 — Acceso al panel admin tras la migración de rol

DONDE un usuario tiene asignado el rol **administrador**,
CUANDO intenta acceder al panel administrativo,
EL SISTEMA DEBE concederle acceso,
SIN depender de la lista de correos previa.

### R8 — Formulario de auth con estilo de marca

DONDE un visitante está en las pantallas de registro, login o restablecimiento de contraseña del storefront,
CUANDO la página carga,
EL SISTEMA DEBE mostrarla con el mismo estilo visual del storefront existente (layout y componentes de marca).

### Validación y error

### R9 — Registro con email ya existente

CUANDO se intenta registrar con un email ya asociado a una cuenta,
EL SISTEMA DEBE rechazar la operación con un mensaje de error
SIN crear una cuenta duplicada.

### R10 — Registro con datos inválidos

CUANDO el formulario de registro se envía con contraseña que no cumple la política mínima, confirmación que no coincide, o sin aceptar los términos,
EL SISTEMA DEBE rechazar la operación
SIN crear la cuenta.

### R11 — Login con credenciales inválidas

CUANDO se envían email o contraseña incorrectos,
EL SISTEMA DEBE rechazar el inicio de sesión con un mensaje genérico
SIN indicar cuál de los dos campos es incorrecto.

### R12 — Intentos de login excesivos

CUANDO se superan los intentos de inicio de sesión permitidos para un mismo email/IP en una ventana de tiempo,
EL SISTEMA DEBE bloquear temporalmente nuevos intentos
E DEBE informar el bloqueo sin revelar información adicional sobre la cuenta.

### R13 — Acción de compromiso sin email verificado

CUANDO un cliente autenticado sin email verificado intenta usar wishlist, dejar una reseña, o completar un checkout autenticado,
EL SISTEMA DEBE denegar la acción
Y DEBE indicar que se requiere verificar el email, con opción de reenviar el enlace.

### R14 — Enlace de restablecimiento inválido o vencido

CUANDO se usa un enlace de restablecimiento de contraseña vencido o ya utilizado,
EL SISTEMA DEBE rechazar la operación
SIN modificar la contraseña.

### R15 — Usuario sin rol admin intenta acceder al panel

CUANDO un usuario autenticado sin el rol **administrador** intenta acceder al panel administrativo,
EL SISTEMA DEBE denegar el acceso.

### R16 — Checkout de invitado no se ve afectado

CUANDO un visitante sin cuenta completa el checkout como invitado,
EL SISTEMA DEBE procesar la compra exactamente como antes de esta feature,
SIN exigir registro ni inicio de sesión en ningún paso.

### R17 — Mensajes en español

CUANDO el sistema muestra un error o mensaje de estos flujos (registro, login, verificación, restablecimiento),
EL SISTEMA DEBE mostrarlo en español vía claves de localización, no copy hardcodeado.

---

## Mapa de pruebas (orientativo)

| Área | Escenarios mínimos |
|------|-------------------|
| Registro | datos válidos; email duplicado; contraseña inválida/no coincide; sin aceptar términos; rol asignado = cliente |
| Login/Logout | credenciales válidas; credenciales inválidas; throttle tras intentos excesivos; merge de carrito invitado→usuario al loguear |
| Verificación de email | enlace válido marca verificado; acción de compromiso bloqueada sin verificar; reenvío de enlace |
| Password reset | solicitud no revela existencia de email; enlace válido cambia contraseña; enlace vencido/usado rechazado |
| Acceso admin | usuario con rol admin accede; usuario sin rol admin denegado; administradores pre-existentes retienen acceso tras la migración |
| Regresión checkout invitado | compra completa sin cuenta sigue funcionando igual que antes |

---

## Definition of Done (producto)

- [ ] R1–R17 cubiertos por tests o verificación manual documentada.
- [ ] Ningún administrador existente pierde acceso al panel durante el despliegue.
- [ ] Checkout de invitado sin regresiones (test explícito).
- [ ] `lang/es/auth.php` y `lang/es/passwords.php` con traducciones (hoy solo existen en inglés).
- [ ] Roadmap F08 → **Completa** al cerrar implementación; F09 y F10 quedan desbloqueadas.
- [ ] Pint + tests Sail del alcance en verde.
