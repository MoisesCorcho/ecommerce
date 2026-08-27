# F-07: Barra de Anuncios Administrable (Top Bar / Announcement Bar)

> **Estado:** Specs en progreso  
> **ID:** F-07 · **Slug:** `15-announcement-bar`  
> **Prerequisitos:** Ninguno (independiente)  
> **Desbloquea:** Conversión y avisos globales en storefront  
> **Presupuesto / Estimación:** 9 horas (72 €)

---

## 1. Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Reporte unificado de cliente | [`docs/cliente/Reporte Unificado Retroalimentacion y Scope.md`](../../../docs/cliente/Reporte%20Unificado%20Retroalimentacion%20y%20Scope.md) |
| Convenciones de arquitectura y código | `AGENTS.md` / `.ai/project-conventions rules` |
| Calidad EARS y trazabilidad | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Layout Storefront | `resources/views/layouts/storefront.blade.php` |

---

## 2. User Stories

1. **Como** administrador de la tienda, **quiero** crear, editar y programar anuncios de texto bilingües (español e inglés) desde Filament, **para** comunicar promociones, avisos de envío gratis o novedades a los visitantes.
2. **Como** administrador, **quiero** asociar un enlace opcional (URL interna o externa) al anuncio, **para** redirigir el tráfico a una sección o producto específico.
3. **Como** administrador, **quiero** definir fechas de vigencia (`starts_at` y `ends_at`) y un switch de activación inmediata (`is_active`), **para** automatizar campañas sin tener que publicarlas manualmente a deshoras.
4. **Como** visitante, **quiero** ver el anuncio en mi idioma preferido (español o inglés) en la parte superior de la tienda, **para** estar al tanto de ofertas relevantes.
5. **Como** visitante, **quiero** poder cerrar el anuncio haciendo clic en un botón ("X"), **para** que no me estorbe durante la navegación si ya lo leí.
6. **Como** visitante, **quiero** que el anuncio cerrado permanezca oculto durante mi navegación, **pero** que si el administrador publica un nuevo anuncio diferente, este vuelva a mostrarse.

---

## 3. Criterios de Aceptación (EARS: R1 – Rn)

- **R1 (Vigencia y Estado):**  
  *Cuando* se consulte el anuncio para el storefront,  
  *el sistema deberá* retornar únicamente anuncios con `is_active = true`, `starts_at` nulo o menor/igual al momento actual, y `ends_at` nulo o mayor/igual al momento actual.

- **R2 (Prioridad y Ordenamiento):**  
  *Cuando* existan múltiples anuncios activos y vigentes,  
  *el sistema deberá* seleccionar el anuncio con el menor `sort_order` (y en caso de empate, el más recientemente creado / mayor `id`).

- **R3 (Localización del Mensaje):**  
  *Cuando* el storefront renderice el anuncio,  
  *el sistema deberá* mostrar `text_es` si el locale es `es`, o `text_en` si el locale es `en` (con fallback a `text_es` si `text_en` estuviera vacío).

- **R4 (Enlace Opcional):**  
  *Si* el anuncio tiene una `url` configurada,  
  *el sistema deberá* renderizar el texto o un contenedor cliqueable que dirija al usuario a dicha URL.  
  *Si* el anuncio no tiene `url`,  
  *el sistema deberá* renderizar el texto como elemento no interactivo (sin enlace).

- **R5 (Persistencia de Cierre en Cliente):**  
  *Cuando* el visitante haga clic en el botón de cierre ("X"),  
  *el sistema cliente (Alpine.js)* deberá ocultar la barra inmediatamente y registrar en `localStorage` el ID del anuncio descartado (`leen_announcement_dismissed_{id}`).

- **R6 (Reaparición ante Nuevo Anuncio):**  
  *Cuando* el visitante cargue una página y el anuncio activo tenga un ID que NO esté en `localStorage`,  
  *el sistema cliente* deberá mostrar la barra de anuncios normalmente.

- **R7 (Gestión Administrativa en Filament):**  
  *Cuando* un usuario con rol de administrador ingrese al panel Filament,  
  *el sistema deberá* permitir listar, crear, editar y eliminar registros de anuncios con validación de campos obligatorios (`text_es`, `text_en`, `is_active`, `sort_order`).

- **R8 (Protección de Acceso):**  
  *Si* un usuario no administrador intenta acceder o manipular los anuncios vía endpoints/Filament,  
  *el sistema deberá* denegar el acceso con HTTP 403.
