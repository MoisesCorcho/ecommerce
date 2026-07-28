# 14 — Guía de Revisión UI: Identidad de Marca y Decisiones de Diseño

> **Propósito:** Documento de referencia para ejecutar una revisión exhaustiva de todas las vistas implementadas. Establece la identidad de marca, las decisiones de diseño mantenidas, y el alcance de lo que la revisión debe cuestionar vs. lo que NO debe cambiar.

---

## 1. Identidad de Marca — Leen Handbags

### Atributos de marca

| Atributo | Significado en UI |
|----------|-------------------|
| Elegancia | Tipografías con peso medio/bajo, spacing generoso, transiciones suaves |
| Minimalismo | Pocos elementos por vista, máximo 3 niveles de jerarquía visibles |
| Exclusividad | Imágenes grandes, poco contenido textual, whitespace como lenguaje |
| Sofisticación | Paleta neutra/cálida, sin colores saturados, sin gradientes llamativos |
| Atemporalidad | Sin tendencias de diseño pasajeras (no glassmorphism, no neón, no gradients) |
| Cercanía | Tonos tierra cálidos, tipografía sans-serif legible, espacios no intimidantes |
| Calidad | Imágenes de alta resolución, tipografía impecable, alineación perfecta |

### Paleta de colores

| Color | HEX | Rol | Proporción en UI |
|-------|-----|-----|-------------------|
| **Silk Cream** | `#FFF8CF` | Fondo principal, superficies predominantes | ~70% |
| **Soft Sand** | `#E9DED3` | Fondos alternativos, tarjetas, dividers, contraste | Separador entre secciones |
| **Intense Cocoa** | `#372621` | Texto, títulos, botones primarios, iconografía, estructura | ~20% |
| **Soft Gold** | `#D2AE36` | Hover, estados activos, acentos, indicadores | ~10% |

**Regla crítica:** Soft Gold **NUNCA** debe ser color predominante de fondo o de superficies extensas. Solo acentos.

**Colores semánticos (mantener tonos cálidos):**
- Error: `#B33A3A`
- Éxito: `#5A8A4A`
- Advertencia: Soft Gold `#D2AE36` (ya definido)

### Tipografía

| Familia | Rol | Pesos clave |
|---------|-----|-------------|
| **Chillax** | Logo, grandes títulos, encabezados principales | Medium (500) para logo, Semibold/Bold para títulos |
| **Montserrat** | Navegación, párrafos, formularios, botones, tarjetas, admin | Regular (400) para cuerpo, Medium/SemiBold para UI interactiva |
| **La Belle Aurore** | Frases decorativas, detalles emocionales, firma de marca | Regular (400) únicamente |

**Regla crítica:** La Belle Aurore **NUNCA** para textos largos ni contenido funcional. Solo frases cortas de impacto emocional.

### Escala tipográfica definida

| Token | Desktop | Móvil | Familia | Uso |
|-------|---------|-------|---------|-----|
| `display-lg` | 64px / 1.1 | 40px / 1.2 | Chillax Light | Hero headlines |
| `headline-md` | 32px / 1.3 | — | Chillax Regular | Subtítulos de sección |
| `headline-sm` | 24px / 1.4 | — | Chillax Medium | Títulos de tarjeta, precios |
| `body-lg` | 18px / 1.6 | — | Montserrat Regular | Descripciones de producto |
| `body-md` | 16px / 1.6 | — | Montserrat Regular | Párrafos, cuerpo |
| `label-caps` | 12px / 1.0 / tracking 0.1em | — | Montserrat SemiBold | Navegación, etiquetas, caps |
| `accent-script` | 28px / 1.0 | — | La Belle Aurore | Frases decorativas |

### Logo

Variantes: Brown, Cream, White, Black.

- **Brown** sobre fondos claros (Silk Cream, Soft Sand).
- **White** sobre fotografías o fondos oscuros.
- **Cream** sobre fondos oscuros.
- **Black** solo cuando el contexto lo requiera.

### Layout y Grid

- **12 columnas** en desktop.
- **Max-width**: 1440px (`--container-storefront: 90rem`).
- **Márgenes laterales**: 80px desktop (`--spacing-margin-desktop: 5rem`), 20px móvil (`--spacing-margin-mobile: 1.25rem`).
- **Gutter**: 24px (`--spacing-gutter: 1.5rem`).
- **Section-gap**: 120px (`--spacing-section-gap: 7.5rem`).

### Grid de productos

- Máximo **3 columnas** para listados. Excepcionalmente 2 para imágenes grandes.
- **Evitar 4+ columnas** — las imágenes deben ser grandes y los detalles visibles.

### Componentes base

- **Botón primario**: fondo Intense Cocoa, texto Silk Cream, radius 4px. Hover → Soft Gold.
- **Botón secundario**: border 1px Intense Cocoa, sin fill. Hover → fondo Soft Sand.
- **Campos de entrada**: solo border inferior de Intense Cocoa al 30% opacidad. Focus → 100%.
- **Product Cards**: sin bordes, imagen aspecto 4:5, hover con zoom suave.
- **Navegación**: top nav con label-caps, estado activo = subrayado 2px Soft Gold.
- **Chips/Badges**: fondo Soft Sand, texto Intense Cocoa, Montserrat Bold 10px.

### Elevación y profundidad

- **Layering tonal** sobre sombras: Soft Sand para tarjetas inset.
- **Sombra** (solo hover en cards): `0px 10px 30px rgba(55, 38, 33, 0.05)` — luz ambiental, no digital.
- **Bordes**: 1px Intense Cocoa al 10-15% opacidad para campos y separadores.

### Formas y radius

| Token | Valor | Uso |
|-------|-------|-----|
| `sm` | 2px | Elementos pequeños |
| `DEFAULT` | 4px | Botones, inputs |
| `md` | 6px | Elementos medianos |
| `lg` | 8px | Cards, contenedores |
| `xl` | 12px | Elementos grandes |
| `full` | 9999px | Circulares |

**Imágenes de producto**: siempre **0px (sharp)** — preservar integridad editorial/fotográfica.

---

## 2. Decisiones de Diseño Mantenidas (NO cambiar)

Las siguientes decisiones son **contractuales** y NO deben ser cuestionadas por la revisión:

### D1 — Sin CMS

Home, FAQ, About Us y Contacto son **estáticos en plantillas Blade**. El admin NO edita contenido institucional. Cambios requieren editar código.

### D2 — Carrito guest + user

Visitantes no autenticados pueden agregar al carrito y comprar. Carrito persiste por sesión/cookie, se fusiona al login.

### D3 — Variantes con atributos estructurados

Color, material, tamaño como campos separados. Permite filtros facetados y selectores visuales.

### D4 — Stock visible

Las vistas muestran inventario disponible y estado "Agotado". Badge, overlay, botones deshabilitados.

### D5 — Moneda

COP (pesos enteros) y EUR (centavos). Sin floats.

### D6 — Single-vendor

No es marketplace. Un solo vendedor. Sin comisiones, sin storefront por vendedor.

### Desktop-first como ciudadana de primera clase

Esta es una **tienda online de marca premium**, no una app móvil adaptada. El usuario principal se sienta frente a una computadora. La experiencia desktop **no** debe sentirse como una app móvil ampliada. Móvil y tablet ofrecen experiencia equivalente en comodidad, no versión degradada.

### Livewire MFC sin Volt

- Multi-file components (MFC), sin Volt, sin prefijo ⚡.
- Blade plano + componentes Livewire MFC anidados.
- Contenido estático en Blade, secciones dinámicas en Livewire.

### Stack frontend

- **Livewire v4** para interactividad.
- **Tailwind CSS v4** para estilos.
- **Blade** para plantillas.
- **Filament v5** solo para admin (no storefront).

---

## 3. Vistas Implementadas — Inventario

### Storefront (vistas públicas)

| # | Brief | Vista | Componente | Estado |
|---|-------|-------|------------|--------|
| 01 | `01-home.md` | Home | `home.blade.php` + `categories-grid`, `featured-products-grid`, `product-card` | Implementada |
| 02 | `02-shop.md` | Shop | `catalog-list` + `_filters` | Implementada |
| 03 | `03-producto.md` | Detalle producto | `product-detail` | Implementada |
| 04 | `04-carrito-de-compra.md` | Carrito | `cart-page` | Implementada |
| 05 | `05-checkout.md` | Checkout | `checkout-page` + partials | Implementada |
| 06 | `06-login.md` | Login | `login-page` | Implementada |
| 07 | `07-registro.md` | Registro | `register-page` | Implementada |
| 08 | `08-perfil.md` | Perfil | `profile-page`, `profile-addresses-page`, `profile-orders-page`, `profile-reviews-page` | Implementada |
| 09 | `09-lista-de-deseados.md` | Wishlist | `wishlist-page` | Implementada |
| 10 | `10-contacto.md` | Contacto | `contact-page` | Implementada |
| 11 | `11-faq.md` | FAQ | `faq-page` | Implementada |
| 12 | `12-about-us.md` | About Us | `about-page` | Implementada |

### Componentes compartidos

| Componente | Archivo | Uso |
|------------|---------|-----|
| Product Card | `product-card` | Home, Shop, Wishlist, Relacionados |
| Add to Cart Button | `add-to-cart-button` | Home, Shop, Wishlist, Producto |
| Favorite Button | `favorite-button` | Home, Shop, Wishlist, Producto |
| Toast | `partials/toast` | Feedback global |
| Account Nav | `partials/account-nav` | Perfil, Wishlist |
| Account Shell | `partials/account-shell` | Perfil |
| Account Empty State | `partials/account-empty-state` | Perfil secciones vacías |
| Verify Email Notice | `verify-email-notice` | Auth |

### Admin (Filament v5)

| # | Brief | Recurso | Estado |
|---|-------|---------|--------|
| 13 | `13-admin-panel.md` | `CategoryResource`, `ProductResource` | ✅ F01 completa |

### Layouts

| Layout | Archivo | Uso |
|--------|---------|-----|
| Storefront | `layouts/storefront.blade.php` | Todas las vistas públicas |
| Auth | `layouts/auth.blade.php` | Login, Registro, Forgot/Reset Password |
| App | `layouts/app.blade.php` | Perfil (dentro de account-shell) |

---

## 4. Decisiones de Implementación Clave

### Perfil — Favoritos NO es sección del perfil

El menú del perfil incluye "Favoritos" como **enlace externo** a `/wishlist`, NO como sección interna. Marcar visualmente con ícono de flecha `→`.

### Checkout — Híbrido orquestador

Componente `CheckoutLivewire` orquesta el paso actual + sub-componentes `ShippingForm`, `PaymentForm`, `ReviewSummary`.

### Páginas estáticas — Blade sin Livewire

- FAQ: acordeón con Alpine.js o `<details>` nativo.
- About Us: lightbox con Alpine.js.
- Contacto: solo el formulario es Livewire (`ContactForm`), el resto Blade estático.
- Home: Blade plano + sub-componentes Livewire reutilizables.

### Navegación compartida

El header y footer son comunes a todas las vistas storefront (en `storefront.blade.php`):
- Header: sticky, fondo Soft Sand, logo centrado, nav a la izquierda, iconos de acción a la derecha.
- Footer: fondo Soft Sand, logo, frase La Belle Aurore, enlaces, redes sociales, copyright.

### Autenticación

- Sin login social en la primera versión.
- Acceso al admin controlado por `config('ecommerce.admin_emails')`.
- Sin roles ni permisos granulares (no Spatie).

---

## 5. Instrucciones para la Revisión Impeccable

### Alcance de la revisión

La skill `impeccable` debe evaluar cada vista contra los siguientes criterios:

#### ✅ SÍ revisar (cambios justificados)

1. **Consistencia de paleta**: ¿Se usan los colores correctos en los lugares correctos? ¿Algún componente usa un color fuera de la paleta?
2. **Consistencia tipográfica**: ¿Se respetan las familias, pesos y tamaños definidos? ¿La Belle Aurore se usa correctamente (solo frases decorativas)?
3. **Espaciado y ritmo vertical**: ¿Se respetan los tokens de spacing (`section-gap`, `stack-lg/md/sm`, `gutter`, márgenes)?
4. **Jerarquía visual**: ¿La información se presenta en el orden correcto de importancia?
5. **Responsive**: ¿Desktop se siente como ciudadana de primera clase? ¿Mobile es equivalente en comodidad?
6. **Estados de interacción**: ¿Hover, focus, loading, error, vacío están implementados correctamente?
7. **Consistencia entre vistas**: ¿Los patrones se repiten de forma coherente (cards, botones, formularios, navegación)?
8. **Accessibility básica**: ¿Labels en inputs, aria-labels en iconos, contraste suficiente?
9. **Coherencia con los briefs**: ¿La implementación cumple lo especificado en cada brief?
10. **Componentes compartidos**: ¿Se reutilizan los componentes existentes en vez de duplicar código?
11. **Logo**: ¿Se usa la variante correcta según el fondo?
12. **Radius**: ¿Imágenes de producto siempre sharp (0px)? ¿Botones e inputs con radius 4px?
13. **Elevación**: ¿Sombra solo en hover de cards, no como efecto decorativo?
14. **Botones**: ¿Estilos correctos (primario/secundario), hover states, deshabilitados?
15. **Campos de formulario**: ¿Borde inferior solo, focus Soft Gold, labels sobre inputs?

#### ❌ NO revisar (no cambiar)

1. **Funcionalidad backend**: No revisar lógica PHP, modelos, controladores, Livewire properties/methods.
2. **Decisiones de producto D1-D6**: No cuestionar que el contenido sea estático, que haya carrito guest, etc.
3. **Stack tecnológico**: No sugerir cambiar de Livewire a otra cosa, ni de Tailwind a CSS puro.
4. **Arquitectura de componentes**: No reestructurar la forma en que están organizados los componentes Livewire/Blade.
5. **Contenido de texto**: No editar copywriting (textos de marca, descripciones de producto, etc.) — solo evaluar presentación visual.
6. **Fotografía/imágenes**: No juzgar la calidad de las imágenes placeholder, solo su comportamiento visual (aspect ratio, hover, lazy loading).
7. **Funcionalidad de Filament**: No revisar el admin panel a menos que haya un fallo visual grave.
8. **Decisiones de responsive**: No cuestionar el enfoque desktop-first.
9. **SEO, performance, seguridad**: Fuera del alcance de esta revisión visual.
10. **Nuevas features**: No sugerir funcionalidades que no existen en los briefs.

### Metodología de revisión

1. **Una vista a la time**: seguir el orden de los briefs (01→12).
2. **Comparar contra el brief**: para cada vista, verificar que la implementación cumple lo especificado.
3. **Comparar contra 00-design-tokens.md**: verificar paleta, tipografía, spacing, componentes.
4. **Verificar consistencia cruzada**: comparar patrones entre vistas (mismo botón, misma card, mismo formulario).
5. **Desktop + Mobile**: verificar ambos breakpoints para cada vista.
6. **Estados**: verificar al menos: default, hover, loading, error, vacío.

### Formato del reporte esperado

Para cada vista, la revisión debe generar:

```markdown
## Vista: [Nombre]

### Hallazgos críticos (requieren fix)
- [Descripción del problema] → [Archivo:línea] → [Solución sugerida]

### Hallazgos menores (mejoras opcionales)
- [Descripción] → [Archivo:línea] → [Sugerencia]

### Cumplimiento de brand
- Paleta: ✅ / ⚠️
- Tipografía: ✅ / ⚠️
- Spacing: ✅ / ⚠️
- Componentes: ✅ / ⚠️
- Responsive: ✅ / ⚠️

### Estado vs. Brief
- [ ] Estructura
- [ ] Componentes
- [ ] Estados
- [ ] Breakpoints
```

---

## 6. Archivos de Referencia

| Archivo | Propósito |
|---------|-----------|
| `specs/ui-briefs/00-design-tokens.md` | **Fuente de verdad** — paleta, tipografía, spacing, componentes, principios |
| `specs/ui-briefs/README.md` | Decisiones de producto (D1-D6), mapeo briefs→roadmap, stack |
| `specs/ui-briefs/FUENTES.md` | Inventario de archivos de fuente |
| `specs/ui-briefs/01-home.md` → `12-about-us.md` | Briefs individuales de cada vista |
| `resources/css/app.css` | Theme tokens de Tailwind (colores, fuentes, spacing, radius) |
| `resources/css/fonts.css` | Declaraciones @font-face de Chillax, Montserrat, La Belle Aurore |
| `resources/views/layouts/storefront.blade.php` | Layout compartido (header, footer, estructura base) |
| `resources/views/layouts/auth.blade.php` | Layout de autenticación |
