# Brief UI: About Us

> **Vista:** About Us · **Ruta sugerida:** `/about-us`
> **Depende de:** Ninguna (página estática)
> **Estado:** Completa

---

# Para Stitch (diseño visual)

> **Prompt para Google Stitch.** Pasar este bloque + `00-design-tokens.md`.

## Objetivo de la vista

Página institucional que presenta la historia, identidad y propósito de Leen Handbags. Crear conexión emocional con el cliente. Sensación de exclusividad, autenticidad y artesanía.

## Estructura y layout

**Scroll vertical, secciones a ancho completo o con contenedor centrado (max-width 1200px).**

1. **Presentación (hero secundario)** — imagen de marca + título + subtítulo. Altura media (40–50vh).
2. **Historia** — dos columnas: texto a un lado, imagen al otro.
3. **Filosofía** — grid de 3–5 íconos + texto.
4. **Diferencial** — imagen grande + texto descriptivo.
5. **Galería** — grid de imágenes.
6. **CTA** — banner al final con botón a `/shop`.

## Componentes visuales

### Presentación (hero secundario)
- Imagen de fondo a pantalla completa o 40–50vh (lifestyle, taller, producto destacado).
- Overlay sutil Intense Cocoa semi-transparente para legibilidad.
- Título grande (Chillax Bold) en Silk Cream o White sobre el overlay.
- Subtítulo breve (Montserrat Regular o La Belle Aurore para frase decorativa).
- Centrado vertical.

### Historia
- **Dos columnas** (desktop): texto a un lado (50%), imagen al otro (50%). Alternar dirección con la sección de diferencial para ritmo visual.
- **Texto**: título "Nuestra historia" (Chillax Semibold) + párrafos (Montserrat Regular, Intense Cocoa).
- **Imagen**: fotografía de alta calidad (origen, taller, fundadora). Aspecto 4:5 o cuadrada.
- En móvil, una columna: imagen arriba, texto abajo.

### Filosofía
- Grid de 3–5 valores (calidad, diseño, exclusividad, artesanía, compromiso).
- Cada valor: ícono lineal (Intense Cocoa) + título (Montserrat SemiBold) + descripción corta (Montserrat Regular, 1–2 líneas).
- Fondo Soft Sand para contraste con secciones adyacentes.
- En móvil, grid de 2 columnas o una columna.

### Diferencial
- **Dos columnas** (desktop): imagen grande a un lado, texto al otro (dirección opuesta a Historia).
- **Texto**: título "Lo que nos hace diferentes" (Chillax Semibold) + descripción (Montserrat Regular) + bullets o pasos numerados (materiales, fabricación, atención al detalle, diseño).
- **Imagen**: fotografía del proceso o un detalle de producto.
- En móvil, una columna.

### Galería
- Grid de imágenes (masonry o grid uniforme). 4–6 imágenes.
- Cada imagen: cuadrada o 4:5, fondo Silk Cream, hover con overlay sutil + ícono expandir.
- Lightbox al hacer clic (overlay Intense Cocoa + imagen centrada + botón cerrar).
- Lazy Loading.

### CTA final
- Banner a ancho completo, fondo Intense Cocoa o imagen de fondo con overlay.
- Texto Silk Cream: "Descubre nuestras colecciones" (Chillax Semibold o La Belle Aurore para frase decorativa).
- Botón "Ver productos" (fondo Silk Cream, texto Intense Cocoa, hover Soft Gold) que lleva a `/shop`.

## Paleta de colores (ver `00-design-tokens.md`)

- Fondo principal: **Silk Cream** `#FFF8CF`
- Contraste (filosofía, dividers): **Soft Sand** `#E9DED3`
- Texto, botones, íconos: **Intense Cocoa** `#372621`
- Hover, detalles, overlay: **Soft Gold** `#D2AE36`
- Overlays de imágenes: **Intense Cocoa** semi-transparente.
- CTA final: fondo **Intense Cocoa** con texto **Silk Cream**.
- Proporción 70/20/10.

## Tipografía (ver `00-design-tokens.md`)

- Títulos de sección, CTA, hero: **Chillax** Semibold o Bold.
- Cuerpo, descripciones, bullets: **Montserrat** Regular.
- Frases decorativas / hero subtítulo emocional: **La Belle Aurore** Regular — usar con moderación para frases cortas impactantes.
- Etiquetas, captions: **Montserrat** Medium.

## Estilo visual

- **Premium, artesanal, atemporal.** Sensación de exclusividad y autenticidad.
- Paleta neutra/tierra (Silk Cream, Soft Sand, Intense Cocoa, Soft Gold).
- Tipografía sans-serif (Chillax) para títulos, sans-serif (Montserrat) para cuerpo.
- Fotografía de alta calidad: lifestyle, taller, materiales, detalles, campañas.
- Mucho espacio en blanco. El contenido respira.
- Secciones con ritmo visual (alternar dirección de columnas).
- Sin ilustraciones innecesarias. Las imágenes son el protagonista visual.
- Bordes discretos, sombras suaves.

## Estados

- **Galería**: hover con overlay sutil + ícono expandir, lightbox al hacer clic.
- **Lightbox**: overlay Intense Cocoa + imagen centrada + botón cerrar + navegación (anterior/siguiente) si hay múltiples.
- **Loading de imágenes**: placeholder Silk Cream o skeleton.

## Breakpoints

Diseñar primero para **desktop** (`lg`/`xl`) con secciones de dos columnas y grids amplios. En tablet (`md`) y móvil (`sm`), una columna y grids más simples. Ver `00-design-tokens.md` sección Responsive.

---

# Para implementación (NO pasar a Stitch)

## Contexto del proyecto

- **Página estática** (D1): todo el contenido (historia, filosofía, diferencial, galería) vive en Blade hardcodeado. **No** es editable desde el panel admin.
- Cambios requieren editar las plantillas Blade.
- Sin modelo de datos — todo es estático.
- La marca es **Leen Handbags** (bolsos/accesorios, single-vendor).

## Acciones del usuario

El usuario podrá:

- Conocer la historia de la marca.
- Explorar el contenido visual (galería).
- Ampliar imágenes de la galería (lightbox).
- Acceder al catálogo de productos (`/shop`).

## Validaciones

- Validar la correcta carga de imágenes y contenido multimedia.

## Datos requeridos

**Estático (en Blade):** texto de presentación, historia, filosofía, diferencial; imágenes de galería (rutas en `public/`).

## Consideraciones técnicas

- Lazy Loading en imágenes de la galería.
- Lightbox para ampliar imágenes (puede usar Alpine.js o librería ligera).
- Carga rápida (contenido estático, sin consultas a base de datos).
- Responsive: desktop-first con experiencia móvil equivalente (ver `00-design-tokens.md`).
- Imágenes optimizadas (WebP si es posible).

## Fuera de alcance (diferido)

- **Video institucional** — mejora futura.
- **Línea de tiempo de la marca** — mejora futura.
- **Reconocimientos o certificaciones** — mejora futura.
- **Equipo de trabajo** — mejora futura.
- **Colaboraciones con otras marcas** — mejora futura.
- **Administración desde el panel** — requiere CMS (no planeado, D1).
