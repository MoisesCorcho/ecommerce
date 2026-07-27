# FAQ — Preguntas Frecuentes

> **Estado:** Completa
> **ID:** F11 · **Slug:** `11-faq` (página estática de storefront, fuera de la secuencia F0N del roadmap)
> **Prerequisitos:** Ninguna (página estática) — ver [`ui-briefs/11-faq.md`](../../ui-briefs/11-faq.md)
> **Desbloquea:** —

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Brief visual y de producto | [`specs/ui-briefs/11-faq.md`](../../ui-briefs/11-faq.md) |
| Tokens de diseño | [`specs/ui-briefs/00-design-tokens.md`](../../ui-briefs/00-design-tokens.md) |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Convenciones de código (Actions, DTOs, i18n) | `AGENTS.md` / project-conventions |
| Layout storefront existente | `resources/views/layouts/storefront.blade.php` |
| Página de contacto (patrón de referencia) | `resources/views/components/contact-page/` |

> Nota de partida: el footer del storefront ya enlaza `/faq` y la página de contacto incluye un CTA a `/faq`. Ambos actualmente dan 404 porque la ruta no está registrada.

## User stories

1. **Como** visitante, **quiero** ver las preguntas frecuentes organizadas por categoría, **para** encontrar rápidamente la respuesta a mi duda sin contactar soporte.
2. **Como** visitante, **quiero** expandir y contraer preguntas individualmente, **para** leer solo las que me interesan sin distraerme con el resto.
3. **Como** visitante, **quiero** que solo una pregunta esté abierta a la vez dentro de mi categoría activa, **para** mantener la vista limpia y enfocada.
4. **Como** visitante, **quiero** navegar entre categorías mediante tabs, **para** filtrar las preguntas por tema.
5. **Como** visitante en móvil, **quiero** deslizar horizontalmente los tabs de categoría, **para** acceder a todas las categorías en pantallas pequeñas.
6. **Como** visitante, **quiero** acceder a la página de contacto desde el final de la FAQ, **para** enviar una pregunta que no encuentro en la lista.
7. **Como** visitante, **quiero** ver un breadcrumb con la ruta actual, **para** saber dónde estoy y poder volver al inicio.

## Alcance de esta feature

**Incluye:**

- Ruta pública `/faq` con página Livewire (breadcrumb, título, introducción, tabs de categorías, acordeón de preguntas, CTA a contacto).
- Categorías estáticas: Compras, Envíos, Pagos, Cambios y devoluciones, Cuenta de usuario.
- Contenido estático en Blade/i18n — no administrable desde panel.
- Acordeón con una sola pregunta abierta a la vez (Alpine.js).
- Tabs horizontales con scroll en móvil.
- CTA final a `/contact`.
- Nuevo dominio i18n `lang/{en,es}/faq.php` para toda la copy de la página.
- Activación de la ruta `/faq` (ya referenciada en footer y CTA de contacto).

**No incluye (diferido, según brief):**

- Buscador de preguntas.
- Preguntas destacadas.
- Administración desde el panel admin (requiere CMS, no planeado).
- Enlaces a artículos de ayuda relacionados.
- Modelo de datos FAQ / persistencia en BD.

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | Contenido estático | Las preguntas, respuestas y categorías viven en archivos de traducción (`lang/{en,es}/faq.php`). Cambios requieren editar los archivos PHP de i18n. No hay modelo de datos. |
| D2 | Categorías | 5 categorías fijas: Compras, Envíos, Pagos, Cambios y devoluciones, Cuenta de usuario. Definidas en el brief. |
| D3 | Comportamiento del acordeón | Solo una pregunta abierta a la vez dentro de la categoría activa. Implementado con Alpine.js (`x-show`, transición suave). |
| D4 | Navegación por tabs | Tabs horizontales; en móvil scroll horizontal con indicador visual. Sin componente Livewire — todo en Blade + Alpine. |
| D5 | CTA a contacto | Card al final de la página con enlace a `/contact`. Patrón visual similar al CTA de FAQ en la página de contacto. |

---

## Criterios de aceptación (EARS)

### Happy path

### R1 — Visualización de la página FAQ

CUANDO cualquier visitante accede a `/faq`,
EL SISTEMA DEBE mostrar la página con breadcrumb (`Inicio / FAQ`), título "Preguntas Frecuentes", introducción breve, tabs de categorías y acordeón de preguntas,
con respuesta HTTP 200.

### R2 — Contenido de categorías y preguntas

CUANDO la página se renderiza,
EL SISTEMA DEBE mostrar 5 categorías como tabs horizontales (Compras, Envíos, Pagos, Cambios y devoluciones, Cuenta de usuario)
Y DEBE mostrar al menos una pregunta y respuesta bajo cada categoría.

### R3 — Selección de categoría por tab

CUANDO el visitante hace clic en un tab de categoría,
EL SISTEMA DEBE mostrar únicamente las preguntas de esa categoría
Y DEBE marcar visualmente el tab como activo (fondo Intense Cocoa + texto Silk Cream, o borde inferior Soft Gold).

### R4 — Expansión de pregunta (acordeón)

CUANDO el visitante hace clic en una pregunta cerrada,
EL SISTEMA DEBE expandir la respuesta con animación suave
Y DEBE mostrar el ícono de contracción (`−` o flecha abajo).

### R5 — Contracción de pregunta

CUANDO el visitante hace clic en una pregunta ya abierta,
EL SISTEMA DEBE contraer la respuesta con animación suave
Y DEBE restaurar el ícono de expansión (`+` o flecha derecha).

### R6 — Una sola pregunta abierta a la vez

CUANDO el visitante expande una pregunta mientras otra está abierta dentro de la misma categoría,
EL SISTEMA DEBE contraer la pregunta previamente abierta
Y DEBE expandir la nueva pregunta,
de modo que nunca haya más de una pregunta expandida simultáneamente en la categoría activa.

### R7 — Scroll horizontal de tabs en móvil

DONDE el visitante accede desde móvil o tablet (breakpoint `sm`/`md`),
CUANDO las categorías no caben en el ancho visible,
EL SISTEMA DEBE permitir scroll horizontal de los tabs
Y DEBE mostrar un indicador visual de que hay más tabs disponibles (flecha o fade).

### R8 — CTA a contacto al final

CUANDO el visitante llega al final de la página de FAQ,
EL SISTEMA DEBE mostrar un banner o card con el texto "¿No encontraste lo que buscabas?" y un botón "Contáctanos" que enlaza a `/contact`.

### R9 — Breadcrumb funcional

DONDE el visitante está en la página de FAQ,
CUANDO hace clic en "Inicio" del breadcrumb,
EL SISTEMA DEBE navegar a la página principal (`/`).

### R10 — Layout responsive (ancho máximo)

DONDE el visitante accede desde cualquier dispositivo,
EL SISTEMA DEBE mostrar el contenido de la FAQ centrado con un ancho máximo de 700–800px,
SIN exceder ese ancho en ningún breakpoint.

### R11 — Copy localizada

CUANDO se renderiza cualquier texto de la página FAQ (breadcrumb, título, introducción, categorías, preguntas, respuestas, CTA),
EL SISTEMA DEBE resolverlo desde el dominio de traducción `lang/{en,es}/faq.php`,
SIN cadenas hardcodeadas en español o inglés.

### R12 — Enlaces de navegación al FAQ

DONDE el visitante está en el footer del storefront,
CUANDO hace clic en el enlace "FAQs",
EL SISTEMA DEBE llevarlo a `/faq` con respuesta 200,
SIN apuntar a la ruta inexistente `/faqs`.

### Validación y error

### R13 — Categoría sin preguntas (robustez)

CUANDO una categoría no tiene preguntas definidas (caso edge de datos),
EL SISTEMA DEBE mostrar el mensaje "No hay preguntas en esta categoría."
SIN romper el layout ni la navegación por tabs.

### R14 — Ruta inexistente

CUANDO un visitante accede a una ruta que no existe bajo `/faq` (como `/faq/algo`),
EL SISTEMA DEBE devolver HTTP 404 con la página de error estándar del storefront.

---

## Mapa de pruebas (orientativo)

| Área | Escenarios mínimos |
|------|-------------------|
| Ruta y navegación | `/faq` responde 200 para guest y usuario autenticado; footer link resuelve correctamente |
| Contenido estático | Todas las categorías visibles; al menos 1 pregunta por categoría; CTA a `/contact` presente |
| Acordeón | Expandir pregunta muestra respuesta; contraer la oculta; abrir segunda cierra primera |
| Tabs | Clic en tab muestra solo sus preguntas; tab activo visualmente marcado |
| Responsive | Tabs scrollable en móvil (verificar indicador visual); contenido centrado 700–800px |
| i18n | Copy visible en `en` y `es` sin cadenas hardcodeadas |
| Edge cases | Categoría vacía muestra mensaje; ruta inválida devuelve 404 |

---

## Definition of Done (producto)

- [ ] R1–R14 cubiertos por tests (feature test `FaqPageTest`).
- [ ] `lang/{en,es}/faq.php` completo para toda la copy nueva de esta feature.
- [ ] Ruta `/faq` registrada y funcional (ya referenciada en footer y CTA de contacto).
- [ ] Acordeón funcional con Alpine.js (una pregunta abierta a la vez).
- [ ] Tabs de categoría con scroll horizontal en móvil.
- [ ] Pint + tests Sail del alcance en verde.
