# Brief UI: Carrito de compras

> **Vista:** Carrito · **Ruta sugerida:** `/cart`
> **Depende de:** F03 (carrito), F01-S (storefront)
> **Estado:** Pendiente de F03

---

# Para Stitch (diseño visual)

> **Prompt para Google Stitch.** Pasar este bloque + `00-design-tokens.md`.

## Objetivo de la vista

El usuario revisa los productos seleccionados antes de comprar, modifica cantidades y ve un resumen claro del pedido antes de ir al checkout.

## Estructura y layout

**Desktop (layout principal):**
- Breadcrumb arriba (`Inicio / Carrito`).
- **Dos columnas:**
  - Izquierda (60–70%): lista de ítems del carrito.
  - Derecha (30–40%, **sticky**): resumen de compra.
- Espacio en blanco generoso entre ítems y resumen.

**Tablet:**
- Dos columnas más equilibradas o lista arriba + resumen colapsable abajo.

**Móvil:**
- Una sola columna: lista de ítems arriba, resumen abajo (fijo o al final con botón "Finalizar compra" destacado).

## Componentes visuales

### Ítem del carrito (fila)
- **Imagen pequeña** (80×80px aprox.) a la izquierda, fondo Silk Cream.
- **Nombre del producto** (Montserrat Medium, Intense Cocoa) + atributos de variante (color, material, tamaño en Montserrat Regular, opacidad) + SKU pequeño.
- **Precio unitario** (Montserrat Regular, Intense Cocoa).
- **Selector de cantidad**: input con botones `−` / `+`. Deshabilitar `+` si se alcanza el stock.
- **Subtotal** (Montserrat SemiBold, Intense Cocoa, alineado a la derecha).
- **Botón eliminar**: ícono papelera o corazón tachado, Intense Cocoa, hover Soft Gold o rojo sutil.
- Separadores sutiles (Soft Sand) entre ítems.

### Resumen de compra (columna derecha, sticky)
- Card con fondo Soft Sand o Silk Cream con borde sutil.
- **Título** "Resumen del pedido" en Chillax Semibold o Montserrat SemiBold.
- **Cantidad total de productos** (Montserrat Regular).
- **Subtotal** (Montserrat Regular).
- **Total a pagar** (Chillax Semibold o Montserrat Bold, tamaño destacado, Intense Cocoa).
- Botón **"Finalizar compra"** ancho completo, fondo Intense Cocoa, texto Silk Cream, Montserrat SemiBold. Hover Soft Gold.
- Botón **"Continuar comprando"** como enlace secundario debajo (Montserrat Medium, Intense Cocoa, sin fondo).

### Estado vacío
- Centrado en la página.
- Ilustración minimalista (bolsa de compra vacía).
- Mensaje "Tu carrito está vacío" en Chillax Semibold.
- Botón "Explorar productos" (Intense Cocoa, borde) que lleva a `/shop`.

### Alertas de stock
- Si un ítem cambió de disponibilidad, banner sutil (fondo Soft Sand, borde Soft Gold) con mensaje "La disponibilidad de un producto cambió. Revisa tu carrito.".

## Paleta de colores (ver `00-design-tokens.md`)

- Fondo: **Silk Cream** `#FFF8CF`
- Contraste (cards, dividers): **Soft Sand** `#E9DED3`
- Texto, botones, íconos: **Intense Cocoa** `#372621`
- Hover, activos, detalles: **Soft Gold** `#D2AE36`
- Proporción 70/20/10.

## Tipografía (ver `00-design-tokens.md`)

- Títulos ("Resumen del pedido", estado vacío): **Chillax** Semibold.
- Nombres de producto, botones, etiquetas: **Montserrat** Medium/SemiBold.
- Precios, cuerpo, captions: **Montserrat** Regular/SemiBold.

## Estilo visual

- Limpio, minimalista, sensación de orden.
- Lista de ítems con separadores sutiles, sin bordes pesados.
- Resumen de compra destacado pero no invasivo (card con fondo Soft Sand).
- Mucho espacio en blanco.
- El total debe ser lo más visible de la columna derecha.
- Iconografía lineal, Intense Cocoa.

## Estados

- **Carrito vacío**: ilustración + mensaje + CTA a `/shop`.
- **Cantidad mínima (1)**: botón `−` deshabilitado.
- **Cantidad máxima (stock)**: botón `+` deshabilitado + mensaje sutil.
- **Ítem agotado**: badge "Agotado" + botón `+` deshabilitado + nota.
- **Actualización de cantidad**: subtotal y total se actualizan en tiempo real (sin recargar).
- **Eliminación**: ítem desaparece con animación sutil, total se recalcula.

## Breakpoints

Diseñar primero para **desktop** (`lg`/`xl`) con dos columnas (lista + resumen sticky). En tablet (`md`) y móvil (`sm`), una sola columna. Ver `00-design-tokens.md` sección Responsive.

---

# Para implementación (NO pasar a Stitch)

## Contexto del proyecto

- **Carrito guest + user** (D2): los visitantes no autenticados pueden tener carrito. Se persiste por sesión/cookie. Al hacer login, se fusiona con el carrito del usuario.
- **Stock visible** (D4): no se puede agregar más cantidad que el stock disponible. Si el stock cambió desde que se agregó, informar al usuario.
- **Moneda** (D5): precios en moneda de contexto.
- El carrito opera sobre variantes específicas (no productos genéricos).

## Acciones del usuario

El usuario podrá:

- Modificar la cantidad de un producto.
- Eliminar productos del carrito.
- Continuar explorando el catálogo.
- Proceder al Checkout (`/checkout`).

## Validaciones

- No permitir cantidades inferiores a 1.
- No permitir cantidades superiores al stock disponible.
- Actualizar automáticamente los totales al modificar el carrito.
- Mostrar un mensaje cuando el carrito se encuentre vacío.
- Si un producto ya no está disponible o su stock cambió, informar al usuario.

## Datos requeridos

**Dinámicos (del backend):**

- Ítems del carrito: producto (nombre, imagen, slug), variante (SKU, atributos, stock), cantidad, precio unitario.
- Totales calculados (subtotal, total).
- Estado de cada variante (disponible / agotado / stock insuficiente).

## Consideraciones técnicas

- Actualizar el resumen del pedido sin recargar la página (Livewire).
- Persistir el carrito para usuarios autenticados (DB) y visitantes (sesión/cookie).
- Recalcular automáticamente los totales ante cualquier modificación.
- Al hacer login, fusionar el carrito de invitado con el del usuario.
- Responsive: desktop-first con experiencia móvil equivalente (ver `00-design-tokens.md`).

## Fuera de alcance (diferido)

- **Descuentos / cupones** — requiere F06 (cupones). No se incluye en la primera versión del carrito.
- **Costo de envío** — requiere lógica de envíos (no implementada). El carrito no calcula envío.
- **Productos recomendados basados en el carrito** — mejora futura.
- **Barra de progreso para envío gratuito** — mejora futura.
- **Guardar carrito para comprar después** — mejora futura.
