# Brief UI: Home

> **Vista:** Home · **Ruta sugerida:** `/`
> **Depende de:** F01-S (storefront), F03 (carrito), F08 (wishlist)
> **Estado:** Pendiente de F01-S

---

# Para Stitch (diseño visual)

> **Prompt para Google Stitch.** Pasar este bloque + `00-design-tokens.md`.
> No incluir la sección "Para implementación" al final.

## Objetivo de la vista

Página principal de la tienda Leen Handbags. Presentar la identidad de la marca, destacar productos relevantes y guiar al usuario hacia el catálogo para que compre.

## Estructura y secciones (de arriba a abajo)

1. **Hero** — banner principal a pantalla completa o altura significativa (60–70vh). Imagen de fondo de alta calidad (lifestyle o producto destacado). Título con la esencia de la marca, subtítulo breve, y un CTA principal ("Ver colección" / "Explorar productos") que lleve a `/shop`. Contenido estático.
2. **Categorías** — grid de categorías del catálogo. 3–4 columnas en desktop, 2 en tablet, 1–2 en móvil. Tarjetas con imagen + nombre. Hover sutil.
3. **Productos destacados** — grid de 4 columnas en desktop, 2–3 en tablet, 2 en móvil. Tarjetas de producto con imagen, nombre, precio, botones de carrito y favoritos (visibles o en hover). Sección con título "Destacados" o "Nuestros favoritos".
4. **Historia de la marca** — sección de ancho completo, dos columnas: imagen a un lado, texto al otro. Breve introducción a la identidad de Leen Handbags. CTA secundario hacia `/about-us`.
5. **Beneficios** — fila de 4 íconos lineales con texto corto (envíos, garantía, soporte, métodos de pago). Fondo Soft Sand para generar contraste.
6. **Instagram** — grid de imágenes cuadradas (estilo feed) con overlay al hover. Enlace al perfil de Instagram.
7. **Footer** — compartido con todas las vistas.

## Paleta de colores (ver `00-design-tokens.md` para detalle)

- Fondo principal: **Silk Cream** `#FFF8CF`
- Fondo de contraste (sección beneficios): **Soft Sand** `#E9DED3`
- Texto, botones primarios, íconos: **Intense Cocoa** `#372621`
- Hover, estados activos, detalles: **Soft Gold** `#D2AE36`
- Proporción: 70% Silk Cream, 20% Intense Cocoa, 10% Soft Gold. Soft Sand solo para contraste entre secciones.

## Tipografía (ver `00-design-tokens.md` para detalle)

- **Títulos grandes / Hero**: Chillax (Semibold o Bold).
- **Navegación, botones, tarjetas, cuerpo**: Montserrat (Regular para cuerpo, Medium/SemiBold para botones y etiquetas).
- **Frases decorativas / Hero subtítulo emocional**: La Belle Aurore (Regular) — usar con moderación, solo para frases cortas impactantes.

## Estilo visual

- **Minimalista, premium, artesanal.** Sensación de lujo atemporal.
- Mucho espacio en blanco. El contenido debe respirar.
- Pocas distracciones. El producto es el protagonista.
- Bordes discretos, sombras suaves.
- Fotografías de alta calidad (lifestyle, productos, materiales, detalles).
- Iconografía simple, grosor uniforme, en Intense Cocoa (Soft Gold solo para estados destacados).
- Logo: usar variante **Brown** sobre Silk Cream, **White** sobre fotografías/fondos oscuros.

## Estados

- **Producto agotado**: badge "Agotado" sobre la imagen, botón de carrito deshabilitado.
- **Hover en tarjetas**: elevación sutil + botones de acción visibles.
- **Loading**: skeleton loaders para imágenes y productos.

## Breakpoints

Diseñar primero para **desktop** (`lg`/`xl`), luego adaptar a tablet (`md`) y móvil (`sm`). La experiencia desktop es prioritaria — no debe sentirse como una app móvil ampliada. Ver `00-design-tokens.md` sección Responsive.

---

# Para implementación (NO pasar a Stitch)

## Contexto del proyecto

- **Contenido estático** (D1): los textos del Hero, la historia de la marca, los beneficios y los enlaces a redes sociales viven en plantillas Blade hardcodeadas. **No** son editables desde el panel admin.
- **Categorías y productos** son dinámicos (vienen del catálogo gestionado en F01).
- El criterio de "productos destacados" se define como: **selección manual** mediante un flag en el modelo de producto (requiere extender F01 o crear feature dedicada).

## Datos requeridos

**Dinámicos (del backend):**

- Categorías existentes (`Category`: nombre, slug, imagen si existe).
- Productos destacados (`Product` + `ProductVariant` + `ProductVariantPrice`: imagen, nombre, precio, slug).

**Estáticos (en Blade):**

- Contenido del Hero (título, subtítulo, imagen, CTA).
- Texto de historia de la marca.
- Lista de beneficios.
- Enlaces a redes sociales.

## Acciones del usuario

El usuario podrá:

- Navegar hacia las categorías (`/shop?category=<slug>`).
- Acceder al catálogo completo (`/shop`).
- Visualizar un producto (`/products/<slug>`).
- Agregar productos al carrito (requiere F03).
- Agregar productos a Favoritos (requiere F08, requiere autenticación).
- Acceder a la historia de la marca (`/about-us`).
- Visitar las redes sociales (enlace externo).

## Consideraciones técnicas

- Lazy Loading en imágenes de productos y categorías.
- Responsive: desktop-first con experiencia móvil equivalente (ver `00-design-tokens.md`).
- Las acciones de "agregar al carrito" y "agregar a favoritos" deben ser asíncronas (sin recargar página) vía Livewire.
- El contenido estático se edita directamente en plantillas Blade (no requiere panel admin).

## Fuera de alcance (diferido)

- **Administración del contenido desde panel** — el contenido institucional del Home (Hero, historia, beneficios, redes) es estático (D1); las secciones de catálogo (categorías, productos destacados) son dinámicas vía Livewire. Cambios de contenido institucional requieren editar Blade.
- **Integración con API de Instagram** — solo enlaces externos por ahora.
- **Promociones temporales / banners dinámicos** — requiere CMS (no planeado).
- **Testimonios de clientes** — requiere feature de reviews (F07).
- **Blog o novedades** — fuera del roadmap actual.
- **Campañas estacionales** — fuera del roadmap actual.
