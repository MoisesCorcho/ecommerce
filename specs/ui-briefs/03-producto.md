# Brief UI: Detalle de producto

> **Vista:** Producto · **Ruta sugerida:** `/products/{slug}`
> **Depende de:** F01-S (storefront), F03 (carrito), F08 (wishlist), F07 (reviews — diferido)
> **Estado:** Pendiente de F01-S

---

# Para Stitch (diseño visual)

> **Prompt para Google Stitch.** Pasar este bloque + `00-design-tokens.md`.

## Objetivo de la vista

Página de detalle de un producto de Leen Handbags. El usuario conoce las características, visualiza imágenes, selecciona una variante y compra.

## Estructura y layout

**Desktop (layout principal):**
- Breadcrumb arriba (`Inicio / Shop / <categoría> / <producto>`).
- **Dos columnas:**
  - Izquierda (50–60%): galería de imágenes.
  - Derecha (40–50%): información de compra (nombre, precio, variantes, cantidad, botones).
- Sección de descripción completa a ancho completo debajo.
- Carrusel de productos relacionados al final.

**Tablet:**
- Dos columnas más equilibradas o galería arriba + info debajo.

**Móvil:**
- Una sola columna: galería arriba, info de compra debajo, descripción y relacionados después.

## Componentes visuales

### Galería de imágenes
- Imagen principal grande (aspecto 4:5 o cuadrada), fondo Silk Cream.
- Miniaturas en fila debajo (desktop) o columna lateral (desktop grande).
- Lightbox al hacer clic en la imagen principal (overlay Intense Cocoa semi-transparente + imagen centrada + botón cerrar).
- Al cambiar de variante, actualizar imagen principal si la variante tiene su propia imagen.

### Información de compra (columna derecha)
- **Nombre del producto**: Chillax Semibold o Bold, tamaño grande, Intense Cocoa.
- **Precio**: Montserrat SemiBold, tamaño destacado, Intense Cocoa.
- **Estado de stock**: texto pequeño (Montserrat Regular) — "Disponible", "X unidades", o "Agotado" (en Soft Gold o rojo sutil).
- **SKU**: texto pequeño, Montserrat Regular, Intense Cocoa con opacidad (ej: "REF-001").
- **Descripción breve**: Montserrat Regular, 2–3 líneas.

### Selectores de variante
- **Color**: swatches circulares con el color real. Seleccionado con borde Intense Cocoa o Soft Gold. Etiqueta del color al lado o debajo.
- **Material**: botones pequeños (pills) o dropdown. Seleccionado con fondo Intense Cocoa + texto Silk Cream.
- **Tamaño**: botones pequeños (pills) o dropdown. Mismo estilo que material.
- Solo mostrar atributos que el producto tenga. Si un producto no tiene variantes, omitir los selectores.

### Selector de cantidad
- Input con botones `−` / `+`. Mínimo 1, máximo = stock de la variante seleccionada.
- Deshabilitar `+` si se alcanza el stock.
- Estilo limpio, bordes Intense Cocoa.

### Botones de acción
- **Agregar al carrito**: ancho completo (de la columna derecha), fondo Intense Cocoa, texto Silk Cream, Montserrat SemiBold. Hover: Soft Gold. Deshabilitado si variante agotada o sin seleccionar.
- **Comprar ahora** (opcional): secundario, borde Intense Cocoa, texto Intense Cocoa, fondo transparente. Hover: fondo Intense Cocoa + texto Silk Cream.
- **Favoritos**: ícono corazón flotante o botón pequeño. Intense Cocoa, estado activo (ya en favoritos) en Soft Gold o relleno Intense Cocoa.

### Descripción completa
- Sección de ancho completo, fondo Silk Cream o Soft Sand para contraste.
- Título "Descripción" o "Detalles" en Chillax Semibold.
- Contenido en Montserrat Regular, organizado en bloques (características, materiales, dimensiones, cuidados).
- Posiblemente tabs o secciones con subtítulos en Montserrat SemiBold.

### Productos relacionados
- Carrusel horizontal de tarjetas pequeñas (iguales a las de Shop pero más compactas).
- Título "Productos relacionados" o "También te puede interesar" en Chillax Semibold.
- Flechas de navegación a los lados (Intense Cocoa, hover Soft Gold).

## Paleta de colores (ver `00-design-tokens.md`)

- Fondo: **Silk Cream** `#FFF8CF`
- Contraste (sección descripción, dividers): **Soft Sand** `#E9DED3`
- Texto, botones primarios, íconos: **Intense Cocoa** `#372621`
- Hover, activos, detalles: **Soft Gold** `#D2AE36`
- Proporción 70/20/10.

## Tipografía (ver `00-design-tokens.md`)

- Nombre del producto, títulos de sección: **Chillax** Semibold o Bold.
- Precio, botones, etiquetas: **Montserrat** SemiBold.
- Descripción, cuerpo: **Montserrat** Regular.
- SKU, captions: **Montserrat** Regular con opacidad.
- Frase decorativa (si aplica, ej: tagline de la colección): **La Belle Aurore** Regular — usar con moderación.

## Estilo visual

- Premium, minimalista, artesanal. El producto es el protagonista.
- Mucho espacio en blanco. Layout de dos columnas respira.
- Fotografía de alta calidad, fondo neutro (Silk Cream) para destacar el producto.
- Bordes discretos, sombras suaves.
- Iconografía lineal, Intense Cocoa.
- Sin distracciones. La zona de compra debe ser clara y rápida.

## Estados

- **Variante agotada**: badge "Agotado", botón "Agregar al carrito" deshabilitado, selector muestra la opción pero sin stock.
- **Variante sin seleccionar**: botón "Agregar al carrito" deshabilitado con mensaje "Selecciona una variante".
- **Cantidad excedida**: input limita al stock, feedback sutil.
- **Agregado al carrito**: toast o notificación ("Producto agregado al carrito") + link al carrito.
- **Agregado a favoritos**: ícono corazón relleno + toast sutil.
- **Producto no publicable (404)**: página 404 de marca ( Silk Cream, mensaje en Chillax, ilustración minimalista).

## Breakpoints

Diseñar primero para **desktop** (`lg`/`xl`) con dos columnas. En tablet (`md`) y móvil (`sm`), una sola columna: galería arriba, info debajo. Ver `00-design-tokens.md` sección Responsive.

---

# Para implementación (NO pasar a Stitch)

## Contexto del proyecto

- **Variantes con atributos estructurados** (D3): color, material, tamaño como campos separados. El usuario selecciona una combinación de atributos para elegir una variante.
- **Stock visible** (D4): cada variante tiene stock. Si la variante seleccionada no tiene stock, mostrar "Agotado".
- **SKU obligatorio** en cada variante (F01 R20). No es opcional.
- **Moneda** (D5): precio en moneda de contexto (COP o EUR).
- Solo se muestra el producto si es publicable según F01 R12 (existe, `is_active = true`, tiene variante activa con precio en moneda de contexto). Si no, 404.

## Acciones del usuario

El usuario podrá:

- Cambiar la imagen principal (clic en miniatura).
- Ampliar una imagen (lightbox).
- Seleccionar atributos de variante (color, material, tamaño).
- Modificar la cantidad.
- Agregar el producto al carrito (requiere F03).
- Comprar inmediatamente (agregar + ir a checkout).
- Agregar el producto a Favoritos (requiere F08).
- Navegar hacia productos relacionados.

## Validaciones

- No permitir agregar cantidades menores a 1.
- No permitir agregar cantidades superiores al stock de la variante seleccionada.
- Validar que se haya seleccionado una variante (si el producto tiene variantes) antes de agregar al carrito.
- Mostrar mensajes de confirmación o error según corresponda.
- Si el producto no es publicable, responder 404 (F01 R17).

## Datos requeridos

**Dinámicos (del backend):**

- Información del producto: nombre, descripción, slug.
- Galería de imágenes (`ProductImage`: path, is_primary).
- Variantes disponibles (`ProductVariant`: SKU, stock, atributos de color/material/tamaño).
- Precios por variante en moneda de contexto (`ProductVariantPrice`).
- Productos relacionados (misma categoría).
- Estado de Favoritos del usuario autenticado (cuando aplique).

## Consideraciones técnicas

- Lazy Loading en imágenes de la galería.
- Galería responsive (carrusel en móvil, grid/miniaturas en desktop).
- Las acciones de agregar al carrito y favoritos deben ser asíncronas (Livewire, sin recargar).
- Al cambiar de variante, actualizar precio, stock e imagen dinámicamente.
- Responsive: desktop-first con experiencia móvil equivalente (ver `00-design-tokens.md`).
- La estructura debe permitir agregar nuevos atributos de variante sin modificar el layout.

## Fuera de alcance (diferido)

- **Opiniones / reseñas** — requiere F07 (reviews). La sección de opiniones no se incluye en la primera versión.
- **Precio anterior / descuento** — no hay modelo de promociones (F06).
- **Videos del producto** — posible mejora futura.
- **Tiempo estimado de entrega** — requiere lógica de envíos (no implementada).
- **Información de envío / devoluciones / garantía** — contenido estático genérico por ahora, no por producto.
- **Notificación cuando vuelva a haber stock** — mejora futura.
- **Compartir producto** — mejora futura.
- **Recomendaciones personalizadas** — mejora futura.
