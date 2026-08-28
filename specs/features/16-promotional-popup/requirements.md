# F-04: Pop-up Promocional Administrable Vinculado a Cupones

> **Estado:** Specs en progreso  
> **ID:** F-04 · **Slug:** `16-promotional-popup`  
> **Prerequisitos:** F06 (Cupones)  
> **Desbloquea:** Captura de leads, conversión y promociones automáticas  
> **Presupuesto / Estimación:** 13 horas (104 €)

---

## 1. Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Reporte unificado de cliente | [`docs/cliente/Reporte Unificado Retroalimentacion y Scope.md`](../../../docs/cliente/Reporte%20Unificado%20Retroalimentacion%20y%20Scope.md) |
| Módulo de Cupones existente | [`specs/features/06-coupons/`](../06-coupons/) / `app/Models/Coupon.php` |
| Convenciones de arquitectura | `AGENTS.md` / `.ai/project-conventions rules` |
| Calidad EARS y trazabilidad | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |

---

## 2. User Stories

1. **Como** administrador, **quiero** configurar pop-ups promocionales con imagen, textos bilingües, retraso de aparición (`delay_seconds`) y cupón de descuento asociado, **para** incentivar la compra de visitantes.
2. **Como** administrador, **quiero** programar fechas de inicio y fin (`starts_at`, `ends_at`) y poder activar o pausar el pop-up en cualquier momento con un switch, **para** controlar campañas de marketing estacionales.
3. **Como** visitante, **quiero** ver un modal atractivo y no invasivo tras navegar unos segundos en la tienda, **para** descubrir ofertas exclusivas o códigos de descuento.
4. **Como** visitante, **quiero** poder copiar el código del cupón con un solo clic (o aplicarlo directamente), **para** usarlo de inmediato en mi carrito.
5. **Como** visitante, **quiero** cerrar el pop-up y que no vuelva a interrumpirme durante mi sesión o durante los próximos días, **para** una experiencia de navegación cómoda.

---

## 3. Criterios de Aceptación (EARS: R1 – Rn)

- **R1 (Vigencia y Selección):**  
  *Cuando* el visitante cargue la tienda,  
  *el sistema deberá* evaluar los pop-ups con `is_active = true`, dentro del rango de fechas (`starts_at` $\le$ now $\le$ `ends_at`), seleccionando el más prioritario o reciente.

- **R2 (Frecuencia y Descarte en Cliente):**  
  *Si* el visitante ya cerró el pop-up previamente,  
  *el sistema cliente (Alpine.js / localStorage)* no deberá mostrar el modal mientras el registro de descarte (`leen_popup_dismissed_{id}`) esté vigente (por defecto 7 días o hasta que cambie el ID del pop-up).

- **R3 (Temporizador de Aparición):**  
  *Cuando* la página termine de cargar y el pop-up sea elegible,  
  *el sistema cliente* deberá esperar el tiempo configurado en `delay_seconds` (mínimo 1s, por defecto 5s) antes de abrir el modal con transición suave.

- **R4 (Vinculación a Cupones):**  
  *Si* el pop-up tiene un `coupon_id` asociado válido,  
  *el sistema deberá* mostrar el código del cupón, un botón para copiarlo al portapapeles y un badge con el descuento aplicable.

- **R5 (Localización i18n):**  
  *Cuando* el pop-up se renderice,  
  *el sistema deberá* mostrar `title_es`, `subtitle_es`, `cta_text_es` si el locale es `es`, o sus variantes en inglés si el locale es `en`.

- **R6 (Administración en Filament):**  
  *Cuando* un administrador acceda a `PromotionalPopupResource`,  
  *el sistema deberá* permitir crear y editar pop-ups con subida de imagen, selección de cupón, selector de fechas y campos bilingües obligatorios.

- **R7 (Seguridad y Roles):**  
  *Si* un usuario no administrador intenta acceder al recurso de pop-ups en Filament,  
  *el sistema deberá* bloquear el acceso con 403 Forbidden.
