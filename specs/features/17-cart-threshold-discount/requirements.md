# F-05: Regla de Descuento Progresivo en Carrito (Threshold Discount)

> **Estado:** Specs en progreso  
> **ID:** F-05 · **Slug:** `17-cart-threshold-discount`  
> **Prerequisitos:** F03 (Carrito), F04 (Checkout y Órdenes), F06 (Cupones)  
> **Desbloquea:** Incremento de ticket promedio (AOV) e incentivos visuales en carrito  
> **Presupuesto / Estimación:** 12 horas (96 €)

---

## 1. Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Reporte unificado de cliente | [`docs/cliente/Reporte Unificado Retroalimentacion y Scope.md`](../../../docs/cliente/Reporte%20Unificado%20Retroalimentacion%20y%20Scope.md) |
| Servicio de Carrito | `app/Services/Cart/CartPricingService.php` |
| Creación de Órdenes | `app/Actions/Orders/CreateOrderFromCartAction.php` |
| Carrito Livewire y Drawer | `resources/views/components/cart-page/` |
| Convenciones de arquitectura | `AGENTS.md` / `.ai/project-conventions rules` |

---

## 2. User Stories

1. **Como** comprador, **quiero** ver cuánto dinero me falta para desbloquear un 10% de descuento automático en mi carrito, **para** animarme a agregar más productos y alcanzar el beneficio.
2. **Como** comprador, **quiero** que al superar el monto mínimo (300 EUR / 320 USD / $1.200.000 COP) se aplique automáticamente el 10% de descuento al subtotal sin tener que escribir ningún código.
3. **Como** comprador, **quiero** ver reflejado el descuento desglosado claramente en el resumen del carrito, en el drawer y en el checkout.
4. **Como** administrador de la tienda, **quiero** que los umbrales respeten la moneda activa (EUR/USD/COP) en enteros/minor units y queden registrados de forma inmutable en la orden generada.

---

## 3. Criterios de Aceptación (EARS: R1 – Rn)

- **R1 (Umbrales Multi-moneda):**  
  *El sistema deberá* definir los siguientes umbrales exactos para el 10% de descuento:  
  - **EUR:** 30.000 centavos (€300,00)  
  - **USD:** 32.000 centavos ($320,00)  
  - **COP:** 1.200.000 pesos ($1.200.000)

- **R2 (Cálculo Automático en Carrito):**  
  *Cuando* el subtotal de productos elegibles en el carrito alcance o supere el umbral de la moneda activa,  
  *el sistema deberá* calcular un descuento del 10% (`floor(subtotal * 0.10)`) y restarlo del total a pagar.

- **R3 (Indicador Progresivo en Storefront):**  
  *Si* el subtotal del carrito es menor al umbral,  
  *el sistema deberá* mostrar un mensaje dinámico con el monto restante (*"Añade X [Moneda] más para obtener un 10% de descuento"*).  
  *Si* el subtotal supera el umbral,  
  *el sistema deberá* mostrar una confirmación positiva de beneficio desbloqueado (*"¡Tienes 10% de descuento aplicado!"*).

- **R4 (Interacción con Cupones):**  
  *Si* el comprador ingresa un cupón de descuento adicional,  
  *el sistema deberá* calcular el cupón sobre el subtotal base respetando la política comercial (descuento acumulable o aplicado sobre el neto remanente), sin permitir totales negativos.

- **R5 (Inmutabilidad en Checkout y Orden):**  
  *Cuando* el carrito se convierta en una orden mediante `CreateOrderFromCartAction`,  
  *el sistema deberá* congelar el monto del descuento por umbral en la orden generada (`Order`), reflejándolo en el snapshot financiero.
