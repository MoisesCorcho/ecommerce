# F-03: Notificaciones Automáticas de Wishlist (Marketing Alerts)

> **Estado:** Specs en progreso  
> **ID:** F-03 · **Slug:** `18-wishlist-alerts`  
> **Prerequisitos:** F10 (Wishlist), F01 (Catálogo)  
> **Desbloquea:** Recuperación de carritos abandonados, automatización de marketing y fidelización  
> **Presupuesto / Estimación:** 13 horas (104 €)

---

## 1. Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Reporte unificado de cliente | [`docs/cliente/Reporte Unificado Retroalimentacion y Scope.md`](../../../docs/cliente/Reporte%20Unificado%20Retroalimentacion%20y%20Scope.md) |
| Feature Wishlist existente | [`specs/features/10-wishlist/`](../10-wishlist/) / `app/Models/Wishlist.php` |
| Convenciones de arquitectura | `AGENTS.md` / `.ai/project-conventions rules` |

---

## 2. User Stories

1. **Como** cliente registrado con artículos en mi lista de deseos, **quiero** recibir un correo elegante cuando un bolso de mi interés baje de precio, **para** aprovechar la oportunidad de comprarlo.
2. **Como** cliente con artículos en mi lista de deseos, **quiero** recibir un aviso cuando queden las últimas unidades en stock (< 3 unidades), **para** no quedarme sin mi producto favorito.
3. **Como** comprador, **no quiero** recibir correos repetitivos o spam innecesario sobre el mismo producto a diario.
4. **Como** administrador de la tienda, **quiero** que el sistema ejecute automáticamente un comando programado que evalúe y despache estas alertas sin intervención manual.

---

## 3. Criterios de Aceptación (EARS: R1 – Rn)

- **R1 (Detección de Rebaja de Precio):**  
  *Cuando* se ejecute el comando `app:send-wishlist-alerts`,  
  *el sistema deberá* identificar aquellos productos en la wishlist del usuario cuyo precio actual sea inferior al precio registrado al guardarlo (o que tengan descuento activo).

- **R2 (Detección de Stock Crítico):**  
  *Cuando* un producto guardado en la wishlist tenga un inventario disponible mayor a 0 pero menor o igual a 3 unidades,  
  *el sistema deberá* considerarlo candidato para una alerta de stock bajo.

- **R3 (Control Anti-Spam / Frecuencia):**  
  *Si* el usuario ya recibió una alerta para el mismo producto y variante en los últimos 7 días,  
  *el sistema deberá* omitir el envío para evitar saturación de correo.

- **R4 (Plantillas y Branding de Correo):**  
  *Cuando* se despache un correo (`WishlistPriceDropMail` o `WishlistLowStockMail`),  
  *el sistema deberá* renderizar una plantilla HTML responsive con el logo de Leen, foto del producto, precio anterior/actual y botón CTA directo para añadir al carrito.

- **R5 (Registro de Envíos en Base de Datos):**  
  *Al* enviar con éxito una notificación,  
  *el sistema deberá* persistir un registro en `wishlist_notification_logs` con `user_id`, `product_variant_id`, `notification_type` y `sent_at`.
