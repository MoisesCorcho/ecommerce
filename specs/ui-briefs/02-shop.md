# Brief UI: Shop (listado de catálogo)

> **Vista:** Shop · **Ruta sugerida:** `/shop`
> **Depende de:** F01-S (storefront), F03 (carrito), F08 (wishlist)
> **Estado:** Pendiente de F01-S

---

# Para Stitch (diseño visual)

> **Prompt para Google Stitch.** Pasar este bloque + `00-design-tokens.md`.
> No incluir la sección "Para implementación" al final.

## Objetivo de la vista

Página de listado del catálogo de Leen Handbags. El usuario explora, filtra y ordena los productos disponibles hasta encontrar el que desea comprar.

## Estructura y layout

**Desktop (layout principal):**
- Header con breadcrumb (ej: `Inicio / Shop / Bolsos`), título de categoría/colección y contador de resultados.
- **Sidebar de filtros a la izquierda** (240–280px), **grid de productos a la derecha** (resto del ancho).
- Selector de ordenamiento como dropdown en la esquina superior derecha del grid.
- Paginación al pie del grid.

**Tablet:**
- Sidebar colapsa a drawer deslizable con botón "Filtros".
- Grid de 3 columnas.

**Móvil:**
- Drawer de filtros con botón "Filtros" fijo arriba.
- Grid de 2 columnas.

## Componentes visuales

### Breadcrumb
`Inicio / Shop / <categoría>` con enlaces. Separador `/` en Soft Sand o Intense Cocoa con opacidad.

### Header del catálogo
Título grande (Chillax Semibold) + contador de resultados ("24 productos"). Fondo Silk Cream.

### Panel de filtros (sidebar / drawer)
- **Categoría**: checkboxes con nombre + contador entre paréntesis.
- **Color**: swatches circulares con el color real + nombre al hover.
- **Precio**: slider dual (rango min–max) con valores visibles.
- **Disponibilidad**: checkbox "Solo disponibles".
- Botón "Limpiar filtros" al pie del panel.
- Cada grupo de filtro con título en Montserrat SemiBold, separadores sutiles.

### Selector de ordenamiento
Dropdown simple: "Más recientes", "Precio: menor a mayor", "Precio: mayor a menor". Estilo limpio, borde Intense Cocoa, ícono de flecha.

### Tarjeta de producto
- **Imagen**: cuadrada o 4:5, fondo Silk Cream o blanco, objeto centrado.
- **Nombre**: Montserrat Medium, Intense Cocoa, 1–2 líneas máximo.
- **Precio**: Montserrat SemiBold, Intense Cocoa. Destacado.
- **Colores disponibles**: puntos de color debajo del precio (swatches pequeños).
- **Estado stock**: si agotado, overlay semi-transparente Intense Cocoa + badge "Agotado" en Soft Gold o blanco.
- **Botones de acción**:
  - Ícono corazón (favoritos) — Intense Cocoa, hover Soft Gold.
  - Ícono carrito — Intense Cocoa, hover Soft Gold.
  - En desktop, botones visibles al hover sobre la imagen. En móvil, siempre visibles debajo.
- Hover en tarjeta: elevación sutil (sombra suave), sin cambio de color.

### Vista rápida (modal)
Modal centrado, fondo con overlay Intense Cocoa semi-transparente. Contenido: imagen grande, nombre, precio, selector de color, botón "Ver producto" (primario, Intense Cocoa) + "Agregar al carrito" (secundario, borde Intense Cocoa).

### Paginación
Números + flechas. Página actual destacada en Intense Cocoa con fondo Soft Sand. Resto en Intense Cocoa con opacidad. Estilo limpio.

### Estado vacío
Ilustración minimalista (sin productos) + mensaje "No se encontraron productos" + botón "Limpiar filtlos" (Intense Cocoa, borde).

## Paleta de colores (ver `00-design-tokens.md`)

- Fondo: **Silk Cream** `#FFF8CF`
- Contraste (tarjetas, dividers): **Soft Sand** `#E9DED3`
- Texto, botones, íconos: **Intense Cocoa** `#372621`
- Hover, activos, detalles: **Soft Gold** `#D2AE36`
- Proporción 70/20/10.

## Tipografía (ver `00-design-tokens.md`)

- Título del catálogo: Chillax Semibold.
- Nombres de producto, etiquetas, botones: Montserrat Medium/SemiBold.
- Precio: Montserrat SemiBold.
- Filtros, captions: Montserrat Regular.

## Estilo visual

- Minimalista, premium. Consistente con Home.
- Mucho espacio en blanco. Grid amplio en desktop.
- Tarjetas limpias, sin exceso de información.
- Iconografía lineal, grosor uniforme, Intense Cocoa.
- Hover states sutiles (elevación + Soft Gold en acciones).

## Estados

- **Agotado**: badge + overlay + botones deshabilitados.
- **Loading**: skeleton loaders para tarjetas.
- **Filtros sin resultados**: estado vacío con ilustración + CTA limpiar.
- **Hover tarjeta**: elevación + botones visibles.

## Breakpoints

Diseñar primero para **desktop** (`lg`/`xl`) con sidebar de filtros visible. En tablet (`md`) y móvil (`sm`), filtros en drawer. Ver `00-design-tokens.md` sección Responsive.

---

# Para implementación (NO pasar a Stitch)

## Contexto del proyecto

- **Variantes con atributos estructurados** (D3): las variantes tienen color, material, tamaño como campos separados. Esto permite filtros facetados.
- **Stock visible** (D4): cada variante tiene stock. Los productos agotados se muestran como "Agotado".
- **Moneda** (D5): precios en COP (pesos enteros) o EUR (centavos). Una moneda por contexto de tienda.
- Solo se muestran productos **publicables** según F01 R11: `is_active = true` con al menos una variante activa que tenga precio en la moneda de contexto.

## Filtros

**Del alcance inicial:**

- Categoría.
- Color (facetado desde atributos de variante).
- Precio (rango).
- Disponibilidad (en stock / agotado).

**Diferidos (requieren features no implementadas):**

- ~Popularidad~ (requiere F04 órdenes).
- ~Mejor valorados~ (requiere F07 reviews).
- ~Productos más recientes~ (puede incluirse — ordenamiento simple por `created_at`).

## Ordenamiento

**Del alcance inicial:**

- Más recientes (por `created_at`).
- Precio: menor a mayor.
- Precio: mayor a menor.

**Diferidos:**

- ~Más vendidos~ (requiere F04 órdenes).
- ~Mejor valorados~ (requiere F07 reviews).

## Acciones del usuario

El usuario podrá:

- Filtrar productos por categoría, color, precio, disponibilidad.
- Ordenar el catálogo.
- Limpiar los filtros aplicados.
- Abrir la vista rápida de un producto (modal).
- Agregar un producto al carrito (requiere F03).
- Agregar un producto a Favoritos (requiere F08).
- Acceder a la página de un producto (`/products/<slug>`).
- Navegar entre las páginas del catálogo.

## Validaciones

- Mostrar un mensaje cuando no existan productos que coincidan con los filtros aplicados.
- Mantener los filtros seleccionados mientras el usuario navega entre páginas.
- Mostrar únicamente productos publicables según F01 R11.
- Deshabilitar el botón "Agregar al carrito" si el producto está agotado.

## Datos requeridos

**Dinámicos (del backend):**

- Listado de productos publicables (con paginación).
- Para cada producto: imagen principal, nombre, precio (moneda de contexto), colores disponibles, estado de stock, slug.
- Categorías existentes (para filtro).
- Colores disponibles en el catálogo (para filtro facetado).
- Información de paginación (página actual, total páginas, total productos).
- Estado de Favoritos del usuario autenticado (cuando aplique).

## Consideraciones técnicas

- Sincronizar filtros con la URL (query params) para facilitar compartir búsquedas y conservar el estado al recargar.
- Actualizar el listado dinámicamente al aplicar filtros u ordenamientos sin recargar la página (Livewire).
- Lazy Loading en imágenes de productos.
- Responsive: desktop-first con experiencia móvil equivalente (ver `00-design-tokens.md`).
- Filtros facetados: solo mostrar opciones que tengan resultados.

## Fuera de alcance (diferido)

- **Precio anterior / descuento** — no hay modelo de promociones (F06 no implementado).
- **Filtro por popularidad** — requiere F04 (órdenes).
- **Filtro por valoración** — requiere F07 (reviews).
- **Búsqueda avanzada / buscador textual** — fuera del alcance inicial.
- **Filtros dinámicos según tipo de producto** — posible futura mejora.
- **Colecciones** — no existe modelo de colecciones, solo categorías.
