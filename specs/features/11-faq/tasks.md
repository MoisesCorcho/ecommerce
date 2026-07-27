# FAQ — Tasks

> **Feature:** F11 · **Slug:** `11-faq`
> **Requisitos:** [`requirements.md`](requirements.md)
> **Design:** [`design.md`](design.md)

---

## 1. Internacionalización (i18n)

- [ ] 1.1 Crear `lang/en/faq.php` con toda la copy: breadcrumb, título, subtítulo, 5 categorías con preguntas y respuestas, mensaje de categoría vacía, CTA. _(cubre R2, R11)_
- [ ] 1.2 Crear `lang/es/faq.php` con las traducciones correspondientes. _(cubre R2, R11)_

## 2. Ruta y componente

- [ ] 2.1 Registrar ruta `Route::livewire('/faq', 'faq-page')` en `routes/web.php`. _(cubre R1, R12)_
- [ ] 2.2 Crear componente Livewire anónimo `resources/views/components/faq-page/faq-page.php` con `#[Layout('layouts.storefront')]`. _(cubre R1)_
- [ ] 2.3 Crear vista Blade `resources/views/components/faq-page/faq-page.blade.php` con estructura base (breadcrumb, título, subtítulo). _(cubre R1, R9)_

## 3. Tabs de categorías

- [ ] 3.1 Implementar tabs horizontales con Alpine.js (`x-data`, `activeCategory`). _(cubre R3)_
- [ ] 3.2 Aplicar estilos de tab activo vs inactivo según design tokens (Intense Cocoa fondo/texto o borde Soft Gold). _(cubre R3)_
- [ ] 3.3 Implementar scroll horizontal de tabs en móvil con indicador visual de más contenido. _(cubre R7)_

## 4. Acordeón de preguntas

- [ ] 4.1 Implementar acordeón con Alpine.js (`openQuestion`, `x-show`, `x-transition`). _(cubre R4, R5)_
- [ ] 4.2 Implementar lógica de una sola pregunta abierta a la vez (R6). _(cubre R6)_
- [ ] 4.3 Aplicar estilos del acordeón: tipografía, padding, separadores, íconos +/− según design tokens. _(cubre R4, R5)_

## 5. CTA y layout

- [ ] 5.1 Implementar CTA final a `/contact` con card y botón según brief. _(cubre R8)_
- [ ] 5.2 Aplicar ancho máximo 700–800px centrado y responsive en todos los breakpoints. _(cubre R10)_
- [ ] 5.3 Implementar manejo de categoría vacía (mensaje de fallback). _(cubre R13)_

## 6. Accesibilidad

- [ ] 6.1 Agregar atributos ARIA a tabs (`role="tablist"`, `role="tab"`, `aria-selected`). _(cubre R3)_
- [ ] 6.2 Agregar atributos ARIA al acordeón (`aria-expanded`, `aria-labelledby`). _(cubre R4, R5)_

## 7. Tests

- [ ] 7.1 Feature test: `/faq` responde 200 para guest. _(cubre R1)_
- [ ] 7.2 Feature test: `/faq` responde 200 para usuario autenticado. _(cubre R1)_
- [ ] 7.3 Feature test: página contiene breadcrumb, título, subtítulo, categorías y CTA a `/contact`. _(cubre R1, R2, R8, R9)_
- [ ] 7.4 Feature test: HTML contiene tabs con atributos Alpine (`x-data`, `activeCategory`, `x-show` por categoría). _(cubre R3)_
- [ ] 7.5 Feature test: HTML contiene acordeón con atributos Alpine (`openQuestion`, `x-show`, `x-transition`). _(cubre R4, R5, R6)_
- [ ] 7.6 Feature test: HTML de tabs contiene clases de scroll horizontal (`overflow-x-auto`) y estructura responsive. _(cubre R7)_
- [ ] 7.7 Feature test: contenido renderizado respeta ancho máximo (clases `max-w-[800px]` o equivalente). _(cubre R10)_
- [ ] 7.8 Feature test: copy localizada en `en` y `es` sin cadenas hardcodeadas. _(cubre R11)_
- [ ] 7.9 Feature test: `/faq/algo` devuelve 404. _(cubre R14)_
- [ ] 7.10 Verificar que tests existentes (`ContactPageTest`, `StorefrontLayoutTest`) sigan pasando (CTA a `/faq` ahora apunta a ruta válida).

---

## Mapa de trazabilidad

| Criterio | Tareas |
|----------|--------|
| R1 | 2.1, 2.2, 2.3, 7.1, 7.2, 7.3 |
| R2 | 1.1, 1.2, 7.3 |
| R3 | 3.1, 3.2, 6.1, 7.4 |
| R4 | 4.1, 4.3, 6.2, 7.5 |
| R5 | 4.1, 4.3, 6.2, 7.5 |
| R6 | 4.2, 7.5 |
| R7 | 3.3, 7.6 |
| R8 | 5.1, 7.3 |
| R9 | 2.3, 7.3 |
| R10 | 5.2, 7.7 |
| R11 | 1.1, 1.2, 7.8 |
| R12 | 2.1 |
| R13 | 5.3 |
| R14 | 7.9 |

---

## Definition of Done

- [ ] R1–R14 cubiertos por tests (feature test `FaqPageTest`).
- [ ] `lang/{en,es}/faq.php` completo con toda la copy.
- [ ] Ruta `/faq` registrada y funcional.
- [ ] Acordeón funcional con Alpine.js + `x-transition` (una pregunta abierta a la vez).
- [ ] Tabs de categoría con scroll horizontal en móvil.
- [ ] CTA a `/contact` presente y funcional.
- [ ] `vendor/bin/sail bin pint --dirty --format agent` en PHP tocado.
- [ ] Tests Sail del alcance en verde (`vendor/bin/sail artisan test --compact --filter=FaqPageTest`).
- [ ] Tests existentes (`ContactPageTest`, `StorefrontLayoutTest`) siguen en verde.
