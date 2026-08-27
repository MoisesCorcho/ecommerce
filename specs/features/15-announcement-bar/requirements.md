# F-07: Barra de Anuncios Administrable (Top Bar / Announcement Bar)

> **Estado:** Completa  
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

## 2. Decisiones de Producto

| ID | Decisión | Razón / Impacto |
|---|---|---|
| **D1** | **Motor de Traducción Dinámica (Spatie Translatable):** Se utiliza `Spatie Translatable` con columna `text` en formato JSON (`{"es": "...", "en": "..."}`). | Estandariza la internacionalización de contenidos administrables junto con F19 (Blog) y permite soportar N idiomas a futuro sin migraciones de esquema. |
| **D2** | **Fallback de Idioma:** Si el visitante consulta la tienda en un idioma sin traducción registrada (p. ej. `en`), se muestra la versión en español (`es`). | Evita barras vacías o mensajes rotos en el storefront ante traducciones pendientes. |
| **D3** | **Persistencia de Cierre (Dismiss) por ID:** El descarte del anuncio se almacena en el cliente (`localStorage`) asociado al ID del anuncio (`leen_announcement_dismissed_{id}`). | Si el administrador publica un nuevo anuncio (nuevo ID), este se mostrará a todos los visitantes aunque hayan cerrado el anterior. |
| **D4** | **Enlaces Flexibles (Relativos y Absolutos):** La URL del anuncio admite rutas internas (ej. `/products`, `/contacto`) y enlaces externos (`https://...`). | Enlaces externos abren en nueva pestaña con `rel="noopener noreferrer"`. Enlaces internos navegan en la misma pestaña. |
| **D5** | **Consistencia Temporal:** En Filament se valida que `ends_at` sea mayor o igual a `starts_at` si ambas fechas están presentes. | Previene la creación de anuncios con rangos de vigencia imposibles. |
| **D6** | **Gestión Eficiente en Panel:** Filament v5 integra `LocaleSwitcher` en cabecera y `ToggleColumn` en tabla para activación rápida. | Misma UX estándar que el resto de recursos traducibles del sistema. |
| **D7** | **Prevención de FOUC/CLS:** La barra de anuncios se inicializa con `x-cloak` en Alpine.js. | Evita el parpadeo de la barra durante la carga inicial en clientes que ya la habían descartado. |

---

## 3. User Stories

1. **Como** administrador de la tienda, **quiero** crear, editar y programar anuncios de texto bilingües desde Filament utilizando el selector de idioma nativo, **para** comunicar promociones, avisos de envío gratis o novedades a los visitantes de manera homogénea.
2. **Como** administrador, **quiero** asociar un enlace opcional (interno o externo) al anuncio, **para** redirigir el tráfico a una sección o producto específico.
3. **Como** administrador, **quiero** definir fechas de vigencia (`starts_at` y `ends_at`) y un switch de activación inmediata (`is_active`), **para** automatizar campañas sin tener que publicarlas manualmente a deshoras.
4. **Como** visitante, **quiero** ver el anuncio en mi idioma preferido (español o inglés con fallback) en la parte superior de la tienda, **para** estar al tanto de ofertas relevantes.
5. **Como** visitante, **quiero** poder cerrar el anuncio haciendo clic en un botón ("X"), **para** que no me estorbe durante la navegación si ya lo leí.
6. **Como** visitante, **quiero** que el anuncio cerrado permanezca oculto durante mi navegación, **pero** que si el administrador publica un nuevo anuncio diferente, este vuelva a mostrarse.

---

## 4. Criterios de Aceptación (EARS: R1 – R8)

### R1 — Vigencia y Estado de Anuncio
CUANDO se consulte el anuncio activo para el storefront,  
EL SISTEMA DEBE retornar únicamente anuncios marcados como activos (`is_active = true`), con fecha de inicio nula o en el pasado (`starts_at <= now()`), y con fecha de fin nula o en el futuro (`ends_at >= now()`).

### R2 — Prioridad y Ordenamiento Determinístico
CUANDO existan múltiples anuncios activos y vigentes simultáneamente,  
EL SISTEMA DEBE seleccionar aquel con el menor `sort_order` numérico (y en caso de empate, el de mayor `id` / creado más recientemente).

### R3 — Localización del Mensaje con Fallback
DONDE un visitante navega por cualquier página pública del storefront,  
CUANDO el componente de la barra de anuncios se renderiza,  
EL SISTEMA DEBE obtener la traducción del texto según el locale activo de la aplicación (`app()->getLocale()`), aplicando fallback al idioma base (`es`) si el idioma activo carece de traducción.

### R4 — Enlace Opcional e Interactividad
DONDE se renderiza un anuncio en el storefront,  
SI el anuncio tiene una `url` configurada,  
EL SISTEMA DEBE envolver el mensaje en un enlace interactivo (con `rel="noopener noreferrer"` y `target="_blank"` si es una URL externa).  
SI el anuncio no tiene `url`,  
EL SISTEMA DEBE renderizar el mensaje como texto plano no interactivo.

### R5 — Persistencia de Cierre en Cliente
DONDE un visitante visualiza la barra de anuncios en el storefront,  
CUANDO hace clic en el botón de cierre ("X"),  
EL SISTEMA CLIENTE (Alpine.js) DEBE ocultar la barra de inmediato y registrar en `localStorage` la clave de descarte asociada al ID del anuncio (`leen_announcement_dismissed_{id}`).

### R6 — Reaparición ante Nuevo Anuncio
DONDE un visitante que cerró un anuncio anterior navega por el storefront,  
CUANDO el anuncio activo y vigente en el servidor tiene un ID que NO está registrado como descartado en su `localStorage`,  
EL SISTEMA CLIENTE DEBE mostrar la barra de anuncios normalmente.

### R7 — Gestión Administrativa Multilingüe en Filament
DONDE un usuario administrador autenticado accede al recurso de Anuncios en Filament,  
CUANDO crea o edita un registro,  
EL SISTEMA DEBE permitir alternar entre idiomas mediante `LocaleSwitcher`, validar la obligatoriedad del texto en el idioma principal, validar que `ends_at >= starts_at`, y permitir alternar el estado activo directamente desde la tabla.

### R8 — Control de Acceso y Autorización
SI un usuario no autenticado o sin rol de administrador intenta acceder o manipular los registros de anuncios vía panel Filament o endpoints internos,  
EL SISTEMA DEBE rechazar la petición con código de respuesta HTTP 403 (Forbidden).
