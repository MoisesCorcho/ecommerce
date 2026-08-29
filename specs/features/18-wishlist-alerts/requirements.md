# F18 — Notificaciones Automáticas de Wishlist (Marketing Alerts)

> **Estado:** Completa  
> **ID:** F18 (F-03) · **Slug:** `18-wishlist-alerts`  

> **Prerequisitos:** F01 (Catálogo), F08 (Auth storefront), F10 (Wishlist) — ver [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **Desbloquea:** Automatización de marketing, recuperación de interés y fidelización  
> **Presupuesto / Estimación:** 13 horas (104 €)

---

## 1. Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Producto y roadmap | [`specs/_global/01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md) |
| Calidad EARS y SDD | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Reporte unificado de cliente | [`docs/cliente/Reporte Unificado Retroalimentacion y Scope.md`](../../../docs/cliente/Reporte%20Unificado%20Retroalimentacion%20y%20Scope.md) |
| Feature Wishlist existente | [`specs/features/10-wishlist/`](../10-wishlist/) / `app/Models/Wishlist.php` |
| Convenciones de arquitectura | [`AGENTS.md`](../../../AGENTS.md) / `.ai/project-conventions rules` |
| Modelo de datos y monedas | `app/Models/{Wishlist,ProductVariant,ProductVariantPrice,User}.php`, `app/Enums/Commerce/CurrencyEnum.php` |

---

## 2. User Stories

1. **Como** comprador registrado con artículos en mi lista de deseos, **quiero** recibir un correo elegante cuando un bolso guardado baje de precio, **para** aprovechar la oportunidad de compra de inmediato.
2. **Como** comprador con artículos en mi lista de deseos, **quiero** recibir un aviso cuando queden las últimas unidades en stock (1 a 3 unidades), **para** no quedarme sin mi producto favorito antes de que se agote.
3. **Como** comprador, **no quiero** recibir correos repetitivos o saturación de alertas sobre el mismo producto de forma diaria o masiva.
4. **Como** administrador de la tienda, **quiero** que el sistema ejecute automáticamente una tarea programada que evalúe y despache estas alertas sin intervención manual, registrando cada envío para auditoría.

---

## 3. Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| **D1** | **Multi-moneda en wishlist** | Se extiende `wishlists` con `price_when_added` (entero en minor units) y `currency_when_added` (`CurrencyEnum`). Al guardar un favorito, `ToggleWishlistAction` captura el precio y moneda de contexto del comprador. Si una fila legacy carece de precio guardado (`null`), se evalúa si la variante tiene oferta activa (`hasDiscount()`) en la moneda por defecto. |
| **D2** | **Cooldown anti-spam (7 días)** | El período de supresión de 7 días se aplica por tupla estricta `(user_id, product_variant_id, notification_type)`. Si un usuario recibió alerta de precio hace 3 días y el stock baja a 1 unidad, la alerta de stock sí es elegible; pero no se repetirá el mismo tipo de alerta dentro de los 7 días. |
| **D3** | **Límite de volumen por usuario** | Para prevenir saturación de buzón (deliverability/reputación SMTP), un usuario recibirá como máximo 3 correos de alertas por ejecución del comando. Si tiene más candidatos, se despachan los 3 con mayor descuento o menor stock relativo y el resto se evalúa en la siguiente corrida. |
| **D4** | **Invariantes de catálogo y preventa** | Solo son elegibles variantes activas pertenecientes a productos activos y publicados (`scopePublishedForStorefront`). Productos en preventa (`is_preorder = true`) y variantes con stock = 0 están expresamente excluidos de las alertas de stock bajo. |
| **D5** | **Elegibilidad de usuarios** | Solo se envían alertas a usuarios registrados con correo verificado (`email_verified_at IS NOT NULL`) y que no hayan sido eliminados (`deleted_at IS NULL`). |
| **D6** | **Orquestación de dominio** | La evaluación y despacho de alertas reside en una Action de dominio (`SendWishlistAlertsAction`). El comando Artisan `app:send-wishlist-alerts` actúa únicamente como entrypoint CLI delgado. |
| **D7** | **Vocabulario tipado en Enum** | Los tipos de notificación se encapsulan en el enum respaldado `WishlistNotificationTypeEnum` (`price_drop`, `low_stock`). |
| **D8** | **Localización e i18n** | Todo el copy de los correos (asuntos, encabezados, botones CTA, pie de página) reside en `lang/{es,en}/wishlist.php`. |

---

## 4. Criterios de Aceptación (EARS)

### Happy Path

### R1 — Detección y despacho de alerta por rebaja de precio
CUANDO se ejecuta la evaluación periódica de alertas de wishlist,  
EL SISTEMA DEBE identificar las variantes guardadas cuyo precio actual en la moneda guardada sea estrictamente menor a `price_when_added` (o que presenten `compare_at_price > price` activo),  
Y DEBE despachar un correo de tipo `price_drop` al comprador con el precio anterior, precio rebajado formateado con su símbolo/moneda y botón CTA directo a la tienda.

### R2 — Detección y despacho de alerta por stock crítico
CUANDO una variante guardada en la wishlist de un comprador pertenezca a un producto que no es preventa y cuente con un inventario disponible entre 1 y 3 unidades inclusive ($1 \le \text{stock} \le 3$),  
EL SISTEMA DEBE considerarla candidata y despachar un correo de tipo `low_stock` advirtiendo las últimas unidades disponibles  
SIN despachar la alerta si el stock es 0 (agotado).

### R3 — Registro inmutable de log de notificación
CUANDO se envía exitosamente una alerta de wishlist (de precio o stock),  
EL SISTEMA DEBE persistir de forma inmutable un registro en el log de notificaciones con `user_id`, `product_variant_id`, `notification_type` y la marca de tiempo `sent_at`.

### R4 — Plantillas de correo responsive con branding Leen
CUANDO se renderiza cualquiera de los correos de alertas de wishlist (`WishlistPriceDropMail` o `WishlistLowStockMail`),  
EL SISTEMA DEBE generar un correo HTML/Markdown responsive que incluya el logo oficial de Leen, imagen principal del bolso, nombre de variante (color/talla), precios formateados según la moneda y botón de acción directa.

### R5 — Captura de precio y moneda al agregar a la lista de deseos
CUANDO un comprador autenticado guarda una variante en su lista de deseos mediante `ToggleWishlistAction`,  
EL SISTEMA DEBE persistir la variante junto con el precio actual y la moneda activa de navegación en `wishlists`  
SIN alterar el comportamiento cuando la acción sea remover un favorito.

---

### Validación y Error (Exclusiones y Rate Limiting)

### R6 — Supresión por rate-limiting y cooldown anti-spam (7 días)
SI un usuario ya recibió una notificación del mismo tipo para la misma variante en los últimos 7 días corridos (`sent_at >= now() - 7 days`),  
EL SISTEMA DEBE omitir el envío del correo y registrar la omisión  
SIN actualizar el log de notificaciones.

### R7 — Exclusión de variantes inactivas, despublicadas o en preventa
SI la variante guardada en la wishlist o su producto asociado se encuentra inactivo, despublicado en el catálogo o marcado como producto en preventa (`is_preorder = true`),  
EL SISTEMA DEBE excluir automáticamente dicha variante de la evaluación de alertas de stock crítico  
SIN emitir ningún correo al comprador.

### R8 — Exclusión de usuarios no verificados o eliminados
SI un usuario que posee artículos en su lista de deseos no ha verificado su dirección de correo electrónico (`email_verified_at` nulo) o se encuentra en estado eliminado (`deleted_at` no nulo),  
EL SISTEMA DEBE ignorar sus registros de wishlist  
SIN intentar despachar correos ni registrar logs de envío.

### R9 — Límite de volumen de alertas por corrida
CUANDO un usuario califique para más de 3 alertas en una sola ejecución del proceso,  
EL SISTEMA DEBE despachar únicamente las 3 alertas prioritarias (mayor porcentaje de descuento o menor stock) en dicha corrida  
SIN saturar el buzón del destinatario.
