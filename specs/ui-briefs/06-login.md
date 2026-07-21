# Brief UI: Login

> **Vista:** Login · **Ruta sugerida:** `/login`
> **Depende de:** F02 (cuentas y direcciones)
> **Estado:** Pendiente de F02

---

# Para Stitch (diseño visual)

> **Prompt para Google Stitch.** Pasar este bloque + `00-design-tokens.md`.

## Objetivo de la vista

Página de acceso para usuarios con cuenta. Sensación de seguridad, simplicidad y confianza.

## Estructura y layout

**Todos los breakpoints:**
- Página centrada vertical y horizontalmente.
- Card de formulario con padding generoso, ancho máximo 400–450px.
- Fondo de página: Silk Cream o imagen sutil de marca (lifestyle, textura de cuero, etc.).
- Logo de la marca arriba del formulario (variante Brown sobre Silk Cream, o White sobre imagen de fondo).

## Componentes visuales

### Card de formulario
- Fondo Silk Cream (si la página tiene imagen de fondo) o blanco/Silk Cream sutil.
- Sombra suave.
- Bordes discretos o sin bordes (sombra define la card).
- Padding generoso (32–40px).

### Campos
- **Correo electrónico**: input con label sobre el campo, ícono de sobre a la izquierda (Intense Cocoa).
- **Contraseña**: input con label, ícono de candado a la izquierda, toggle de visibilidad (ojo) a la derecha.
- Bordes Intense Cocoa sutil, fondo Silk Cream.
- Focus: borde Soft Gold.
- Error: borde rojo + mensaje debajo.

### Botón "Iniciar sesión"
- Ancho completo, fondo Intense Cocoa, texto Silk Cream, Montserrat SemiBold.
- Hover: Soft Gold.
- Estado de loading: spinner + texto "Iniciando sesión...".

### Enlaces
- "¿Olvidaste tu contraseña?" debajo del botón, alineado a la izquierda o centro, Montserrat Medium, Intense Cocoa, hover Soft Gold.
- "¿No tienes cuenta? Crear una" debajo, centrado, Montserrat Medium, Intense Cocoa, hover Soft Gold.

### Error de credenciales
- Banner sutil en la parte superior del formulario (fondo Soft Sand, borde Soft Gold, texto Intense Cocoa): "Las credenciales no son correctas. Verifica tu email y contraseña.".

## Paleta de colores (ver `00-design-tokens.md`)

- Fondo: **Silk Cream** `#FFF8CF` (o imagen de marca con overlay Intense Cocoa).
- Card: Silk Cream o blanco.
- Texto, botones, íconos: **Intense Cocoa** `#372621`
- Hover, focus, detalles: **Soft Gold** `#D2AE36`
- Contraste (banner error): **Soft Sand** `#E9DED3`

## Tipografía (ver `00-design-tokens.md`)

- Logo: **Chillax** Medium.
- Título (si existe, ej: "Iniciar sesión"): **Chillax** Semibold.
- Labels, botones, enlaces: **Montserrat** Medium/SemiBold.
- Inputs, cuerpo: **Montserrat** Regular.

## Estilo visual

- Limpio, minimalista, sensación de seguridad.
- Logo de la marca como ancla visual.
- Sin distracciones. Un solo objetivo: iniciar sesión.
- Consistente con la identidad premium de Leen Handbags.
- Mucho espacio en blanco dentro de la card.

## Estados

- **Campos vacíos**: botón deshabilitado o validación al submit.
- **Formato inválido**: borde rojo + mensaje en tiempo real.
- **Credenciales incorrectas**: banner de error general.
- **Loading**: spinner en botón, inputs deshabilitados.
- **Éxito**: redirección a página previa o home.

## Breakpoints

La página es la misma en todos los breakpoints (centrada). Solo varía el ancho de la card (máximo 400–450px). En móvil, padding de la card ligeramente menor. Ver `00-design-tokens.md` sección Responsive.

---

# Para implementación (NO pasar a Stitch)

## Contexto del proyecto

- Laravel maneja la autenticación nativamente. El modelo `User` tiene `email`, `password` (hashed), `name`, `phone`.
- **Sin login social** en la primera versión (no Google, no Facebook, no Apple).
- El acceso al panel admin es separado (Filament en `/admin`) y se controla por `config('ecommerce.admin_emails')`, no por esta vista.
- Sin campo `is_active` en `User` por ahora — la cuenta no se "bloquea" desde el admin.

## Acciones del usuario

El usuario podrá:

- Iniciar sesión con email y contraseña.
- Recuperar su contraseña (link a flujo de recuperación).
- Acceder al formulario de registro.

## Validaciones

- Validar que todos los campos obligatorios hayan sido completados.
- Validar el formato del correo electrónico.
- Mostrar un mensaje genérico cuando las credenciales sean incorrectas (sin revelar qué campo falló).
- Redirigir al usuario a la página correspondiente después de iniciar sesión (página previa o home).

## Datos requeridos

**Entrada (formulario):** correo electrónico, contraseña.

## Consideraciones técnicas

- No almacenar contraseñas en el navegador (autocomplete off en campo de contraseña).
- Conexión segura (HTTPS) durante la autenticación.
- Mostrar mensajes de error sin revelar información sensible (no decir "email no existe" o "contraseña incorrecta" por separado).
- Redirigir al usuario a la página previa o al home después de iniciar sesión.
- Responsive: desktop-first con experiencia móvil equivalente (ver `00-design-tokens.md`).
- Usar el sistema de autenticación nativo de Laravel.

## Fuera de alcance (diferido)

- **Recordar sesión** — mejora futura (checkbox "Recordarme").
- **Login social** — Google, Facebook, Apple. Fuera de la primera versión.
- **Autenticación en dos pasos (2FA)** — mejora futura.
- **Bloqueo de cuenta inactiva** — no hay campo `is_active` en el modelo.
