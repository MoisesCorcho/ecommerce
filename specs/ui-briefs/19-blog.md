# Brief UI: Blog (Módulo de Contenidos y Artículos)

> **Vistas:** Listado de Artículos (`/blog`) y Detalle de Artículo (`/blog/{slug}`)  
> **Depende de:** `App\Models\Post`, `App\Models\PostCategory`, `App\Enums\Blog\PostStatusEnum`, `LocaleEnum`  
> **Estado:** Listo para implementar  
> **Fuentes de verdad:** [`specs/ui-briefs/00-design-tokens.md`](00-design-tokens.md), [`resources/css/app.css`](../../resources/css/app.css)

---

# Para Stitch (Diseño Visual)

> **Prompt para Google Stitch.** Pasar este bloque + `00-design-tokens.md`.

## Objetivo de las Vistas

Ofrecer una experiencia de lectura editorial y de descubrimiento sofisticada y refinada para la marca Leen Handbags. El blog actúa como canal de storytelling, posicionamiento orgánico (SEO) y educación sobre moda consciente, artesanía del cuero y estilo de vida atemporal. La estética debe sentirse como una revista de moda de alta gama (vogue/editorial minimalista), no como un blog genérico.

---

## 1. Vista de Listado: `/blog`

### Estructura y Layout
- **Contenedor:** `max-w-storefront` (`1440px`), centrado, con `px-margin-mobile` (`20px`) y `px-margin-desktop` (`80px`).
- **Ritmo vertical:** `py-section-gap` (`120px`) entre hero/cabecera y listado.

### 1.1 Cabecera Editorial (Hero del Blog)
- **Título principal:** `font-chillax` en escala `text-display-lg` (Desktop 64px / Móvil 40px), color `text-intense-cocoa`, centrado.
- **Subtítulo o frase de marca:** `font-labelle-aurore` (`text-accent-script`, 28px), color `text-soft-gold`, centrado.
- **Descripción de sección:** `font-sans` (`Montserrat`) en `text-body-md`, `text-intense-cocoa/70`, max-width de 640px centrado.

### 1.2 Barra de Categorías y Filtros (Pills Navegables)
- Selector horizontal de categorías con scroll suave en móvil.
- **Pill activa:** fondo `bg-intense-cocoa`, texto `text-silk-cream`, tipografía `font-label-caps` (`uppercase`, tracking amplio).
- **Pills inactivas:** borde sutil `border border-intense-cocoa/20`, texto `text-intense-cocoa`, hover con fondo `bg-soft-sand`.
- Opción inicial **"Todos los artículos"** / **"All articles"** seleccionada por defecto.

### 1.3 Grid de Artículos (Editorial 3 Columnas)
- **Desktop (`lg`/`xl`):** Grid de **3 columnas** (`grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter`). Nunca 4 columnas para garantizar que las fotos respiren y destaquen.
- **Artículo Destacado (Opcional en primera posición):** El primer post puede abarcar 2 o 3 columnas en desktop con layout horizontal (imagen grande a la izquierda, contenido a la derecha).

### 1.4 Componente Tarjeta de Artículo (`ArticleCard`)
- **Contenedor:** Limpio, sin bordes toscos, fondo transparente o tarjeta sutil `bg-soft-sand/30`.
- **Imagen de Portada:** Proporción 16:10 o 4:3, esquinas rectas (`rounded-none`), con zoom suave en hover (`duration-700 hover:scale-105 overflow-hidden`).
- **Metadatos superiores:** 
  - Badge de Categoría: `bg-soft-sand text-intense-cocoa` en `font-label-caps`.
  - Fecha de publicación y Tiempo estimado de lectura (ej. *"15 Ago, 2026 · 4 min de lectura"*): `text-intense-cocoa/60 font-label-caps`.
- **Título del Artículo:** `font-chillax` en `text-headline-sm` (24px, Medium/Semibold), `text-intense-cocoa`, hover a `text-soft-gold` (`transition-colors duration-300`).
- **Extracto (Excerpt):** `font-sans` (`Montserrat`) en `text-body-md` (`text-intense-cocoa/75`), truncado elegante a 2 o 3 líneas.
- **Enlace de lectura:** *"Leer artículo →"* / *"Read story →"* en `font-label-caps text-intense-cocoa font-semibold group-hover:text-soft-gold`.

### 1.5 Paginación
- Paginación Livewire estilizada según el tema de Leen: números con `text-label-caps`, estado activo con fondo `bg-intense-cocoa text-silk-cream`.

---

## 2. Vista de Detalle: `/blog/{slug}`

### Estructura y Layout
- **Contenedor de lectura:** Contenedor estrecho optimizado para legibilidad editorial (`max-w-4xl` / ~800px) centrado dentro del layout general.

### 2.1 Breadcrumb de Navegación
- `Home > Blog > [Nombre de Categoría] > [Título del Post]` en `font-label-caps` (`text-intense-cocoa/60`, hover `text-soft-gold`).

### 2.2 Cabecera del Artículo
- **Categoría:** Badge destacado en `font-label-caps text-soft-gold`.
- **Título:** `font-chillax` en `text-display-lg` (Desktop 48–56px / Móvil 32px), `text-intense-cocoa`, `leading-tight`.
- **Metadatos de Publicación:** Fila con avatar/nombre de autor (Leen Editorial), fecha formateada y tiempo estimado de lectura en `font-label-caps`.

### 2.3 Imagen de Portada Principal
- Imagen a ancho completo del contenedor o sangrado extendido (`aspect-[16/9]`), fotográfica de alta resolución, esquinas `rounded-none`.

### 2.4 Cuerpo del Artículo (Prose Typographic Style)
- **Tipografía base:** `font-sans` (`Montserrat`), `text-body-lg` (18px), `leading-relaxed` (1.75), `text-intense-cocoa/85`.
- **Encabezados dentro del contenido (H2, H3):** `font-chillax text-headline-md` (`text-intense-cocoa`), con márgenes generosos arriba (`mt-12 mb-6`).
- **Párrafos:** Separación amplia (`mb-6`), sin justificado forzado.
- **Citas destacadas (Blockquotes):** Borde izquierdo de 2px en `border-soft-gold`, fondo sutil `bg-soft-sand/40`, tipografía en `font-accent-script` o `Montserrat Italic` en `text-headline-sm`.
- **Imágenes incrustadas:** `w-full my-8`, con pie de foto en `font-label-caps text-center text-intense-cocoa/50`.

### 2.5 Compartir y Navegación de Pie
- Botones sutiles para compartir en redes (Pinterest, WhatsApp, Copiar enlace) con íconos lineales en `text-intense-cocoa/70`.

### 2.6 Sección "Historias Relacionadas" (Artículos Recomendados)
- Separador con `py-section-gap`.
- Título: *"Quizás también te interese"* / *"You might also like"* en `font-headline-md text-intense-cocoa text-center mb-12`.
- Grid de **3 tarjetas de artículo** (excluyendo el actual).

---

## Paleta de Colores (según `00-design-tokens.md`)

| Color | HEX | Aplicación en Blog |
|---|---|---|
| **Silk Cream** | `#FFF8CF` | Fondo principal de las vistas y páginas. |
| **Soft Sand** | `#E9DED3` | Fondos de pills inactivas, blockquotes y tarjetas secundarias. |
| **Intense Cocoa** | `#372621` | Texto principal, títulos, botones de pill activas e íconos. |
| **Soft Gold** | `#D2AE36` | Acentos, enlaces hover, bordes de blockquotes y fechas destacadas. |

---

## Tipografía (según `00-design-tokens.md`)

| Familia | Rol en Blog | Escala |
|---|---|---|
| **Chillax** | Título principal de página, títulos de artículos y H2/H3 en contenido | `display-lg`, `headline-md`, `headline-sm` |
| **Montserrat** | Cuerpo de lectura, extractos, metadatos, botones, pills y breadcrumbs | `body-lg`, `body-md`, `label-caps` |
| **La Belle Aurore** | Subtítulo editorial decorativo, firmas y destacados | `accent-script` |

---

## Responsive y Breakpoints

- **Desktop (`lg`/`xl` - 1024px+):** Experiencia de lectura amplia, grid de 3 columnas para el catálogo, márgenes laterales de 80px.
- **Tablet (`md` - 768px):** Grid de 2 columnas en el listado, márgenes de 40px.
- **Móvil (`sm` - <640px):** 1 sola columna, selector de categorías con scroll horizontal (`overflow-x-auto no-scrollbar`), padding lateral de 20px (`px-margin-mobile`).
