# Contacto

> **Estado:** Completa
> **ID:** N/A · **Slug:** `contacto` (página utilitaria de storefront, fuera de la secuencia F0N del roadmap)
> **Prerequisitos:** Ninguna (página estática) — ver [`ui-briefs/10-contacto.md`](../../ui-briefs/10-contacto.md)
> **Desbloquea:** —

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Brief visual y de producto | [`specs/ui-briefs/10-contacto.md`](../../ui-briefs/10-contacto.md) |
| Tokens de diseño | [`specs/ui-briefs/00-design-tokens.md`](../../ui-briefs/00-design-tokens.md) |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Convenciones de código (Actions, DTOs, i18n) | `AGENTS.md` / project-conventions |
| Layout storefront existente | `resources/views/layouts/storefront.blade.php` |
| Propuesta SDD de esta feature | Engram `sdd/contacto/proposal` |

> Nota de partida: los enlaces de "Contacto" en header, nav móvil y footer ya existen y apuntan a `/contact`, pero la ruta no está registrada — cada entrada da 404. El footer también enlaza `/faqs` (inexistente); la ruta correcta es `/faq`.

## User stories

1. **Como** visitante, **quiero** ver la información de contacto de la marca (correo, teléfono, WhatsApp, redes sociales, horario), **para** comunicarme por el canal que prefiera sin llenar un formulario.
2. **Como** visitante o comprador, **quiero** enviar un mensaje mediante un formulario de contacto, **para** consultar dudas o reportar un problema sin salir del sitio.
3. **Como** visitante, **quiero** recibir confirmación visual de que mi mensaje fue enviado, **para** saber que la marca lo recibirá.
4. **Como** visitante, **quiero** ver un mensaje de error claro con una alternativa si el envío falla, **para** no perder mi intento de contacto.
5. **Como** comprador autenticado, **quiero** que mi nombre y correo aparezcan prellenados en el formulario (pero editables), **para** no volver a escribirlos.
6. **Como** visitante, **quiero** acceder rápido a las preguntas frecuentes desde la página de contacto, **para** resolver dudas comunes sin enviar un mensaje.
7. **Como** negocio, **quiero** limitar los envíos automatizados o abusivos del formulario, **para** proteger el buzón de spam.

## Alcance de esta feature

**Incluye:**

- Ruta pública `/contact` con página Livewire (breadcrumb, título, dos columnas 40/60 en desktop, una columna en tablet/móvil).
- Columna de información de contacto estática (email, teléfono, WhatsApp, redes sociales, horario) con valores **placeholder** claramente identificables como tales en el código (ver D2).
- Formulario de contacto (nombre, correo, asunto, mensaje ≤1000 caracteres con contador en vivo), validado en cliente y servidor.
- Envío de correo al buzón configurado por entorno, con `Reply-To` del remitente.
- Rate limiting por IP para el envío del formulario.
- Estados de UI: vacío/validación, carga, éxito (confirmación + reset), error de envío con `mailto` de respaldo.
- CTA final a `/faq`.
- Activación de los enlaces de "Contacto" ya presentes en header/nav móvil/footer; corrección del enlace de FAQ del footer (`/faqs` → `/faq`).
- Nuevo dominio i18n `lang/{en,es}/contact.php` para toda la copy de la página.

**No incluye (diferido, según brief):**

- Adjuntar archivos al mensaje.
- Selector del tipo de solicitud.
- Integración con sistema de tickets.
- Integración con chat en vivo.
- Guardado de mensajes en base de datos (ni como fallback ante fallo de envío — ver D3).
- Administración de mensajes desde el panel admin (requiere CMS, no planeado).
- Información de contacto editable desde el panel admin.
- Construcción de la página `/faq` en sí (feature separada, ver `ui-briefs/11-faq.md`).

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | Buzón de destino | Env-driven vía `CONTACT_MAIL_TO` (`.env`/`.env.example`); el valor real lo define el usuario más adelante. El sistema debe requerir y usar esta config, sin hardcodear una dirección. |
| D2 | Datos de contacto estáticos | Se publican con valores **placeholder** explícitamente marcados como tales; el usuario los reemplazará luego sin nuevo ciclo SDD. La ausencia de valores "reales" no bloquea esta feature. |
| D3 | Sin persistencia ante fallo de envío | Si el envío de correo falla, no hay tabla/migración de respaldo (mantiene el D1 original del brief). Se muestra un banner de error con `mailto` de respaldo y se registra el fallo en el log del servidor. |
| D4 | `Reply-To` del correo saliente | Igual al correo ingresado por el remitente, para permitir responder directo desde el buzón. |
| D5 | Prellenado para usuario autenticado | Nombre y correo se prellenan desde la sesión si el usuario está autenticado; ambos campos permanecen editables. |

---

## Criterios de aceptación (EARS)

### Happy path

### R1 — Visualización de información de contacto estática

CUANDO cualquier visitante accede a `/contact`,
EL SISTEMA DEBE mostrar la columna de información de contacto (correo con enlace `mailto`, teléfono con enlace `tel`, WhatsApp con enlace `wa.me`, redes sociales, horario de atención) con los valores placeholder configurados en Blade (D2).

### R2 — Layout responsive de la página

DONDE el visitante accede desde desktop (`lg`/`xl`),
EL SISTEMA DEBE mostrar la información de contacto y el formulario en dos columnas (40/60);
DONDE accede desde tablet o móvil (`md`/`sm`),
EL SISTEMA DEBE mostrar una sola columna con la información arriba y el formulario debajo.

### R3 — Envío exitoso del formulario

CUANDO un visitante completa nombre, correo, asunto y mensaje válidos y envía el formulario,
EL SISTEMA DEBE validar en servidor, enviar un correo al buzón definido en `CONTACT_MAIL_TO` con `Reply-To` igual al correo del remitente (D4), mostrar una card de confirmación de éxito
Y DEBE limpiar el formulario.

### R4 — Prellenado para comprador autenticado

CUANDO un comprador autenticado accede a `/contact`,
EL SISTEMA DEBE prellenar los campos nombre y correo con los datos de su sesión,
SIN impedir que el comprador edite ambos campos antes de enviar.

### R5 — Contador de caracteres del mensaje

MIENTRAS el visitante escribe en el campo mensaje,
EL SISTEMA DEBE mostrar un contador de caracteres en vivo con límite de 1000.

### R6 — Estado de carga durante el envío

CUANDO el visitante envía el formulario,
EL SISTEMA DEBE mostrar un estado de carga (spinner + texto "Enviando...")
Y DEBE deshabilitar los campos del formulario hasta que el envío finalice.

### R7 — CTA a preguntas frecuentes

CUANDO el visitante llega al final de la página de contacto,
EL SISTEMA DEBE mostrar un banner o card con un enlace hacia `/faq`.

### R8 — Enlaces de navegación al contacto

DONDE el visitante está en el header, el nav móvil o el footer del storefront,
CUANDO hace clic en el enlace "Contacto",
EL SISTEMA DEBE llevarlo a `/contact` con respuesta 200, en lugar de un 404.

### R9 — Corrección del enlace de FAQ en el footer

CUANDO el visitante hace clic en el enlace de preguntas frecuentes del footer,
EL SISTEMA DEBE llevarlo a `/faq`
SIN apuntar a la ruta inexistente `/faqs`.

### Validación y error

### R10 — Campos obligatorios vacíos

CUANDO el visitante envía el formulario con nombre, correo, asunto o mensaje vacío,
EL SISTEMA DEBE rechazar el envío y mostrar el error de validación junto a cada campo afectado,
SIN enviar ningún correo.

### R11 — Formato de correo inválido

CUANDO el correo ingresado no tiene formato válido,
EL SISTEMA DEBE rechazar el envío con un mensaje de error localizado,
SIN enviar ningún correo.

### R12 — Mensaje excede el límite de caracteres

CUANDO el mensaje ingresado supera 1000 caracteres,
EL SISTEMA DEBE bloquear el envío y mostrar el contador en rojo con el mensaje de error correspondiente.

### R13 — Rate limiting por IP

CUANDO una misma dirección IP excede el límite configurado de envíos dentro de la ventana de tiempo definida,
EL SISTEMA DEBE rechazar los envíos adicionales y mostrar un mensaje localizado indicando el tiempo de espera restante,
SIN enviar correo ni contar el intento bloqueado como un nuevo envío exitoso.

### R14 — Falla en el envío de correo

CUANDO el envío del correo falla (excepción del transporte de mail),
EL SISTEMA DEBE registrar el fallo en el log del servidor y mostrar un banner de error con un enlace `mailto` de respaldo hacia el correo de contacto,
SIN persistir el mensaje en base de datos (D3).

### R15 — Validación en cliente y servidor

CUANDO el visitante interactúa con el formulario,
EL SISTEMA DEBE validar en tiempo real en el cliente (Livewire)
Y DEBE revalidar en servidor antes de procesar el envío, de modo que la validación de cliente nunca sea la única barrera.

### R16 — Copy localizada

CUANDO se renderiza cualquier texto de la página de contacto (labels, mensajes de validación, estados, CTA),
EL SISTEMA DEBE resolverlo desde el dominio de traducción `lang/{en,es}/contact.php`,
SIN cadenas hardcodeadas en español o inglés.

---

## Mapa de pruebas (orientativo)

| Área | Escenarios mínimos |
|------|-------------------|
| Ruta y navegación | `/contact` responde 200 para guest y usuario autenticado; enlaces de header/nav móvil/footer resuelven; footer ya no apunta a `/faqs` |
| Formulario — happy path | envío válido dispara `Mail::assertSent` con `to` = `CONTACT_MAIL_TO` y `replyTo` = correo del remitente; card de éxito; formulario reseteado; prellenado de nombre/correo para usuario autenticado |
| Formulario — validación | campos vacíos; correo con formato inválido; mensaje > 1000 caracteres — todos sin `Mail::assertSent` |
| Rate limiting | N+1 envíos desde la misma IP dentro de la ventana bloquean el envío y no disparan `Mail::assertSent` |
| Falla de envío | `Mail::fake()`/excepción simulada del gateway → banner de error con `mailto`, log de fallo, sin registro en BD |
| i18n | copy visible en `en` y `es` sin cadenas hardcodeadas |

---

## Definition of Done (producto)

- [ ] R1–R16 cubiertos por tests (feature test `ContactPageTest`).
- [ ] `CONTACT_MAIL_TO` documentado en `.env.example`; sin dirección hardcodeada en código.
- [ ] `lang/{en,es}/contact.php` completo para toda la copy nueva de esta feature.
- [ ] Enlaces de contacto activos en header/nav móvil/footer; footer corregido de `/faqs` a `/faq`.
- [ ] Pint + tests Sail del alcance en verde.
