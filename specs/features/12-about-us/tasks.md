# About Us — Tasks

> **Feature:** F12 · **Slug:** `12-about-us`
> **Requisitos:** [`requirements.md`](requirements.md)
> **Design:** [`design.md`](design.md)

---

## 1. Internacionalización (i18n)

- [ ] 1.1 Crear `lang/en/about.php` con toda la copy: breadcrumb, hero, historia, pilares (4 valores), diferencial (descripción + bullets), galería, CTA. _(cubre R2, R3, R5, R6, R9, R11)_
- [ ] 1.2 Crear `lang/es/about.php` con las traducciones correspondientes. _(cubre R2, R3, R5, R6, R9, R11)_

## 2. Ruta y componente

- [ ] 2.1 Registrar ruta `Route::livewire('/about-us', 'about-page')` en `routes/web.php`. _(cubre R1, R14)_
- [ ] 2.2 Crear componente Livewire anónimo `resources/views/components/about-page/about-page.php` con `#[Layout('layouts.storefront')]`. _(cubre R1)_
- [ ] 2.3 Crear vista Blade `resources/views/components/about-page/about-page.blade.php` con estructura base (breadcrumb, contenedor de secciones). _(cubre R1, R10)_

## 3. Hero section

- [ ] 3.1 Implementar hero con imagen de fondo, overlay Intense Cocoa 40%, título (Chillax Bold, display-lg) y subtítulo (La Belle Aurore, accent-script). _(cubre R2)_
- [ ] 3.2 Aplicar altura 40–50vh con `min-h-[400px]` y centrado vertical del contenido. _(cubre R2)_
- [ ] 3.3 Asegurar responsive: título más pequeño en móvil (`font-display-lg-mobile`). _(cubre R13)_

## 4. Sección Historia

- [ ] 4.1 Implementar layout de dos columnas (texto 50% + imagen 50%) con `grid grid-cols-1 md:grid-cols-2`. _(cubre R3)_
- [ ] 4.2 Agregar título "Nuestra Historia" (Chillax, headline-md) y párrafos descriptivos (Montserrat Regular, body-md). _(cubre R3)_
- [ ] 4.3 Implementar responsive móvil: una columna con imagen arriba, texto abajo. _(cubre R4)_
- [ ] 4.4 Agregar imagen de referencia (taller, fundadora, materiales) con `object-cover`. _(cubre R3)_

## 5. Sección Pilares

- [ ] 5.1 Implementar grid de 4 valores con fondo Soft Sand y `rounded-lg`. _(cubre R5)_
- [ ] 5.2 Agregar íconos (Material Symbols o SVGs), títulos (Montserrat SemiBold, uppercase, label-caps) y descripciones cortas. _(cubre R5)_
- [ ] 5.3 Aplicar hover en íconos: `group-hover:bg-soft-gold/20 transition-colors duration-300`. _(cubre R5)_
- [ ] 5.4 Responsive: grid 2 columnas en tablet (`sm:grid-cols-2`), 4 en desktop (`lg:grid-cols-4`). _(cubre R13)_

## 6. Sección Diferencial

- [ ] 6.1 Implementar layout de dos columnas invertidas (imagen izquierda, texto derecha). _(cubre R6)_
- [ ] 6.2 Agregar título "Lo que nos hace diferentes", descripción y bullets con ícono `check_circle` Soft Gold. _(cubre R6)_
- [ ] 6.3 Responsive móvil: una columna. _(cubre R13)_

## 7. Sección Galería

- [ ] 7.1 Implementar grid de imágenes (3 columnas desktop, 2 móvil) con `aspect-square`. _(cubre R7)_
- [ ] 7.2 Agregar lazy loading nativo (`loading="lazy"`) a todas las imágenes. _(cubre R7)_
- [ ] 7.3 Implementar hover: overlay Intense Cocoa 20% + ícono expandir (opacidad 0→100). _(cubre R7)_
- [ ] 7.4 Preparar array de imágenes de galería (placeholder de alta calidad). _(cubre R7)_

## 8. Lightbox

- [ ] 8.1 Implementar lightbox con Alpine.js (`x-data`, `lightboxOpen`, `lightboxIndex`). _(cubre R8)_
- [ ] 8.2 Implementar apertura al clic en imagen de galería (`openLightbox(index)`). _(cubre R8)_
- [ ] 8.3 Implementar cierre: botón X, clic en overlay (`x-on:click.self`), tecla Escape. _(cubre R8)_
- [ ] 8.4 Implementar navegación prev/next con botones y teclado (ArrowLeft/ArrowRight). _(cubre R8)_
- [ ] 8.5 Aplicar transiciones de apertura/cierre con `x-transition`. _(cubre R8)_
- [ ] 8.6 Overlay con `bg-intense-cocoa/80` y backdrop-blur. _(cubre R8)_

## 9. CTA final

- [ ] 9.1 Implementar banner a ancho completo con fondo Intense Cocoa. _(cubre R9)_
- [ ] 9.2 Agregar texto "Descubre nuestras colecciones" (Chillax, Silk Cream) y botón "Ver productos" (fondo Silk Cream, texto Intense Cocoa, hover Soft Gold). _(cubre R9)_
- [ ] 9.3 Botón enlaza a `/shop` (route `products.index`). _(cubre R9)_

## 10. Scroll reveal animations

- [ ] 10.1 Implementar CSS para `.reveal` (opacity 0, translateY 30px) y `.reveal.active` (opacity 1, translateY 0). _(cubre R12)_
- [ ] 10.2 Implementar JavaScript con IntersectionObserver para activar `.reveal.active` al hacer scroll. _(cubre R12)_
- [ ] 10.3 Agregar delays progresivos (`.delay-100`, `.delay-200`) para efecto escalonado. _(cubre R12)_
- [ ] 10.4 Respetar `prefers-reduced-motion`: desactivar animaciones si el usuario lo prefiere. _(cubre R12)_

## 11. Breadcrumb

- [ ] 11.1 Implementar breadcrumb "Inicio / Nosotros" con enlace funcional a `/`. _(cubre R10)_
- [ ] 11.2 Aplicar estilos consistentes con el breadcrumb de FAQ (label-caps, Intense Cocoa/60%). _(cubre R10)_

## 12. Accesibilidad

- [ ] 12.1 Agregar `aria-label` en la sección hero. _(cubre R2)_
- [ ] 12.2 Agregar `alt` descriptivo en todas las imágenes (hero, historia, diferencial, galería). _(cubre R7)_
- [ ] 12.3 Agregar `role="dialog"`, `aria-label`, `aria-modal="true"` al lightbox. _(cubre R8)_
- [ ] 12.4 Implementar navegación por teclado en lightbox (Escape, ArrowLeft, ArrowRight). _(cubre R8)_
- [ ] 12.5 Breadcrumb con `<nav aria-label="Breadcrumb">`. _(cubre R10)_

## 13. Tests

- [x] 13.1 Feature test: `/about-us` responde 200 para guest. _(cubre R1)_
- [x] 13.2 Feature test: `/about-us` responde 200 para usuario autenticado. _(cubre R1)_
- [x] 13.3 Feature test: página contiene breadcrumb, hero, historia, pilares, diferencial, galería, CTA. _(cubre R1, R2, R3, R5, R6, R7, R9, R10)_
- [x] 13.4 Feature test: HTML contiene atributos Alpine para lightbox (`x-data`, `lightboxOpen`, `openLightbox`). _(cubre R8)_
- [x] 13.5 Feature test: HTML contiene clases de scroll reveal (`.reveal`). _(cubre R12)_
- [x] 13.6 Feature test: imágenes de galería tienen `loading="lazy"`. _(cubre R7)_
- [x] 13.7 Feature test: CTA a `/shop` presente con texto correcto. _(cubre R9)_
- [x] 13.8 Feature test: copy localizada en `en` y `es` sin cadenas hardcodeadas. _(cubre R11)_
- [x] 13.9 Feature test: `/about-us/algo` devuelve 404. _(cubre R15)_
- [x] 13.11 Feature test: galería vacía no rompe layout. _(cubre R16)_
- [x] 13.10 Verificar que tests existentes (`StorefrontLayoutTest`, `ContactPageTest`, `FaqPageTest`) sigan pasando (navbar link a `/about-us` ahora apunta a ruta válida).

---

## Mapa de trazabilidad

| Criterio | Tareas |
|----------|--------|
| R1 | 2.1, 2.2, 2.3, 13.1, 13.2, 13.3 |
| R2 | 1.1, 1.2, 3.1, 3.2, 3.3, 12.1, 13.3 |
| R3 | 1.1, 1.2, 4.1, 4.2, 4.4, 13.3 |
| R4 | 4.3, 13.3 |
| R5 | 1.1, 1.2, 5.1, 5.2, 5.3, 5.4, 13.3 |
| R6 | 1.1, 1.2, 6.1, 6.2, 6.3, 13.3 |
| R7 | 7.1, 7.2, 7.3, 7.4, 12.2, 13.3, 13.6 |
| R8 | 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 12.3, 12.4, 13.4 |
| R9 | 1.1, 1.2, 9.1, 9.2, 9.3, 13.3, 13.7 |
| R10 | 2.3, 11.1, 11.2, 12.5, 13.3 |
| R11 | 1.1, 1.2, 13.8 |
| R12 | 10.1, 10.2, 10.3, 10.4, 13.5 |
| R13 | 3.3, 4.3, 5.4, 6.3, 7.1 |
| R14 | 2.1 |
| R15 | 13.9 |
| R16 | 13.11 |

---

## Definition of Done

- [ ] R1–R16 cubiertos por tests (feature test `AboutUsPageTest`).
- [ ] `lang/{en,es}/about.php` completo con toda la copy.
- [ ] Ruta `/about-us` registrada y funcional (ya referenciada en navbar).
- [ ] Lightbox funcional con Alpine.js (abrir, cerrar, navegación prev/next, teclado).
- [ ] Scroll reveal animations con IntersectionObserver + `prefers-reduced-motion`.
- [ ] Responsive: desktop-first con experiencia móvil equivalente.
- [ ] `vendor/bin/sail bin pint --dirty --format agent` en PHP tocado.
- [ ] Tests Sail del alcance en verde (`vendor/bin/sail artisan test --compact --filter=AboutUsPageTest`).
- [ ] Tests existentes (`StorefrontLayoutTest`, `ContactPageTest`, `FaqPageTest`) siguen en verde.
