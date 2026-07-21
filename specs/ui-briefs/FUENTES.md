# Listado de Fuentes — Leen Handbags

Inventario de archivos de fuente en el directorio `Fonts/`.

## Tipos de archivo encontrados

| Extensión | Nombre | Uso principal |
|-----------|--------|---------------|
| `.ttf` | TrueType Font | Instalación en sistemas operativos y diseño |
| `.otf` | OpenType Font | Instalación en sistemas operativos y diseño |
| `.woff` | Web Open Font Format | Uso web (compatible con la mayoría de navegadores) |
| `.woff2` | Web Open Font Format 2 | Uso web (mejor compresión, recomendado) |
| `.eot` | Embedded OpenType | Uso web legacy (Internet Explorer antiguo) |

---

## Familias tipográficas

### 1. La Belle Aurore
Fuente decorativa/script.

| Archivo | Formato | Ruta |
|---------|---------|------|
| LaBelleAurore-Regular | TTF | `./` |

---

### 2. Montserrat
Fuente sans-serif variable (eje `wght`). Licencia: SIL Open Font License.

#### Variable Font
| Archivo | Formato | Ruta |
|---------|---------|------|
| Montserrat-VariableFont_wght | TTF | `./` |
| Montserrat-Italic-VariableFont_wght | TTF | `./` |

#### Static Fonts (en `static/`)
| Archivo | Formato | Ruta |
|---------|---------|------|
| Montserrat-Thin | TTF | `static/` |
| Montserrat-ThinItalic | TTF | `static/` |
| Montserrat-ExtraLight | TTF | `static/` |
| Montserrat-ExtraLightItalic | TTF | `static/` |
| Montserrat-Light | TTF | `static/` |
| Montserrat-LightItalic | TTF | `static/` |
| Montserrat-Regular | TTF | `static/` |
| Montserrat-Italic | TTF | `static/` |
| Montserrat-Medium | TTF | `static/` |
| Montserrat-MediumItalic | TTF | `static/` |
| Montserrat-SemiBold | TTF | `static/` |
| Montserrat-SemiBoldItalic | TTF | `static/` |
| Montserrat-Bold | TTF | `static/` |
| Montserrat-BoldItalic | TTF | `static/` |
| Montserrat-ExtraBold | TTF | `static/` |
| Montserrat-ExtraBoldItalic | TTF | `static/` |
| Montserrat-Black | TTF | `static/` |
| Montserrat-BlackItalic | TTF | `static/` |

---

### 3. Chillax
Fuente sans-serif variable (eje `wght`, rango 200–700). Ubicada en `Fonts/Fonts/`.

#### OpenType (en `Fonts/OTF/`)
| Archivo | Formato |
|---------|---------|
| Chillax-Extralight | OTF |
| Chillax-Light | OTF |
| Chillax-Regular | OTF |
| Chillax-Medium | OTF |
| Chillax-Semibold | OTF |
| Chillax-Bold | OTF |

#### TrueType Variable (en `Fonts/TTF/`)
| Archivo | Formato |
|---------|---------|
| Chillax-Variable | TTF |

#### Web Fonts (en `Fonts/WEB/fonts/`)
Cada peso está disponible en los formatos: `.eot`, `.ttf`, `.woff`, `.woff2`.
Incluye también `Chillax-Variable` en los cuatro formatos web.

| Peso | EOT | TTF | WOFF | WOFF2 |
|------|-----|-----|------|-------|
| Extralight | ✓ | ✓ | ✓ | ✓ |
| Light | ✓ | ✓ | ✓ | ✓ |
| Regular | ✓ | ✓ | ✓ | ✓ |
| Medium | ✓ | ✓ | ✓ | ✓ |
| Semibold | ✓ | ✓ | ✓ | ✓ |
| Bold | ✓ | ✓ | ✓ | ✓ |
| Variable | ✓ | ✓ | ✓ | ✓ |

> CSS de inclusión web: `Fonts/WEB/css/chillax.css`

---

## Resumen por tipo de archivo

| Tipo | Cantidad | Familias |
|------|----------|----------|
| `.ttf` | 22 | La Belle Aurore, Montserrat, Chillax |
| `.otf` | 6 | Chillax |
| `.woff` | 7 | Chillax |
| `.woff2` | 7 | Chillax |
| `.eot` | 7 | Chillax |

## Licencias
- **Montserrat**: SIL Open Font License 1.1 (ver `OFL.txt`).
- **Chillax**: revisar términos del proveedor antes de uso comercial.
- **La Belle Aurore**: revisar términos del proveedor antes de uso comercial.

## Notas
- El directorio `__MACOSX/` contiene metadatos residuales de macOS y puede ignorarse o eliminarse.
- Existen archivos `.DS_Store` (metadata de macOS) que pueden eliminarse sin afectar las fuentes.
