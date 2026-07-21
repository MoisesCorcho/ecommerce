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
