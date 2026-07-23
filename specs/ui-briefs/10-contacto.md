# Brief UI: Contacto

> **Vista:** Contacto · **Ruta sugerida:** `/contact`
> **Depende de:** Ninguna (página estática)
> **Estado:** Lista para implementar

---

# Para Stitch (diseño visual)

> **Prompt para Google Stitch.** Pasar este bloque + `00-design-tokens.md`.

## Objetivo de la vista

Página de contacto. El usuario encuentra canales de comunicación y puede enviar un mensaje. Sensación de "estamos para ayudarte", acogedora y accesible.

## Estructura y layout

**Desktop (layout principal):**
- Breadcrumb arriba (`Inicio / Contacto`).
- Título "Contáctanos" o "Hablemos" (Chillax Semibold, grande).
- **Dos columnas:**
  - Izquierda (40%): información de contacto (canales).
  - Derecha (60%): formulario de contacto.
- CTA a FAQ al final.

**Tablet:**
- Dos columnas más equilibradas o una columna.

**Móvil:**
- Una sola columna: info arriba, formulario abajo.

## Componentes visuales

### Información de contacto (columna izquierda)
- Fondo Soft Sand o Silk Cream con bordes sutiles.
- Lista de canales con íconos lineales (Intense Cocoa) + texto:
  - **Email**: ícono sobre + email de la marca (enlace `mailto`).
  - **Teléfono**: ícono teléfono + número (enlace `tel`).
  - **WhatsApp**: ícono WhatsApp + número (enlace `wa.me`).
  - **Redes sociales**: íconos circulares (Instagram, Facebook, etc.).
  - **Horario de atención**: ícono reloj + texto.
- Montserrat Regular/Medium.

### Formulario de contacto (columna derecha)
- Campos: nombre, correo electrónico, asunto, mensaje.
- Labels sobre inputs, bordes Intense Cocoa sutil, focus Soft Gold.
- Textarea amplio para el mensaje con contador de caracteres (máx 1000).
- Botón "Enviar mensaje" ancho completo o alineado a la derecha, fondo Intense Cocoa, texto Silk Cream, hover Soft Gold.
- Estado de loading: spinner + "Enviando...".

### Confirmación de envío
- Tras enviar, el formulario se reemplaza o muestra encima un mensaje de éxito:
  - Card con fondo Soft Sand, ícono check (Soft Gold o verde sutil), texto "Tu mensaje fue enviado. Te responderemos pronto." (Chillax Semibold o Montserrat Medium).
- El formulario se limpia.

### CTA a FAQ
- Al final de la página, card o banner sutil:
  - "¿Tienes dudas? Revisa nuestras preguntas frecuentes" + botón "Ver FAQ" (Intense Cocoa, borde) que lleva a `/faq`.

## Paleta de colores (ver `00-design-tokens.md`)

- Fondo: **Silk Cream** `#FFF8CF`
- Contraste (info de contacto, confirmación): **Soft Sand** `#E9DED3`
- Texto, botones, íconos: **Intense Cocoa** `#372621`
- Hover, focus, detalles, check de éxito: **Soft Gold** `#D2AE36`
- Proporción 70/20/10.

## Tipografía (ver `00-design-tokens.md`)

- Título, confirmación: **Chillax** Semibold.
- Labels, botones, enlaces: **Montserrat** Medium/SemiBold.
- Inputs, info de contacto, cuerpo: **Montserrat** Regular.

## Estilo visual

- Acogedor, accesible, sensación de ayuda.
- Consistente con la identidad de marca (premium, minimalista).
- Mucho espacio en blanco.
- Iconografía lineal, Intense Cocoa.
- Sin distracciones. El usuario debe poder contactar rápido.

## Estados

- **Campos vacíos**: validación al submit o en tiempo real.
- **Formato inválido**: borde rojo + mensaje.
- **Mensaje muy largo**: contador en rojo + bloqueo.
- **Loading**: spinner en botón, inputs deshabilitados.
- **Éxito**: card de confirmación + formulario limpio.
- **Error de envío**: banner sutil "Hubo un problema. Intenta de nuevo o escríbenos a <email>.".

## Breakpoints

Diseñar primero para **desktop** (`lg`/`xl`) con dos columnas. En tablet (`md`) y móvil (`sm`), una columna. Ver `00-design-tokens.md` sección Responsive.

---

# Para implementación (NO pasar a Stitch)

## Contexto del proyecto

- **Información de contacto estática** (D1): la información (email, teléfono, WhatsApp, redes sociales, horario) vive en Blade hardcodeada. No es editable desde el panel admin.
- **Formulario de contacto:** se implementa como **componente Livewire MFC** (`app/Livewire/ContactForm.php`) que envía un email al correo de la marca usando el sistema de mail de Laravel (configurado en `.env`). No se guarda en base de datos en la primera versión. Validación en tiempo real y feedback sin recarga.
- Sin integración con CRM ni sistema de tickets.

## Acciones del usuario

El usuario podrá:

- Enviar un mensaje vía formulario.
- Contactar mediante WhatsApp (enlace externo).
- Enviar un correo electrónico (enlace `mailto`).
- Acceder a las redes sociales (enlaces externos).
- Consultar las preguntas frecuentes (link a `/faq`).

## Validaciones

- Validar que todos los campos obligatorios hayan sido completados.
- Validar el formato del correo electrónico.
- Limitar la longitud del mensaje (máximo 1000 caracteres).
- Mostrar un mensaje de confirmación cuando el formulario sea enviado correctamente.

## Datos requeridos

**Entrada (formulario):** nombre, correo electrónico, asunto, mensaje.

**Estático (en Blade):** email, teléfono, WhatsApp, redes sociales, horario.

## Consideraciones técnicas

- Validar la información tanto en el cliente como en el servidor.
- Proteger el formulario contra envíos automatizados (rate limiting o captcha básico).
- El formulario envía un email usando Laravel Mail (configurado en `.env` con Mailpit en local).
- Confirmar visualmente el envío exitoso del mensaje.
- Responsive: desktop-first con experiencia móvil equivalente (ver `00-design-tokens.md`).

## Fuera de alcance (diferido)

- **Adjuntar archivos al mensaje** — mejora futura.
- **Selección del tipo de solicitud** — mejora futura.
- **Integración con sistema de tickets** — mejora futura.
- **Integración con chat en vivo** — mejora futura.
- **Guardado de mensajes en base de datos** — mejora futura.
- **Administración de mensajes desde el panel** — requiere CMS (no planeado).
