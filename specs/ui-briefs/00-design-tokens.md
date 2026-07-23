# Design Tokens

## Objetivo

Definir las reglas visuales fundamentales del proyecto para garantizar una interfaz consistente, elegante y alineada con la identidad de Leen Handbags.

Este documento deberá utilizarse como referencia para el diseño de todas las vistas del sistema.

---

# Identidad de Marca

La interfaz deberá transmitir los siguientes atributos:

- Elegancia.
- Minimalismo.
- Exclusividad.
- Sofisticación.
- Atemporalidad.
- Cercanía.
- Calidad.

La experiencia visual deberá priorizar la simplicidad, el equilibrio y el uso inteligente del espacio en blanco.

---

# Paleta de Colores

## Silk Cream

Color principal de la interfaz.

**HEX**

```text
#FFF8CF
```

Uso:

- Fondo principal.
- Secciones generales.
- Superficies predominantes.

---

## Soft Sand

Color secundario.

**HEX**

```text
#E9DED3
```

Uso:

- Fondos alternativos.
- División de secciones.
- Tarjetas.
- Bloques de contenido.

---

## Intense Cocoa

Color estructural.

**HEX**

```text
#372621
```

Uso:

- Texto principal.
- Títulos.
- Botones primarios.
- Iconografía.
- Elementos estructurales.

---

## Soft Gold

Color de acento.

**HEX**

```text
#D2AE36
```

Uso:

- Hover.
- Estados activos.
- Iconos destacados.
- Detalles decorativos.
- Indicadores.
- Enlaces importantes.

No deberá utilizarse como color predominante de la interfaz.

---

# Proporción de Uso del Color

La interfaz deberá mantener una distribución equilibrada de la paleta.

Aproximadamente:

- 70% Silk Cream.
- 20% Intense Cocoa.
- 10% Soft Gold.

Soft Sand deberá utilizarse únicamente para generar contraste entre secciones.

---

# Colores Semánticos

Colores para estados de validación, feedback y comunicación funcional. Deben mantener tonos cálidos consistentes con la paleta de marca.

## Error

Color para mensajes de error y validaciones fallidas.

**HEX**

```text
#B33A3A
```

Uso:

- Mensajes de error.
- Validaciones fallidas.
- Indicadores de fortaleza baja (contraseña débil).
- Campos inválidos.

---

## Éxito

Color para confirmaciones y operaciones completadas.

**HEX**

```text
#5A8A4A
```

Uso:

- Confirmaciones.
- Validaciones exitosas.
- Indicadores de fortaleza alta (contraseña fuerte).
- Operaciones completadas.

---

## Advertencia

Se utiliza **Soft Gold** (`#D2AE36`) ya definido en la paleta de marca.

Uso:

- Advertencias no críticas.
- Indicadores de fortaleza media.
- Mensajes informativos que requieren atención.

---

# Tipografía

## Resumen de familias

| Familia | Tipo | Uso | Pesos disponibles |
|---------|------|-----|-------------------|
| **Chillax** | Sans-serif (variable, rango 200–700) | Logo, grandes títulos, encabezados principales | Extralight (200), Light (300), Regular (400), Medium (500), Semibold (600), Bold (700) |
| **Montserrat** | Sans-serif (variable, eje `wght`) | Navegación, párrafos, formularios, botones, tarjetas, productos, tablas, panel admin | Thin (100), ExtraLight (200), Light (300), Regular (400), Medium (500), SemiBold (600), Bold (700), ExtraBold (800), Black (900) — más variantes Italic |
| **La Belle Aurore** | Script decorativa | Frases destacadas, campañas, detalles decorativos, elementos emocionales | Regular (400) |

Montserrat será la tipografía predominante del sistema. La Belle Aurore **no** debe utilizarse para textos largos ni contenido funcional.

---

## Logo

Fuente:

```text
Chillax
```

Uso:

- Logo.
- Grandes títulos.
- Encabezados principales.

Pesos recomendados: Medium (500) para logo, Semibold (600) o Bold (700) para grandes títulos.

---

## Tipografía principal

Fuente:

```text
Montserrat
```

Uso:

- Navegación.
- Párrafos.
- Formularios.
- Botones.
- Tarjetas.
- Productos.
- Tablas.
- Panel administrativo.

Pesos recomendados:

- Navegación, botones, etiquetas: Medium (500) o SemiBold (600).
- Párrafos, cuerpo de texto: Regular (400).
- Texto secundario, captions: Light (300) o Regular (400).
- Énfasis en cuerpo: SemiBold (600) o Bold (700).

---

## Tipografía de énfasis

Fuente:

```text
La Belle Aurore
```

Uso:

- Frases destacadas.
- Campañas.
- Detalles decorativos.
- Elementos emocionales.

Peso disponible: Regular (400).

No deberá utilizarse para textos largos ni contenido funcional.

---

## Formatos disponibles

Los archivos de fuente están disponibles en:

| Formato | Uso recomendado |
|---------|-----------------|
| `.woff2` | Web (mejor compresión, recomendado) |
| `.woff` | Web (compatibilidad amplia) |
| `.ttf` | Diseño / instalación local |
| `.otf` | Diseño / instalación local |

Para web, priorizar `.woff2` y `.woff` con `@font-face` o cargados vía CSS.

---

# Escala Tipográfica

Definición de tamaños, pesos y line-heights para cada nivel de la jerarquía tipográfica.

## Display LG

| Propiedad | Desktop | Móvil |
|-----------|---------|-------|
| Familia | Chillax | Chillax |
| Tamaño | 64px | 40px |
| Peso | 300 (Light) | 300 (Light) |
| Line-height | 1.1 | 1.2 |
| Letter-spacing | -0.02em | -0.01em |

Uso: títulos principales de página, hero headlines.

---

## Headline MD

| Propiedad | Valor |
|-----------|-------|
| Familia | Chillax |
| Tamaño | 32px |
| Peso | 400 (Regular) |
| Line-height | 1.3 |

Uso: subtítulos de sección, títulos de bloques.

---

## Headline SM

| Propiedad | Valor |
|-----------|-------|
| Familia | Chillax |
| Tamaño | 24px |
| Peso | 500 (Medium) |
| Line-height | 1.4 |

Uso: títulos de tarjeta, nombres de producto, precios destacados.

---

## Body LG

| Propiedad | Valor |
|-----------|-------|
| Familia | Montserrat |
| Tamaño | 18px |
| Peso | 400 (Regular) |
| Line-height | 1.6 |

Uso: descripciones de producto, texto editorial.

---

## Body MD

| Propiedad | Valor |
|-----------|-------|
| Familia | Montserrat |
| Tamaño | 16px |
| Peso | 400 (Regular) |
| Line-height | 1.6 |

Uso: párrafos, cuerpo de texto, descripciones.

---

## Label Caps

| Propiedad | Valor |
|-----------|-------|
| Familia | Montserrat |
| Tamaño | 12px |
| Peso | 600 (SemiBold) |
| Line-height | 1.0 |
| Letter-spacing | 0.1em |

Uso: navegación, etiquetas, categorías, captions en mayúsculas con tracking amplio.

---

## Accent Script

| Propiedad | Valor |
|-----------|-------|
| Familia | La Belle Aurore |
| Tamaño | 28px |
| Peso | 400 (Regular) |
| Line-height | 1.0 |

Uso: frases decorativas, detalles emocionales, firma de marca. Usar con moderación.

---

# Logo

El sistema deberá soportar las diferentes variantes oficiales del logotipo.

Versiones disponibles:

- Brown.
- Cream.
- White.
- Black.

La selección del logo dependerá del contraste con el fondo.

Como regla general:

- Brown sobre fondos claros.
- Cream sobre fondos oscuros.
- White sobre fotografías o fondos oscuros.
- Black únicamente cuando el contexto lo requiera.

---

# Espacio en Blanco

La interfaz deberá priorizar el uso de espacios amplios entre secciones y componentes.

Evitar:

- Interfaces saturadas.
- Exceso de contenido visible simultáneamente.
- Bloques excesivamente compactos.

La sensación general deberá ser limpia y premium.

---

# Layout y Grid

## Grid principal

- **12 columnas** en desktop.
- **Max-width**: 1440px.
- **Márgenes laterales**: 80px en desktop, 20px en móvil.
- **Gutter** (separación entre columnas): 24px.

## Ritmo vertical

- **Section-gap**: 120px entre secciones principales.
- Esto asegura que las historias de producto o secciones editoriales no se sientan amontonadas.

## Grid de productos

- Máximo **3 columnas** para listados de productos.
- Excepcionalmente 2 columnas para destacar imágenes grandes.
- Evitar 4 o más columnas — las imágenes deben ser grandes y los detalles visibles.

---

# Spacing

Tokens de espaciado para mantener consistencia vertical y horizontal.

| Token | Valor | Uso |
|-------|-------|-----|
| `container-max` | 1440px | Ancho máximo del contenedor principal |
| `gutter` | 24px | Separación entre columnas del grid |
| `margin-desktop` | 80px | Márgenes laterales en desktop |
| `margin-mobile` | 20px | Márgenes laterales en móvil |
| `section-gap` | 120px | Separación vertical entre secciones |
| `stack-lg` | 32px | Separación entre elementos grandes |
| `stack-md` | 16px | Separación entre elementos medianos |
| `stack-sm` | 8px | Separación entre elementos pequeños |

---

# Estilo Visual

La interfaz deberá seguir los siguientes principios:

- Diseño minimalista.
- Jerarquía visual clara.
- Pocas distracciones.
- Uso moderado del color.
- Imágenes de alta calidad.
- Componentes simples.
- Bordes discretos.
- Sombras suaves.

---

# Elevación y Profundidad

El sistema prioriza **layering tonal** sobre sombras para crear profundidad.

## Superficies

Usar `Soft Sand` (`#E9DED3`) para tarjetas o secciones inset, creando profundidad sin drop shadows.

## Sombras

Cuando sea necesario (ej: hover sobre una product card), usar una sombra suave y difusa:

```text
0px 10px 30px rgba(55, 38, 33, 0.05)
```

La sombra debe sentirse como luz ambiental, no como un efecto digital.

## Bordes

Usar bordes finos de 1px en `Intense Cocoa` (`#372621`) con 10-15% de opacidad para definir límites de UI como campos de entrada y separadores de navegación.

---

# Formas y Radius

El lenguaje de formas es **sharp to soft** — formas rectilíneas dominan el layout.

| Token | Valor | Uso |
|-------|-------|-----|
| `sm` | 0.125rem (2px) | Elementos pequeños |
| `DEFAULT` | 0.25rem (4px) | Botones, inputs — radio por defecto |
| `md` | 0.375rem (6px) | Elementos medianos |
| `lg` | 0.5rem (8px) | Cards, contenedores |
| `xl` | 0.75rem (12px) | Elementos grandes |
| `full` | 9999px | Elementos circulares |

**Imágenes de producto**: siempre 0px (sharp) para preservar la integridad editorial y fotográfica.

---

# Iconografía

Los iconos deberán:

- Ser simples.
- Mantener un grosor uniforme.
- Utilizar principalmente Intense Cocoa.
- Emplear Soft Gold únicamente para estados destacados.

---

# Imágenes

Las imágenes deberán ser el principal elemento visual de la marca.

Priorizar:

- Fotografías de alta calidad.
- Productos.
- Lifestyle.
- Materiales.
- Detalles.

Evitar:

- Ilustraciones innecesarias.
- Elementos gráficos excesivos.
- Decoraciones que distraigan del producto.

---

# Responsive

Toda la interfaz deberá diseñarse bajo una filosofía responsive con **experiencia desktop como ciudadana de primera clase**.

## Prioridad

Esta es una **tienda online de marca premium**, no una app móvil adaptada. El usuario principal se sentará frente a una computadora a explorar el catálogo y comprar cómodamente. La experiencia desktop **no** debe sentirse como una app móvil ampliada.

- **Desktop** es el objetivo principal de diseño: aprovechar el espacio, sidebar de filtros, grids amplios, layouts de dos columnas, hover states, navegación rica.
- **Móvil y tablet** deben ofrecer una experiencia equivalente en comodidad y claridad, no una versión degradada.

## Consistencia

Las reglas visuales (paleta, tipografías, espacio en blanco, tono) deberán mantenerse consistentes en:

- Mobile.
- Tablet.
- Desktop.

## Breakpoints (Tailwind v4)

| Breakpoint | Min-width | Uso |
|------------|-----------|-----|
| `sm` | 640px | Teléfonos grandes en horizontal |
| `md` | 768px | Tablets |
| `lg` | 1024px | Desktop pequeño / laptop |
| `xl` | 1280px | Desktop estándar |
| `2xl` | 1536px | Desktop grande |

Los layouts se diseñan primero para desktop (`lg`/`xl`) y luego se adaptan a tablet/mobile con breakpoints descendentes.

---

# Principios de Diseño

Toda decisión visual deberá responder a los siguientes principios:

- La simplicidad tiene prioridad sobre la complejidad.
- El producto es el protagonista.
- Menos elementos generan una percepción más premium.
- El contenido debe respirar mediante el uso de espacios en blanco.
- La consistencia visual tiene prioridad sobre la variedad.
- Cada componente debe tener un propósito claro dentro de la interfaz.

---

# Componentes

Especificaciones de los componentes base del sistema.

## Botones

- **Primario**: fondo `Intense Cocoa`, texto `Silk Cream`, radius 4px. Hover: fondo → `Soft Gold`.
- **Secundario**: border 1px `Intense Cocoa`, sin fill. Hover: fondo `Soft Sand`.
- Tipografía: `Montserrat` Medium o SemiBold.

---

## Campos de entrada

- Diseño minimalista con **solo border inferior** de `Intense Cocoa` al 30% de opacidad.
- En focus, el border pasa a 100% de opacidad.
- Sin border en los lados ni arriba.
- Tipografía: `Montserrat` Regular.

---

## Product Cards

- Sin bordes.
- La imagen ocupa el ancho completo (aspecto 4:5).
- Categoría en `label-caps`.
- Nombre de producto en `body-md`.
- Precio en `headline-sm`.
- Hover: zoom suave de la imagen (`duration-700`).

---

## Navegación

- Top nav con `label-caps` y tracking amplio.
- Estado activo: subrayado de 2px en `Soft Gold`.
- Logo centrado en la barra de navegación.
- Iconos de acción a la derecha (búsqueda, favoritos, carrito).

---

## Chips y Badges

- Rellenos con `Soft Sand`, texto `Intense Cocoa`.
- Tipografía: `Montserrat` Bold 10px.
- Uso: "New Arrival", "Limited Edition", "Agotado", estados de stock.

---

## Iconografía

- Tamaño: 24px.
- Grosor de trazo: 1.5pt.
- Color principal: `Intense Cocoa`.
- `Soft Gold` únicamente para estados destacados o activos.
