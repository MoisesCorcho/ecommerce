# Brief UI: Lista de deseados (Wishlist)

> **Vista:** Wishlist · **Ruta sugerida:** `/wishlist`
> **Depende de:** F08 (wishlist), F03 (carrito), F01-S (storefront)
> **Estado:** Pendiente de F08

---

# Para Stitch (diseño visual)

> **Prompt para Google Stitch.** Pasar este bloque + `00-design-tokens.md`.

## Objetivo de la vista

El usuario ve los productos que guardó para comprar después y puede agregarlos al carrito o quitarlos de la lista.

## Estructura y layout

**Desktop (layout principal):**
- Breadcrumb arriba (`Inicio / Favoritos`).
- Título "Mis favoritos" (Chillax Semibold) + contador.
- **Grid de tarjetas de producto**: 3–4 columnas en desktop, 2 en móvil.
- Sin sidebar de filtros (más simple que Shop).

**Tablet:**
- Grid de 2–3 columnas.

**Móvil:**
- Grid de 2 columnas.

## Componentes visuales

### Tarjeta de producto (favorito)
- Similar a la tarjeta de Shop pero adaptada:
  - **Imagen** cuadrada o 4:5, fondo Silk Cream.
  - **Nombre** (Montserrat Medium, Intense Cocoa).
  - **Precio** (Montserrat SemiBold, Intense Cocoa).
  - **Estado de stock**: badge "Disponible" o "Agotado" (texto pequeño, Soft Gold o rojo sutil).
  - **Botón "Agregar al carrito"**: ancho completo o ícono + texto, Intense Cocoa, hover Soft Gold. Deshabilitado si agotado.
  - **Botón "Eliminar"**: ícono corazón relleno (clic para quitar) o ícono papelera. Intense Cocoa, hover Soft Gold o rojo sutil.
  - Enlace al detalle al hacer clic en la imagen o el nombre.

### Estado vacío
- Centrado en la página.
- Ilustración minimalista (corazón vacío o similar).
- Mensaje "Tu lista de deseados está vacía" en Chillax Semibold.
- Botón "Explorar productos" (Intense Cocoa, borde) que lleva a `/shop`.

### Feedback al agregar al carrito
- Toast o notificación sutil: "Producto agregado al carrito" + enlace "Ver carrito".
- Ícono de carrito con contador actualizado (si hay un ícono de carrito en el header).

## Paleta de colores (ver `00-design-tokens.md`)

- Fondo: **Silk Cream** `#FFF8CF`
- Contraste (dividers, cards): **Soft Sand** `#E9DED3`
- Texto, botones, íconos: **Intense Cocoa** `#372621`
- Hover, activos, detalles: **Soft Gold** `#D2AE36`
- Proporción 70/20/10.

## Tipografía (ver `00-design-tokens.md`)

- Título "Mis favoritos": **Chillax** Semibold.
- Nombres de producto, botones, etiquetas: **Montserrat** Medium/SemiBold.
- Precios, captions: **Montserrat** Regular/SemiBold.

## Estilo visual

- Consistente con Shop y Home. Minimalista, premium.
- Grid amplio en desktop, mucho espacio en blanco.
- Tarjetas limpias, sin exceso de información.
- Acciones rápidas (agregar al carrito, eliminar) visibles y fáciles.
- Iconografía lineal, Intense Cocoa.

## Estados

- **Lista vacía**: ilustración + mensaje + CTA a `/shop`.
- **Producto agotado**: badge "Agotado", botón "Agregar al carrito" deshabilitado.
- **Producto eliminado del catálogo**: card con overlay "Ya no disponible" + botón eliminar.
- **Agregado al carrito**: toast + ícono carrito actualizado.
- **Eliminado de favoritos**: card desaparece con animación sutil.

## Breakpoints

Diseñar primero para **desktop** (`lg`/`xl`) con grid de 3–4 columnas. En tablet (`md`) y móvil (`sm`), 2 columnas. Ver `00-design-tokens.md` sección Responsive.

---

# Para implementación (NO pasar a Stitch)

## Contexto del proyecto

- **Requiere autenticación** (D2): la wishlist es solo para usuarios autenticados. Los guests no tienen wishlist.
- La wishlist funciona **por producto**: el usuario guarda el producto, ve todas sus variantes al hacer clic en el detalle.
- **Stock visible** (D4): mostrar si el producto tiene stock o está agotado.
- Sin precio anterior / descuentos (no hay modelo de promociones).

## Acciones del usuario

El usuario podrá:

- Consultar sus productos guardados.
- Acceder a la página de un producto.
- Agregar un producto al carrito.
- Eliminar un producto de la wishlist.

## Validaciones

- Validar que el producto continúe disponible (publicable) antes de mostrarlo.
- Deshabilitar "Agregar al carrito" si el producto está agotado.
- Mostrar un mensaje cuando la wishlist se encuentre vacía.
- Si un producto fue eliminado del catálogo, no mostrarlo o mostrarlo como "Ya no disponible".

## Datos requeridos

**Dinámicos (del backend):**

- Productos guardados por el usuario (`Wishlist` + `Product` + `ProductVariant` + `ProductVariantPrice`).
- Para cada producto: imagen, nombre, precio (moneda de contexto), estado de stock, slug.

## Consideraciones técnicas

- Requiere autenticación (redirect a login si guest).
- Actualizar la información del producto en tiempo real (precio, disponibilidad).
- Permitir agregar al carrito sin abandonar la vista (Livewire asíncrono).
- Sincroniza automáticamente entre dispositivos cuando el usuario inicia sesión (misma cuenta).
- Responsive: desktop-first con experiencia móvil equivalente (ver `00-design-tokens.md`).

## Fuera de alcance (diferido)

- **Compartir wishlist** — mejora futura.
- **Múltiples listas de deseados** — mejora futura.
- **Notificaciones cuando un producto baje de precio** — mejora futura.
- **Notificaciones cuando un producto vuelva a tener stock** — mejora futura.
- **Agregar todos los productos al carrito con una sola acción** — mejora futura.
- **Wishlist para guests** — solo usuarios autenticados en la primera versión.
