# UI Briefs — Vistas del frontend

## Qué es esto

Esta carpeta contiene **briefs de UI** (no specs formales SDD) que describen las responsabilidades, contenido y comportamiento esperado de cada vista del frontend del ecommerce.

**No son features formales.** Las features de producto se especifican en `specs/features/<NN-slug>/` siguiendo el flujo SDD (requirements → design → tasks). Estos briefs son insumo para:

1. **Guiar la creación de features SDD** — saber qué responsabilidades tiene cada vista al escribir `requirements.md` y `design.md`.
2. **Guiar a Google Stitch** — proporcionar estructura y contenido para que los diseños visuales cubran lo mínimo necesario de cada vista.

## Cómo usar estos briefs

### Para crear features SDD

1. Leer el brief de la vista correspondiente.
2. Leer `specs/_global/01-product-and-roadmap.md` para verificar dependencias.
3. Crear `specs/features/<NN-slug>/` con `requirements.md` (EARS), `design.md`, `tasks.md`.
4. El brief es insumo; la spec formal es el contrato.

### Para Google Stitch

Cada brief está dividido en dos secciones:

| Sección | Contenido | ¿Se pasa a Stitch? |
|---------|-----------|---------------------|
| **Para Stitch (diseño visual)** | Objetivo, estructura, layout, componentes visuales, paleta, tipografía, estilo, estados, breakpoints | **Sí** — copiar y pegar esta sección |
| **Para implementación** | Contexto, datos del backend, validaciones, consideraciones técnicas, fuera de alcance | **No** — es para desarrollo |

**Flujo con Stitch:**

1. Pasar el `00-design-tokens.md` (manual de marca: colores, tipografías, estilo, principios).
2. Pasar la sección "Para Stitch" del brief de la vista que quieres diseñar.
3. Stitch genera el diseño visual basado en esos dos inputs.

No es necesario pasarle la sección "Para implementación" — contiene datos del backend, validaciones de servidor y consideraciones técnicas que no aportan al diseño visual y solo consumirían tokens.

## Documentos de referencia

| Documento | Propósito |
|-----------|-----------|
| `00-design-tokens.md` | Manual de marca: paleta de colores, tipografías, estilo visual, principios de diseño. **Pasar siempre a Stitch.** |
| `README.md` | Este documento: cómo usar los briefs, decisiones de producto, mapeo al roadmap. |
| `FUENTES.md` | Inventario de archivos de fuente disponibles (formatos, pesos, familias). |

## Decisiones de producto (aplican a todos los briefs)

| # | Decisión | Impacto |
|---|----------|---------|
| D1 | **Sin CMS** — Home, FAQ y About Us son estáticos en plantillas Blade. El admin NO edita contenido institucional. | Home, FAQ, About Us no requieren entidades ni Filament Resources. Cambios de contenido requieren editar código. |
| D2 | **Carrito guest + user** — visitantes no autenticados pueden agregar al carrito y comprar. | Carrito persiste por sesión/cookie para guests; merge al login. |
| D3 | **Variantes con atributos estructurados** — color, material, tamaño como campos separados. | Permite filtros facetados en Shop y selectores visuales en Producto. Requiere extender modelo de datos. |
| D4 | **Stock visible** — las vistas muestran inventario disponible y estado "Agotado". | Requiere campo `stock` en modelo. Validación de cantidad en carrito y checkout. |
| D5 | **Moneda** — COP (pesos enteros) y EUR (centavos). Sin floats. | Ver `config('ecommerce.default_currency')`. |
| D6 | **Single-vendor** — no es marketplace. Un solo vendedor. | Sin comisiones, sin storefront por vendedor. |

## Mapeo briefs → roadmap

| Brief | Vista | Feature roadmap | Estado |
|-------|-------|-----------------|--------|
| `01-home.md` | Home | F01-S (storefront) | Diferido |
| `02-shop.md` | Shop (listado catálogo) | F01-S | Diferido |
| `03-producto.md` | Detalle de producto | F01-S | Diferido |
| `04-carrito-de-compra.md` | Carrito | F03 | No iniciada |
| `05-checkout.md` | Checkout | F04 | No iniciada |
| `06-login.md` | Login | F02 | No iniciada |
| `07-registro.md` | Registro | F02 | No iniciada |
| `08-perfil.md` | Perfil de usuario | F02 + F08 | No iniciada |
| `09-lista-de-deseados.md` | Wishlist | F08 | No iniciada |
| `10-contacto.md` | Contacto | Sin feature (estático) | — |
| `11-faq.md` | FAQ | Sin feature (estático) | — |
| `12-about-us.md` | About Us | Sin feature (estático) | — |
| `13-admin-panel.md` | Panel administrativo | F01 (completa) + futuras | ✅ F01 completa |

## Stack frontend

- **Livewire v4** (multi-file components / MFC) para interactividad.
- **Tailwind CSS v4** para estilos.
- **Blade** para plantillas.
- **Filament v5** solo para admin (no storefront).

## Convenciones de los briefs

Cada brief sigue esta estructura dividida en dos secciones principales:

### Para Stitch (diseño visual)

| Subsección | Propósito |
|------------|-----------|
| **Objetivo de la vista** | Qué resuelve la vista para el usuario |
| **Estructura y layout** | Cómo se organiza la vista (columnas, secciones, flujo) |
| **Componentes visuales** | Elementos UI con detalle de estilo (colores, tipografía, estados) |
| **Paleta de colores** | Referencia a `00-design-tokens.md` + colores específicos de la vista |
| **Tipografía** | Referencia a `00-design-tokens.md` + uso de fuentes en la vista |
| **Estilo visual** | Tono, principios, sensación general |
| **Estados** | Hover, loading, error, vacío, etc. |
| **Breakpoints** | Cómo adapta el layout a desktop/tablet/móvil |

### Para implementación (NO pasar a Stitch)

| Subsección | Propósito |
|------------|-----------|
| **Contexto del proyecto** | Decisiones D1-D6 que aplican a la vista |
| **Acciones del usuario** | Qué puede hacer el usuario |
| **Validaciones** | Reglas de validación |
| **Datos requeridos** | Qué datos necesita del backend |
| **Consideraciones técnicas** | Notas de implementación Livewire/Blade |
| **Fuera de alcance (diferido)** | Lo que NO se incluye en la primera versión |

## Orden de implementación sugerido

Ver `specs/_global/01-product-and-roadmap.md` para el orden formal. Resumen:

1. **F01** (admin catálogo) — ✅ Completa
2. **F02** (cuentas y direcciones) — login, registro, perfil
3. **F01-S** (storefront) — home, shop, producto
4. **F03** (carrito) — carrito de compra
5. **F04** (checkout y órdenes) — checkout
6. **F05** (pagos) — métodos de pago en checkout
7. **F08** (wishlist) — lista de deseados, sección en perfil
8. **F06** (cupones) — descuentos en carrito/checkout
9. **F07** (reviews) — opiniones en producto

Las páginas estáticas (contacto, FAQ, about us) pueden implementarse en cualquier momento.
