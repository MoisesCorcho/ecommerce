# About Us — Design

> **Feature:** F12 · **Slug:** `12-about-us`
> **Requisitos:** [`requirements.md`](requirements.md)
> **Brief UI:** [`specs/ui-briefs/12-about-us.md`](../../ui-briefs/12-about-us.md)
> **Stitch de referencia:** [`specs/ui-briefs/stitches/about_us_leen_handbags_final_consistency_fix/code.html`](../../ui-briefs/stitches/about_us_leen_handbags_final_consistency_fix/code.html)

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Convenciones de código | `AGENTS.md` / project-conventions |
| Tokens de diseño | [`specs/ui-briefs/00-design-tokens.md`](../../ui-briefs/00-design-tokens.md) |
| Brief visual | [`specs/ui-briefs/12-about-us.md`](../../ui-briefs/12-about-us.md) |
| Patrón de referencia (FAQ page) | `resources/views/components/faq-page/` |
| Patrón de referencia (contact page) | `resources/views/components/contact-page/` |
| Layout storefront | `resources/views/layouts/storefront.blade.php` |

---

## Arquitectura

### Tipo de página

Página **estática** sin backend. No hay modelo, no hay migración, no hay Actions/DTOs/Services.

Implementada como **Livewire anonymous component** (mismo patrón que la página de FAQ y contacto), con lógica de interactividad delegada a **Alpine.js** (lightbox) y **JavaScript vanilla** (scroll reveal).

### Capas

| Capa | Archivo | Responsabilidad |
|------|---------|-----------------|
| Ruta | `routes/web.php` | Registrar `Route::livewire('/about-us', 'about-page')` |
| Componente Livewire | `resources/views/components/about-page/about-page.php` | Clase anónima con `#[Layout('layouts.storefront')]`; sin lógica de negocio |
| Vista Blade | `resources/views/components/about-page/about-page.blade.php` | Estructura HTML de las 6 secciones; lightbox con Alpine.js; scroll reveal con JS vanilla |
| i18n | `lang/{en,es}/about.php` | Toda la copy: breadcrumb, títulos, párrafos, valores, diferencial, CTA |

### Convención de nombres

Siguiendo project-conventions (tipo primero, área después):

- El componente vive en `resources/views/components/about-page/` (área: `about-page`).
- No hay clases en `app/` — es una página puramente estática.

---

## Interactividad

### Lightbox (Alpine.js)

```php
// En el componente Blade, data de Alpine.js para el lightbox
x-data="{
    lightboxOpen: false,
    lightboxIndex: 0,
    images: [],
    openLightbox(index) {
        this.lightboxIndex = index;
        this.lightboxOpen = true;
    },
    closeLightbox() {
        this.lightboxOpen = false;
    },
    nextImage() {
        this.lightboxIndex = (this.lightboxIndex + 1) % this.images.length;
    },
    prevImage() {
        this.lightboxIndex = (this.lightboxIndex - 1 + this.images.length) % this.images.length;
    }
}"
```

### Comportamiento del lightbox

- Click en imagen de galería → `openLightbox(index)`.
- Botón cerrar o click en overlay → `closeLightbox()`.
- Navegación prev/next con botones o teclado (ArrowLeft/ArrowRight).
- `x-show` con `x-transition` para animación de apertura/cierre.
- Overlay con `bg-intense-cocoa/80` (backdrop).

### Scroll reveal (JavaScript vanilla)

```javascript
// Patrón del stitch de referencia — IntersectionObserver es más performante
document.addEventListener('DOMContentLoaded', function() {
    const reveals = document.querySelectorAll('.reveal');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, { threshold: 0.1 });

    reveals.forEach(el => observer.observe(el));
});
```

- Clase `.reveal` → opacity: 0, transform: translateY(30px).
- Clase `.reveal.active` → opacity: 1, transform: translateY(0).
- Transición suave: `transition: all 0.8s cubic-bezier(0.5, 0, 0, 1)`.
- Delay progresivo con clases `.delay-100`, `.delay-200`, etc.

---

## Estructura de datos (i18n)

### `lang/en/about.php`

```php
return [
    'breadcrumb' => [
        'home' => 'Home',
        'about' => 'About Us',
    ],
    'hero' => [
        'title' => 'Our Essence',
        'subtitle' => 'Craftsmanship that transcends time',
    ],
    'story' => [
        'title' => 'Our Story',
        'paragraphs' => [
            'Founded with the vision of returning to the origins of true luxury, LEEN was born from an unwavering passion for noble materials and artisanal mastery...',
            'In a world of immediacy, we choose pause. We carefully select the most exceptional hides...',
        ],
    ],
    'pillars' => [
        'title' => 'Our Pillars',
        'items' => [
            ['icon' => 'workspace_premium', 'title' => 'Exceptional Quality', 'description' => 'Materials selected under the strictest standards of excellence.'],
            ['icon' => 'hourglass_empty', 'title' => 'Timeless Design', 'description' => 'Pure silhouettes that transcend seasons and fleeting trends.'],
            ['icon' => 'diamond', 'title' => 'Exclusivity', 'description' => 'Limited production that guarantees the uniqueness of each creation.'],
            ['icon' => 'front_hand', 'title' => 'Artisanal Commitment', 'description' => 'Every stitch reflects the dedication of expert hands.'],
        ],
    ],
    'differential' => [
        'title' => 'What Makes Us Different',
        'description' => '...',
        'bullets' => [
            'Premium materials sourced from artisanal tanneries',
            'Handcrafted manufacturing with traditional techniques',
            'Meticulous attention to detail at every stage',
            'Timeless designs that transcend seasonal trends',
        ],
    ],
    'gallery' => [
        'title' => 'Our World',
    ],
    'cta' => [
        'heading' => 'Discover our collections',
        'button' => 'View products',
    ],
];
```

### `lang/es/about.php`

Misma estructura con traducciones al español.

---

## Estructura Blade

### Layout de la página

```
<div x-data="{ lightboxOpen: false, lightboxIndex: 0 }">
    {{-- Breadcrumb --}}
    {{-- 1. Hero Section --}}
    {{-- 2. Story Section (dos columnas) --}}
    {{-- 3. Pillars Section (grid de valores) --}}
    {{-- 4. Differential Section (dos columnas invertidas) --}}
    {{-- 5. Gallery Section (grid + lightbox) --}}
    {{-- 6. CTA Section --}}
    {{-- Lightbox overlay --}}
</div>
```

### Sección Hero

```blade
<section class="relative w-full h-[50vh] min-h-[400px] flex flex-col items-center justify-center overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('...')">
        <div class="absolute inset-0 bg-intense-cocoa/40 mix-blend-multiply"></div>
    </div>
    <div class="relative z-10 text-center px-6 max-w-4xl mx-auto reveal">
        <h1 class="font-display-lg text-silk-cream mb-4">{{ __('about.hero.title') }}</h1>
        <p class="font-accent-script text-soft-gold">{{ __('about.hero.subtitle') }}</p>
    </div>
</section>
```

### Sección Historia (dos columnas)

```blade
<section class="py-section-gap grid grid-cols-1 md:grid-cols-2 gap-gutter items-center">
    <div class="reveal">
        <h2 class="font-headline-md text-intense-cocoa mb-6">{{ __('about.story.title') }}</h2>
        @foreach(__('about.story.paragraphs') as $paragraph)
            <p class="font-body-md text-intense-cocoa/70 mb-6 leading-relaxed">{{ $paragraph }}</p>
        @endforeach
    </div>
    <div class="relative h-[600px] reveal delay-100">
        <img src="..." alt="..." class="w-full h-full object-cover">
    </div>
</section>
```

### Sección Pilares (grid)

```blade
<section class="py-section-gap bg-soft-sand rounded-lg px-8 md:px-16 reveal">
    <h2 class="font-headline-md text-intense-cocoa text-center mb-16">{{ __('about.pillars.title') }}</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12">
        @foreach(__('about.pillars.items') as $pillar)
            <div class="flex flex-col items-center text-center group">
                <div class="w-16 h-16 rounded-lg bg-silk-cream flex items-center justify-center mb-6 text-intense-cocoa group-hover:bg-soft-gold/20 transition-colors duration-300">
                    {{-- Use inline SVGs (Heroicons) instead of Material Symbols --}}
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        {{-- Map icon name to SVG path --}}
                    </svg>
                </div>
                <h3 class="font-label-caps text-intense-cocoa mb-3 uppercase tracking-wider">{{ $pillar['title'] }}</h3>
                <p class="font-body-md text-intense-cocoa/60 text-sm">{{ $pillar['description'] }}</p>
            </div>
        @endforeach
    </div>
</section>
```

### Sección Diferencial (dos columnas invertidas)

```blade
<section class="py-section-gap grid grid-cols-1 md:grid-cols-2 gap-gutter items-center">
    <div class="relative h-[600px] reveal">
        <img src="..." alt="..." class="w-full h-full object-cover">
    </div>
    <div class="reveal delay-100">
        <h2 class="font-headline-md text-intense-cocoa mb-6">{{ __('about.differential.title') }}</h2>
        <p class="font-body-md text-intense-cocoa/70 mb-6 leading-relaxed">{{ __('about.differential.description') }}</p>
        <ul class="space-y-3">
            @foreach(__('about.differential.bullets') as $bullet)
                <li class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-soft-gold mt-0.5">check_circle</span>
                    <span class="font-body-md text-intense-cocoa/70">{{ $bullet }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</section>
```

### Sección Galería (grid + lightbox)

```blade
<section class="py-section-gap">
    <h2 class="font-headline-md text-intense-cocoa text-center mb-16 reveal">{{ __('about.gallery.title') }}</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($galleryImages as $i => $image)
            <div class="relative aspect-square overflow-hidden cursor-pointer group reveal"
                 x-on:click="openLightbox({{ $i }})">
                <img src="{{ $image }}" alt="..." loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                <div class="absolute inset-0 bg-intense-cocoa/0 group-hover:bg-intense-cocoa/20 transition-colors duration-300 flex items-center justify-center">
                    <span class="material-symbols-outlined text-silk-cream opacity-0 group-hover:opacity-100 transition-opacity duration-300">open_in_full</span>
                </div>
            </div>
        @endforeach
    </div>
</section>
```

### Lightbox overlay

```blade
<div x-show="lightboxOpen"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 bg-intense-cocoa/80 flex items-center justify-center"
     x-on:click.self="closeLightbox()"
     x-on:keydown.escape.window="closeLightbox()"
     x-on:keydown.arrow-right.window="nextImage()"
     x-on:keydown.arrow-left.window="prevImage()">

    <button x-on:click="closeLightbox()" class="absolute top-6 right-6 text-silk-cream hover:text-soft-gold transition-colors">
        <span class="material-symbols-outlined text-3xl">close</span>
    </button>

    <button x-on:click="prevImage()" class="absolute left-6 text-silk-cream hover:text-soft-gold transition-colors">
        <span class="material-symbols-outlined text-4xl">chevron_left</span>
    </button>

    <img :src="images[lightboxIndex]" alt="" class="max-h-[80vh] max-w-[90vw] object-contain">

    <button x-on:click="nextImage()" class="absolute right-6 text-silk-cream hover:text-soft-gold transition-colors">
        <span class="material-symbols-outlined text-4xl">chevron_right</span>
    </button>
</div>
```

### CTA final

```blade
<section class="bg-intense-cocoa py-20 text-center">
    <h2 class="font-headline-md text-silk-cream mb-8">{{ __('about.cta.heading') }}</h2>
    <a href="{{ route('products.index') }}" class="inline-block bg-silk-cream text-intense-cocoa font-label-caps font-semibold uppercase tracking-widest px-10 py-4 hover:bg-soft-gold transition-colors duration-300">
        {{ __('about.cta.button') }}
    </a>
</section>
```

---

## Visual (según brief + design tokens)

| Elemento | Estilo |
|----------|--------|
| Fondo página | Silk Cream `#FFF8CF` |
| Hero overlay | Intense Cocoa 40% opacity, mix-blend-multiply |
| Hero título | Chillax Bold, display-lg (64px desktop / 40px móvil), Silk Cream |
| Hero subtítulo | La Belle Aurore, accent-script (28px), Soft Gold |
| Sección Historia | Dos columnas, gap 24px, texto Intense Cocoa 70% opacity |
| Sección Pilares | Fondo Soft Sand `#E9DED3`, grid 4 columnas (desktop), íconos en círculos |
| Sección Diferencial | Dos columnas invertidas (imagen izquierda), bullets con ícono check_circle Soft Gold |
| Galería | Grid 3 columnas (desktop), 2 columnas ( móvil), aspect-square |
| Galería hover | Overlay Intense Cocoa 20%, ícono expandir Silk Cream |
| Lightbox overlay | Intense Cocoa 80% opacity, backdrop-blur |
| CTA final | Fondo Intense Cocoa, texto Silk Cream, botón Silk Cream fondo |
| Separadores | Soft Sand `#E9DED3` |
| Ancho máximo | 1440px centrado (patrón storefront) |

---

## Espaciado y ritmo vertical

Siguiendo los design tokens:

- **Section-gap**: 120px entre secciones principales.
- **Padding secciones**: `py-section-gap` (120px) o `py-20` (80px para CTA).
- **Gap interno**: `gap-gutter` (24px) para grids de dos columnas.
- **Margen lateral**: `px-margin-mobile` (20px) / `px-margin-desktop` (80px).

---

## Accesibilidad

- Hero: `aria-label` en la sección para lectores de pantalla.
- Galería: `alt` descriptivo en cada imagen.
- Lightbox: `role="dialog"`, `aria-label="Galería de imágenes"`, `aria-modal="true"`.
- Navegación por teclado en lightbox: Escape para cerrar, ArrowLeft/ArrowRight para navegar.
- CTA: enlace con texto descriptivo, no solo "Click aquí".
- Breadcrumb: `<nav aria-label="Breadcrumb">`.

---

## Dependencias

| Dependencia | Estado | Nota |
|-------------|--------|------|
| Alpine.js | Ya incluida (vía Livewire) | Sin instalación adicional |
| Tailwind CSS v4 | Ya incluida | Usar utilidades del proyecto |
| Material Symbols | Ya incluida (vía Google Fonts en stitch) | O usar SVGs inline del proyecto |

No se agregan paquetes nuevos. No se modifica `composer.json` ni `package.json`.

---

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Imágenes de galería no disponibles | Usar placeholders de alta calidad; etiquetar como "synthetic" para reemplazo futuro |
| Lightbox no accesible por teclado | Implementar `x-on:keydown` para Escape, ArrowLeft, ArrowRight |
| Scroll reveal en dispositivos con `prefers-reduced-motion` | Respetar media query: desactivar animaciones si el usuario lo prefiere |
| Google Fonts (Material Symbols) no cargan | Fallback con SVGs inline del proyecto (Heroicons) |
