# Brief UI: Registro

> **Vista:** Registro · **Ruta sugerida:** `/register`
> **Depende de:** F02 (cuentas y direcciones)
> **Estado:** Pendiente de F02

---

# Para Stitch (diseño visual)

> **Prompt para Google Stitch.** Pasar este bloque + `00-design-tokens.md`.

## Objetivo de la vista

Página de creación de cuenta para nuevos usuarios. Sensación de bienvenida, simplicidad y confianza. Consistente con Login.

## Estructura y layout

**Todos los breakpoints:**
- Página centrada vertical y horizontalmente.
- Card de formulario con padding generoso, ancho máximo 450–500px.
- Fondo de página: Silk Cream o imagen sutil de marca (consistente con Login).
- Logo de la marca arriba del formulario (variante Brown sobre Silk Cream).

## Componentes visuales

### Card de formulario
- Fondo Silk Cream o blanco.
- Sombra suave.
- Padding generoso (32–40px).

### Campos
- **Nombre completo**: input con label sobre el campo, ícono de usuario a la izquierda.
- **Correo electrónico**: input con ícono de sobre.
- **Contraseña**: input con ícono de candado + toggle de visibilidad (ojo).
- **Indicador de fortaleza**: barra de color debajo del campo de contraseña (rojo → amarillo → verde) + texto descriptivo ("Débil", "Media", "Fuerte").
- **Confirmar contraseña**: input con ícono de candado + toggle de visibilidad.
- **Número de teléfono** (opcional): input con ícono de teléfono.
- Bordes Intense Cocoa sutil, fondo Silk Cream.
- Focus: borde Soft Gold.
- Error: borde rojo + mensaje debajo.

### Términos y condiciones
- Checkbox con texto: "Acepto los [Términos y Condiciones] y la [Política de Privacidad]" (enlaces en línea, Intense Cocoa, hover Soft Gold).
- Checkbox en Intense Cocoa, checked con fondo Intense Cocoa y check Silk Cream o Soft Gold.
- Si no se acepta, botón "Crear cuenta" deshabilitado o error al submit.

### Botón "Crear cuenta"
- Ancho completo, fondo Intense Cocoa, texto Silk Cream, Montserrat SemiBold.
- Hover: Soft Gold.
- Estado de loading: spinner + "Creando cuenta...".

### Enlace "Iniciar sesión"
- "¿Ya tienes cuenta? Iniciar sesión" debajo del formulario, centrado, Montserrat Medium, Intense Cocoa, hover Soft Gold.

### Error de email duplicado
- Banner sutil (fondo Soft Sand, borde Soft Gold, texto Intense Cocoa): "Este correo ya está registrado. ¿Quieres iniciar sesión?" con enlace.

## Paleta de colores (ver `00-design-tokens.md`)

- Fondo: **Silk Cream** `#FFF8CF` (o imagen de marca).
- Card: Silk Cream o blanco.
- Texto, botones, íconos: **Intense Cocoa** `#372621`
- Hover, focus, detalles: **Soft Gold** `#D2AE36`
- Contraste (banner error): **Soft Sand** `#E9DED3`
- Indicador de fortaleza: rojo (`#B33A3A` aprox) → amarillo (`#D2AE36`) → verde (`#5A8A4A` aprox). Mantener tonos cálidos consistentes con la paleta.

## Tipografía (ver `00-design-tokens.md`)

- Logo: **Chillax** Medium.
- Título (si existe): **Chillax** Semibold.
- Labels, botones, enlaces: **Montserrat** Medium/SemiBold.
- Inputs, cuerpo, texto de términos: **Montserrat** Regular.

## Estilo visual

- Consistente con Login.
- Sensación de bienvenida, no de formulario frío.
- Logo de la marca como ancla visual.
- Sin distracciones. Un solo objetivo: crear cuenta.
- Mucho espacio en blanco dentro de la card.
- El indicador de fortaleza agrega valor sin ser invasivo.

## Estados

- **Campos vacíos**: validación al submit o en tiempo real.
- **Formato inválido**: borde rojo + mensaje.
- **Contraseña débil**: indicador en rojo + sugerencia.
- **Contraseñas no coinciden**: borde rojo en confirmar + mensaje.
- **Email duplicado**: banner con CTA a login.
- **Términos no aceptados**: botón deshabilitado o error.
- **Loading**: spinner en botón, inputs deshabilitados.
- **Éxito**: redirección a home (sesión iniciada automáticamente).

## Breakpoints

La página es la misma en todos los breakpoints (centrada). Solo varía el ancho de la card (máximo 450–500px) y padding en móvil. Ver `00-design-tokens.md` sección Responsive.

---

# Para implementación (NO pasar a Stitch)

## Contexto del proyecto

- El modelo `User` tiene campo `name` (único, no separado en nombre + apellidos), `email`, `password`, `phone`.
- **Sin registro social** en la primera versión.
- **Términos y condiciones:** se incluirá checkbox de aceptación como obligatorio. El texto legal vive en Blade estático (D1).
- Tras el registro, iniciar sesión automáticamente.

## Acciones del usuario

El usuario podrá:

- Registrar una nueva cuenta.
- Aceptar los términos y condiciones (obligatorio).
- Acceder al formulario de inicio de sesión.

## Validaciones

- Validar que todos los campos obligatorios hayan sido completados.
- Validar el formato del correo electrónico.
- Verificar que el correo electrónico no se encuentre registrado.
- Validar la fortaleza mínima de la contraseña (mínimo 8 caracteres, al menos una mayúscula y un número).
- Verificar que ambas contraseñas coincidan.
- Impedir el registro si no se aceptan los términos y condiciones.

## Datos requeridos

**Entrada (formulario):** nombre completo, correo electrónico, contraseña, confirmación de contraseña, aceptación de términos (boolean), número de teléfono (opcional).

## Consideraciones técnicas

- Las contraseñas se cifran con el sistema nativo de Laravel (bcrypt).
- Validar la información tanto en el cliente como en el servidor.
- Mostrar mensajes de validación claros.
- Iniciar sesión automáticamente después del registro.
- Responsive: desktop-first con experiencia móvil equivalente (ver `00-design-tokens.md`).
- Usar el sistema de registro nativo de Laravel.

## Fuera de alcance (diferido)

- **Verificación de correo electrónico** — mejora futura.
- **Registro social** — Google, Facebook, Apple. Fuera de la primera versión.
- **Suscripción al boletín de noticias durante el registro** — mejora futura.
- **Información adicional del perfil al registrarse** — el perfil se completa después en `/profile`.
