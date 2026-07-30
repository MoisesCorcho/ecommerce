# Brief UI: Preguntas Frecuentes (FAQ)

> **Vista:** FAQ · **Ruta sugerida:** `/faq`
> **Depende de:** Ninguna (página estática)
> **Estado:** Lista para implementar

---

# Para Stitch (diseño visual)

> **Prompt para Google Stitch.** Pasar este bloque + `00-design-tokens.md`.

## Objetivo de la vista

Página de preguntas frecuentes. El usuario encuentra respuestas rápidas a sus dudas sin contactar al soporte. Sensación de ayuda clara y fácil de escanear.

## Estructura y layout

**Todos los breakpoints:**
- Breadcrumb arriba (`Inicio / FAQ`).
- Título "Preguntas Frecuentes" (Chillax Semibold, grande).
- Introducción breve (Montserrat Regular, 1–2 líneas).
- **Categorías como tabs horizontales** (Compras, Envíos, Pagos, Cambios y devoluciones, Cuenta de usuario). Tab activo destacado. En móvil, tabs scrollables horizontalmente.
- **Acordeón de preguntas** debajo de los tabs.
- CTA a Contacto al final.

**Ancho máximo del contenido:** 700–800px centrado.

## Componentes visuales

### Categorías (tabs)
- Tabs horizontales con nombre de categoría.
- Tab activo: fondo Intense Cocoa + texto Silk Cream, o borde inferior Soft Gold + texto Intense Cocoa SemiBold.
- Tabs inactivos: Intense Cocoa Regular con opacidad, hover Soft Gold.
- En móvil, scroll horizontal con indicador de más tabs (flecha o fade).
- Montserrat Medium.

### Acordeón de preguntas
- Cada pregunta como fila expandible.
- **Pregunta**: Montserrat Medium, Intense Cocoa, padding generoso (16–20px vertical).
- **Ícono** de `+`/`−` o flecha a la derecha (Intense Cocoa, rota al expandir).
- **Respuesta**: Montserrat Regular, Intense Cocoa con opacidad, padding horizontal + bottom, animación suave de expansión.
- Solo una pregunta abierta a la vez (dentro de la categoría activa).
- Separadores sutiles (Soft Sand) entre preguntas.
- Fondo Silk Cream, card con sombra suave opcional.

### CTA a Contacto
- Al final de la página, card o banner:
  - "¿No encontraste lo que buscabas?" (Chillax Semibold o Montserrat Medium).
  - Botón "Contáctanos" (Intense Cocoa, borde o fondo) que lleva a `/contact`.
  - Fondo Soft Sand para contraste.

## Paleta de colores (ver `00-design-tokens.md`)

- Fondo: **Silk Cream** `#FFF8CF`
- Contraste (dividers, CTA card): **Soft Sand** `#E9DED3`
- Texto, botones, íconos: **Intense Cocoa** `#372621`
- Hover, activos, detalles: **Soft Gold** `#D2AE36`
- Proporción 70/20/10.

## Tipografía (ver `00-design-tokens.md`)

- Título, CTA: **Chillax** Semibold.
- Preguntas, tabs, botones: **Montserrat** Medium.
- Respuestas, introducción, cuerpo: **Montserrat** Regular.

## Estilo visual

- Limpio, fácil de escanear. Sensación de ayuda rápida.
- Minimalista. Mucho espacio en blanco.
- Acordeón cómodo, no amontonado.
- Categorías claras y navegables.
- Consistente con la identidad de marca.
- Iconografía lineal (íconos de +/− o flechas).

## Estados

- **Pregunta cerrada**: ícono `+` o flecha derecha.
- **Pregunta abierta**: ícono `−` o flecha abajo, respuesta visible con animación.
- **Hover en pregunta**: fondo Soft Sand sutil o texto a SemiBold.
- **Categoría vacía**: no aplica (estático), pero por robustez mostrar "No hay preguntas en esta categoría.".
- **Tab activo**: destacado (ver arriba).

## Breakpoints

El layout es el mismo en todos los breakpoints (una columna centrada, ancho máximo 700–800px). Solo varían los tabs (scroll horizontal en móvil). Ver `00-design-tokens.md` sección Responsive.

---

# Para implementación (NO pasar a Stitch)

## Contexto del proyecto

- **Página estática** (D1): las preguntas, respuestas y categorías viven en Blade hardcodeadas. **No** son editables desde el panel admin.
- Cambios en el contenido requieren editar las plantillas Blade.
- No hay modelo de datos para FAQ — todo es estático.

## Acciones del usuario

El usuario podrá:

- Explorar las categorías disponibles.
- Expandir una pregunta.
- Contraer una pregunta.
- Cambiar de categoría.
- Acceder a la página de Contacto.

## Validaciones

- Mantener una única pregunta expandida a la vez (dentro de la categoría activa).

## Datos requeridos

**Estático (en Blade):** categorías, preguntas, respuestas.

## Consideraciones técnicas

- El acordeón puede implementarse con Alpine.js (incluido con Livewire) o CSS puro (`<details>`/`<summary>`).
- Carga rápida (contenido estático, sin consultas a base de datos).
- Responsive: desktop-first con experiencia móvil equivalente (ver `00-design-tokens.md`).
- Sin dependencias de backend — todo en Blade.

## Fuera de alcance (diferido)

- **Buscador de preguntas** — mejora futura.
- **Preguntas destacadas** — mejora futura.
- **Administración desde el panel** — requiere CMS (no planeado, D1).
- **Enlaces a artículos de ayuda relacionados** — mejora futura.
