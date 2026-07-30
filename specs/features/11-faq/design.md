# FAQ — Design

> **Feature:** F11 · **Slug:** `11-faq`
> **Requisitos:** [`requirements.md`](requirements.md)
> **Brief UI:** [`specs/ui-briefs/11-faq.md`](../../ui-briefs/11-faq.md)

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Convenciones de código | `AGENTS.md` / project-conventions |
| Tokens de diseño | [`specs/ui-briefs/00-design-tokens.md`](../../ui-briefs/00-design-tokens.md) |
| Brief visual | [`specs/ui-briefs/11-faq.md`](../../ui-briefs/11-faq.md) |
| Patrón de referencia (contact page) | `resources/views/components/contact-page/` |
| Layout storefront | `resources/views/layouts/storefront.blade.php` |

---

## Arquitectura

### Tipo de página

Página **estática** sin backend. No hay modelo, no hay migración, no hay Actions/DTOs/Services.

Implementada como **Livewire anonymous component** (mismo patrón que la página de contacto), con lógica de interactividad delegada a **Alpine.js**.

### Capas

| Capa | Archivo | Responsabilidad |
|------|---------|-----------------|
| Ruta | `routes/web.php` | Registrar `Route::livewire('/faq', 'faq-page')` |
| Componente Livewire | `resources/views/components/faq-page/faq-page.php` | Clase anónima con `#[Layout('layouts.storefront')]`; sin lógica de negocio |
| Vista Blade | `resources/views/components/faq-page/faq-page.blade.php` | Estructura HTML, tabs, acordeón, CTA; interactividad con Alpine.js |
| i18n | `lang/{en,es}/faq.php` | Toda la copy: breadcrumb, título, categorías, preguntas, respuestas, CTA |

### Convención de nombres

Siguiendo project-conventions (tipo primero, área después):

- El componente vive en `resources/views/components/faq-page/` (área: `faq-page`).
- No hay clases en `app/` — es una página puramente estática.

---

## Interactividad (Alpine.js)

### Estado

```php
// En el componente Blade, data de Alpine.js
x-data="{
    activeCategory: 'compras',
    openQuestion: null,
    categories: ['compras', 'envios', 'pagos', 'cambios', 'cuenta']
}"
```

### Comportamiento del acordeón

- `openQuestion` almacena el ID de la pregunta abierta (o `null`).
- Clic en pregunta: si ya está abierta, `openQuestion = null`; si no, `openQuestion = id`.
- `x-show` con `x-transition` para la animación (el proyecto no incluye Alpine Collapse plugin; usar `x-transition:enter`/`x-transition:leave` como en el patrón de toast/filtros existente).
- Solo una pregunta abierta a la vez (R6).

### Tabs de categoría

- `activeCategory` almacena el slug de la categoría activa.
- Clic en tab actualiza `activeCategory`.
- Preguntas filtradas con `x-show="activeCategory === 'categoria'"`.
- Tab activo: clase condicional con fondo Intense Cocoa + texto Silk Cream.

### Scroll horizontal en móvil

- `overflow-x-auto` en el contenedor de tabs.
- Indicador visual: gradiente fade a la derecha (`mask-image` o pseudo-elemento) que desaparece al hacer scroll.

---

## Estructura de datos (i18n)

### `lang/en/faq.php`

```php
return [
    'breadcrumb' => [
        'home' => 'Home',
        'faq' => 'FAQ',
    ],
    'title' => 'Frequently Asked Questions',
    'subtitle' => 'Find quick answers to common questions...',
    'categories' => [
        'compras' => [
            'label' => 'Shopping',
            'questions' => [
                ['q' => 'Question text?', 'a' => 'Answer text.'],
                // ...
            ],
        ],
        'envios' => [ /* ... */ ],
        'pagos' => [ /* ... */ ],
        'cambios' => [ /* ... */ ],
        'cuenta' => [ /* ... */ ],
    ],
    'empty' => 'No questions in this category.',
    'cta' => [
        'heading' => 'Didn\'t find what you were looking for?',
        'button' => 'Contact us',
    ],
];
```

### `lang/es/faq.php`

Misma estructura con traducciones al español.

---

## Estructura Blade

### Layout

```
<div class="py-8 lg:py-12">
    {{-- Breadcrumb --}}
    {{-- Título + subtítulo --}}
    {{-- Tabs de categorías (Alpine.js) --}}
    {{-- Contenedor de acordeones por categoría --}}
    {{-- CTA a contacto --}}
</div>
```

### Patrón del acordeón

```blade
@foreach(__('faq.categories') as $key => $category)
    <div x-show="activeCategory === '{{ $key }}'">
        @foreach($category['questions'] as $i => $qa)
            <div class="border-b border-soft-sand">
                <button
                    @click="openQuestion === {{ $i }} ? openQuestion = null : openQuestion = {{ $i }}"
                    :aria-expanded="openQuestion === {{ $i }}"
                    class="flex w-full items-center justify-between py-5 text-left"
                >
                    <span class="font-medium text-intense-cocoa">{{ $qa['q'] }}</span>
                    <span
                        class="ml-4 text-intense-cocoa transition-transform duration-200"
                        :class="openQuestion === {{ $i }} ? 'rotate-45' : ''"
                        aria-hidden="true"
                    >
                        {{-- Ícono + (rota a x al expandir) --}}
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </span>
                </button>
                <div
                    x-show="openQuestion === {{ $i }}"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="pb-5 text-intense-cocoa/70"
                >
                    {{ $qa['a'] }}
                </div>
            </div>
        @endforeach
    </div>
@endforeach
```

---

## Visual (según brief + design tokens)

| Elemento | Estilo |
|----------|--------|
| Fondo página | Silk Cream `#FFF8CF` |
| Tabs activo | Fondo Intense Cocoa `#372621` + texto Silk Cream, o borde inferior Soft Gold |
| Tabs inactivo | Intense Cocoa con opacidad, hover Soft Gold |
| Pregunta | Montserrat Medium, Intense Cocoa, padding 16–20px vertical |
| Respuesta | Montserrat Regular, Intense Cocoa con opacidad, padding horizontal + bottom |
| Separadores | Soft Sand `#E9DED3` |
| CTA card | Fondo Soft Sand, botón Intense Cocoa |
| Ancho máximo | 700–800px centrado |

---

## Accesibilidad

- Tabs con `role="tablist"`, `role="tab"`, `aria-selected`.
- Acordeón con `aria-expanded` en cada botón de pregunta.
- Respuestas con `aria-labelledby` referenciando el botón.
- Breadcrumb con `<nav aria-label="Breadcrumb">`.
- Respuestas ocultas con `x-show` (no `display:none` manual) para que Alpine maneje la visibilidad correctamente.

---

## Dependencias

| Dependencia | Estado | Nota |
|-------------|--------|------|
| Alpine.js | Ya incluida (vía Livewire) | Sin instalación adicional |
| Alpine Collapse plugin | **No incluido** | Usar `x-transition` (patrón ya establecido en el proyecto — ver toast, filtros, lightbox) |
| Tailwind CSS v4 | Ya incluida | Usar utilidades del proyecto |

No se agregan paquetes nuevos. No se modifica `composer.json` ni `package.json`.

---

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Alpine Collapse plugin no disponible | Usar `x-transition` con `max-height` o `x-show` + transición manual |
| Contenido FAQ desactualizado | Vivir en i18n — fácil de editar sin tocar lógica |
| Tabs no visibles en móvil | Scroll horizontal con indicador visual de más contenido |
