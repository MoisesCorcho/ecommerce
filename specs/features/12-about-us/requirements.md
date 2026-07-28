# About Us — Nuestra Esencia

> **Estado:** Completa
> **ID:** F12 · **Slug:** `12-about-us` (página estática de storefront, fuera de la secuencia F0N del roadmap)
> **Prerequisitos:** Ninguna (página estática) — ver [`ui-briefs/12-about-us.md`](../../ui-briefs/12-about-us.md)
> **Desbloquea:** —

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Brief visual y de producto | [`specs/ui-briefs/12-about-us.md`](../../ui-briefs/12-about-us.md) |
| Stitch de referencia | [`specs/ui-briefs/stitches/about_us_leen_handbags_final_consistency_fix/code.html`](../../ui-briefs/stitches/about_us_leen_handbags_final_consistency_fix/code.html) |
| Tokens de diseño | [`specs/ui-briefs/00-design-tokens.md`](../../ui-briefs/00-design-tokens.md) |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Convenciones de código (Actions, DTOs, i18n) | `AGENTS.md` / project-conventions |
| Layout storefront existente | `resources/views/layouts/storefront.blade.php` |
| Página de contacto (patrón de referencia) | `resources/views/components/contact-page/` |
| Página FAQ (patrón de referencia para página estática) | `resources/views/components/faq-page/` |

> Nota de partida: el navbar del storefront ya enlaza `/about-us` (ver `storefront.blade.php:29`). La ruta actualmente no está registrada, dando 404.

## User stories

1. **Como** visitante, **quiero** ver una página institucional sobre la marca, **para** conocer su historia, valores y propuesta de valor antes de comprar.
2. **Como** visitante, **quiero** ver una sección hero con imagen atmosférica y título emocional, **para** captar inmediatamente la identidad de la marca.
3. **Como** visitante, **quiero** leer la historia de la marca en un layout de dos columnas (texto + imagen), **para** conectar emocionalmente con su origen.
4. **Como** visitante, **quiero** ver los valores/pilares de la marca en un grid visual, **para** entender qué diferencia a Leen de otras marcas.
5. **Como** visitante, **quiero** ver una sección diferencial que explique materiales, fabricación y atención al detalle, **para** justificar la exclusividad de los productos.
6. **Como** visitante, **quiero** explorar una galería de imágenes de la marca, **para** ver el producto y taller en contexto.
7. **Como** visitante, **quiero** ampliar imágenes de la galería en un lightbox, **para** ver los detalles con mayor claridad.
8. **Como** visitante, **quiero** acceder al catálogo de productos desde un CTA al final de la página, **para** continuar mi recorrido hacia la compra.
9. **Como** visitante en móvil, **quiero** que la página se adapte correctamente a mi pantalla, **para** tener una experiencia equivalente a desktop.
10. **Como** visitante, **quiero** ver un breadcrumb con la ruta actual, **para** saber dónde estoy y poder volver al inicio.

## Alcance de esta feature

**Incluye:**

- Ruta pública `/about-us` con página Livewire.
- Secciones estáticas: Hero, Historia, Pilares (filosofía), Diferencial, Galería, CTA.
- Contenido estático en Blade/i18n — no administrable desde panel.
- Galería con lightbox (Alpine.js) y lazy loading en imágenes.
- Nuevo dominio i18n `lang/{en,es}/about.php` para toda la copy de la página.
- Activación de la ruta `/about-us` (ya referenciada en navbar del storefront).
- Scroll reveal animations (entradas suaves al hacer scroll).
- Responsive: desktop-first con experiencia móvil equivalente.

**No incluye (diferido, según brief):**

- Video institucional.
- Línea de tiempo de la marca.
- Reconocimientos o certificaciones.
- Equipo de trabajo.
- Colaboraciones con otras marcas.
- Administración desde el panel admin (requiere CMS, no planeado).
- Modelo de datos / persistencia en BD.

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | Contenido estático | Todo el contenido (historia, filosofía, diferencial, galería) vive en archivos de traducción (`lang/{en,es}/about.php`) y Blade hardcodeado. No hay modelo de datos. |
| D2 | Secciones | 6 secciones fijas: Hero, Historia, Pilares, Diferencial, Galería, CTA. Definidas en el brief. |
| D3 | Galería | Grid de 4–6 imágenes con lightbox Alpine.js. Navegación anterior/siguiente dentro del lightbox. Lazy loading nativo (`loading="lazy"`). |
| D4 | Animaciones | Scroll reveal con JavaScript vanilla (IntersectionObserver o scroll listener). Patrón ya presente en el stitch de referencia. |
| D5 | CTA final | Banner a ancho completo con fondo Intense Cocoa, texto Silk Cream, botón "Ver productos" que lleva a `/shop`. |
| D6 | Breadcrumb | "Inicio / Nosotros". Patrón idéntico a FAQ. |
| D7 | Tipografía hero | Título: Chillax Bold (display-lg). Subtítulo decorativo: La Belle Aurore (accent-script). |

---

## Criterios de aceptación (EARS)

### Happy path

### R1 — Visualización de la página About Us

CUANDO cualquier visitante accede a `/about-us`,
EL SISTEMA DEBE mostrar la página con las 6 secciones (Hero, Historia, Pilares, Diferencial, Galería, CTA),
con respuesta HTTP 200.

### R2 — Hero section

CUANDO la página se renderiza,
EL SISTEMA DEBE mostrar una sección hero con imagen de fondo a pantalla completa (40–50vh), overlay sutil, título "Nuestra Esencia" (Chillax Bold, Silk Cream) y subtítulo decorativo (La Belle Aurore, Soft Gold),
con centrado vertical del contenido.

### R3 — Historia (dos columnas)

CUANDO la página se renderiza en desktop,
EL SISTEMA DEBE mostrar la sección de historia en dos columnas (texto 50% + imagen 50%),
con título "Nuestra Historia" (Chillax), párrafos descriptivos (Montserrat Regular) y fotografía de alta calidad.

### R4 — Historia (responsive móvil)

DONDE el visitante accede desde móvil (breakpoint `sm`),
CUANDO se renderiza la sección de historia,
EL SISTEMA DEBE mostrar una columna con imagen arriba y texto abajo,
SIN perder contenido ni legibilidad.

### R5 — Pilares/Filosofía (grid de valores)

CUANDO la página se renderiza,
EL SISTEMA DEBE mostrar una sección "Nuestros Pilares" con 4 valores (Calidad Excepcional, Diseño Atemporal, Exclusividad, Compromiso Artesanal),
cada uno con ícono, título (Montserrat SemiBold, uppercase) y descripción corta,
con fondo Soft Sand para contraste.

### R6 — Diferencial (dos columnas)

CUANDO la página se renderiza en desktop,
EL SISTEMA DEBE mostrar la sección diferencial en dos columnas (imagen + texto) con dirección opuesta a la sección Historia,
con título "Lo que nos hace diferentes", descripción y bullets (materiales, fabricación, atención al detalle, diseño).

### R7 — Galería de imágenes

CUANDO la página se renderiza,
EL SISTEMA DEBE mostrar una galería con 4–6 imágenes en grid (cuadradas o 4:5),
con lazy loading nativo (`loading="lazy"`),
con hover overlay sutil + ícono expandir.

### R8 — Lightbox de galería

CUANDO el visitante hace clic en una imagen de la galería,
EL SISTEMA DEBE abrir un lightbox con overlay Intense Cocoa, imagen centrada y botón cerrar,
con navegación anterior/siguiente si hay múltiples imágenes.

### R9 — CTA final a shop

CUANDO el visitante llega al final de la página,
EL SISTEMA DEBE mostrar un banner a ancho completo con fondo Intense Cocoa, texto "Descubre nuestras colecciones" (Silk Cream) y botón "Ver productos" que enlaza a `/shop`.

### R10 — Breadcrumb funcional

DONDE el visitante está en la página About Us,
CUANDO hace clic en "Inicio" del breadcrumb,
EL SISTEMA DEBE navegar a la página principal (`/`).

### R11 — Copy localizada

CUANDO se renderiza cualquier texto de la página (breadcrumb, títulos, párrafos, valores, CTA),
EL SISTEMA DEBE resolverlo desde el dominio de traducción `lang/{en,es}/about.php`,
SIN cadenas hardcodeadas en español o inglés.

### R12 — Scroll reveal animations

CUANDO el visitante hace scroll por la página,
EL SISTEMA DEBE mostrar animaciones de entrada suaves en las secciones (fade + translate),
sin afectar el rendimiento ni la accesibilidad.

### R13 — Layout responsive completo

DONDE el visitante accede desde cualquier dispositivo,
EL SISTEMA DEBE adaptar todas las secciones a su breakpoint correspondiente (desktop `lg`/`xl`, tablet `md`, móvil `sm`),
con experiencia equivalente en todos los tamaños.

### R14 — Enlace de navegación en navbar

DONDE el visitante está en el navbar del storefront,
CUANDO hace clic en "About" (o "Nosotros"),
EL SISTEMA DEBE llevarlo a `/about-us` con respuesta 200.

### Validación y error

### R15 — Ruta inexistente

CUANDO un visitante accede a una ruta que no existe bajo `/about-us` (como `/about-us/algo`),
EL SISTEMA DEBE devolver HTTP 404 con la página de error estándar del storefront.

### R16 — Galería sin imágenes (robustez)

CUANDO no hay imágenes definidas para la galería (caso edge de datos),
EL SISTEMA DEBE ocultar la sección de galería completamente
SIN romper el layout ni las demás secciones.

---

## Mapa de pruebas (orientativo)

| Área | Escenarios mínimos |
|------|-------------------|
| Ruta y navegación | `/about-us` responde 200 para guest y usuario autenticado; navbar link resuelve correctamente |
| Contenido estático | Todas las 6 secciones visibles; CTA a `/shop` presente; breadcrumb funcional |
| Galería | Imágenes visibles con lazy loading; lightbox abre al clic; cerrar lightbox; navegación prev/next |
| Responsive | Dos columnas en desktop, una en móvil; galería adapta grid; hero mantiene proporción |
| i18n | Copy visible en `en` y `es` sin cadenas hardcodeadas |
| Animaciones | Scroll reveal activa al hacer scroll; no afecta rendimiento |
| Edge cases | Ruta inválida devuelve 404; galería vacía no rompe layout |

---

## Definition of Done (producto)

- [ ] R1–R16 cubiertos por tests (feature test `AboutUsPageTest`).
- [ ] `lang/{en,es}/about.php` completo para toda la copy nueva de esta feature.
- [ ] Ruta `/about-us` registrada y funcional (ya referenciada en navbar).
- [ ] Lightbox funcional con Alpine.js (abrir, cerrar, navegación).
- [ ] Scroll reveal animations implementadas.
- [ ] Responsive: desktop-first con experiencia móvil equivalente.
- [ ] Pint + tests Sail del alcance en verde.
