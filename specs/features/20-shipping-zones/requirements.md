# F20 — Zonas de Envío y Google Places Autocomplete

> **Estado:** Lista para implementar  
> **ID:** F20 · **Slug:** `20-shipping-zones`  
> **Fase:** 5 · Internacionalización y Checkout  
> **Prerequisitos:** F04 (checkout y órdenes), F14 (moneda USD y preferencias de mercado)  
> **Desbloquea:** Operación logística internacional y tarificación regional diferenciada

---

## 1. Fuentes canónicas (no duplicar)

| Tema | Fuente |
|---|---|
| Arquitectura y convenciones | [`AGENTS.md`](../../../AGENTS.md) / `.ai/guidelines/project-conventions.md` |
| Modelo de checkout base | [`specs/features/04-checkout-orders/`](../04-checkout-orders/) |
| Reglas de seguridad y cupones | [`marketplace-security`](../../../.agents/skills/marketplace-security/SKILL.md) / [`specs/features/06-coupons/`](../06-coupons/) |
| Monedas y unidades menores | [`app/Enums/Commerce/CurrencyEnum.php`](../../../app/Enums/Commerce/CurrencyEnum.php) |
| Esquema de persistencia | `app/Models/Order.php`, `Address.php`, `Cart.php` |
| Calidad EARS y SDD | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |

---

## 2. User Stories

1. **Como** comprador en Cali (Colombia), **quiero** que al ingresar mi dirección el costo de envío refleje la tarifa local preferencial ($10.000 COP), **para** pagar lo justo por entrega en la misma ciudad.
2. **Como** comprador en el resto de Colombia, **quiero** que el costo de envío aplique la tarifa plana nacional ($15.000 COP), **para** tener claridad sobre el costo de entrega en el territorio nacional.
3. **Como** comprador internacional en España o resto de Europa, **quiero** que el checkout aplique la tarifa plana para Europa (30 €), **para** recibir mi pedido internacional con transparencia en euros.
4. **Como** comprador internacional en Estados Unidos o Latinoamérica, **quiero** que el checkout aplique la tarifa plana regional ($30 USD), **para** comprar conociendo el costo fijo de envío en dólares.
5. **Como** comprador en cualquier país, **quiero** buscar mi dirección con autocompletado en un solo campo y que se rellenen ciudad, estado y código postal automáticamente, **para** no escribir manualmente y evitar errores de entrega.
6. **Como** comprador en una zona rural o sin cobertura de Google Maps, **quiero** poder ingresar o corregir mi dirección manualmente en los campos del formulario, **para** completar mi pedido sin bloqueos.
7. **Como** administrador de la tienda, **quiero** restringir envíos a países no soportados (`allow_unlisted = false`), **para** evitar pedidos que no podamos despachar logísticamente.

---

## 3. Alcance de la Feature

### Incluye:
* **Matriz de tarificación en cascada** en [`config/ecommerce.php`](../../../config/ecommerce.php) (`shipping.zones`):
  * Excepciones por ciudad normalizada (`CO.cali`: $10.000 COP).
  * Tarifa nacional por país (`CO`: $15.000 COP).
  * Zonas internacionales (`europe`: 30 €; `americas`: $30 USD).
  * Interruptor de restricción geográfica (`allow_unlisted`: `false` por defecto) y tarifa fallback opcional.
* **Refactorización de [`ShippingCostService`](../../../app/Services/Orders/ShippingCostService.php)**:
  * De firma plana `standardCost(CurrencyEnum)` a cálculo contextual autoritativo `calculate(CurrencyEnum $currency, ?string $country = null, ?string $city = null): int`.
  * Normalización canónica de ciudades vía `Str::slug()` (ej. *"Cali"*, *"santiago-de-cali"* → `"cali"`).
* **Integración de Google Places API (New)** en el checkout storefront:
  * Uso del componente web moderno `PlaceAutocompleteElement` o importación dinámica con `Session Tokens` automáticos.
  * Field masking estricto a `addressComponents` y `formattedAddress` (SKU básico económico).
  * Extractor defensivo (`shortText ?? short_name`, `longText ?? long_name`).
  * Autocompletado de los campos del formulario Livewire (`shippingAddressLine1`, `shippingCity`, `shippingState`, `shippingCountry`, `shippingPostalCode`).
* **Resiliencia y fallback manual**:
  * Conservación completa de inputs editables y del selector de país en caso de falla o ausencia de la API Key.
* **Reactividad en Livewire [`checkout-page.php`](../../../resources/views/components/checkout-page/checkout-page.php)**:
  * Hooks `updatedShippingCity`, `updatedShippingCountry`, `updatedShippingAddressId` que recalculan `loadPreview()` en vivo.
* **Validación autoritativa en [`CreateOrderFromCartAction`](../../../app/Actions/Orders/CreateOrderFromCartAction.php)**:
  * Recálculo obligatorio del costo de envío dentro de la transacción de base de datos antes de crear la orden.
  * Bloqueo con excepción controlada (`UnsupportedShippingDestinationException`) si el destino no tiene cobertura.
* **Tests automatizados**:
  * Unit tests para `ShippingCostService` (Cali, Colombia nacional, España/Europa, USA/Latam, países no listados).
  * Feature tests para Livewire checkout (recálculo al cambiar de ciudad/país, selección de direcciones guardadas).
  * Feature tests para creación de órdenes (respeto del snapshot de envío y no-descuento por cupones).

### No incluye:
* Multi-carrier shipping (cotización dinámica vía API con Servientrega, Coordinadora, DHL o FedEx en vivo).
* Módulos de gestión de zonas y tarifas en base de datos desde Filament (se mantiene ágil por configuración).
* Renderizado de mapas visuales interactivos en el checkout.
* Reglas de envío gratuitas dependientes del peso o dimensiones físicas de los paquetes.

---

## 4. Decisiones de Producto

| # | Tema | Decisión |
|---|---|---|
| **D1** | **Estrategia de Zonas** | Configuración en cascada en [`config/ecommerce.php`](../../../config/ecommerce.php): (1) Ciudad específica, (2) País nacional, (3) Región/Moneda, (4) Cobertura no listada. |
| **D2** | **Tarifas Iniciales fijas** | Cali: COP 10.000 · Colombia Nacional: COP 15.000 · Europa (EUR): 30 € (3.000 centavos) · USA/Latam (USD): $30 USD (3.000 centavos). |
| **D3** | **Países no contemplados** | `allow_unlisted => false` por defecto. Si el país no está en las listas, se bloquea con mensaje de validación: *"Actualmente no realizamos envíos a este país"*. Queda prevista la variable `unlisted_fallback_cost_usd` en config. |
| **D4** | **Proveedor de Autocompletado** | Google Places API (New) usando componente web con Session Tokens automáticos y Field Masking al SKU Basic. |
| **D5** | **Esquema de Base de Datos** | **0 migraciones**. Se reutilizan las columnas existentes de `addresses` y `orders` (`shipping_country`, `shipping_city`, `shipping_state`, etc.). |
| **D6** | **Tarifa por defecto en Mount** | Al entrar al checkout, se asume la tarifa estándar nacional/regional de la moneda activa del carrito (ej. COP 15.000, EUR 30€, USD $30). Al detectar Cali o cambiar de ciudad, se actualiza reactivamente. |
| **D7** | **Invariante Financiera de Cupones** | Los cupones aplican estrictamente sobre el subtotal de líneas de producto. El costo de envío **nunca** se reduce por un cupón porcentual o fijo. |
| **D8** | **Normalización de Ciudad** | Toda ciudad se evalúa en el backend tras normalizarse con `Str::slug()`. Se admiten alias conocidos (ej. `"santiago-de-cali"` mapea a `"cali"`). |

---

## 5. Criterios de Aceptación (EARS)

### Ubiquitous (Siempre activo)
* **R1 — Minor units:** EL SISTEMA DEBE almacenar y procesar todos los costos de envío en unidades menores según la moneda: pesos enteros para COP (`10000`, `15000`), centavos para EUR y USD (`3000`).
* **R2 — Invariante de Backend Autoritativo:** EL SISTEMA DEBE calcular el costo de envío exclusivamente en el servidor mediante [`ShippingCostService`](../../../app/Services/Orders/ShippingCostService.php). El frontend nunca envía montos de envío.
* **R3 — Aislamiento del Descuento:** EL SISTEMA DEBE calcular el total de la orden como `max(0, subtotal - threshold_discount - coupon_discount) + shipping_cost + tax_amount`, manteniendo intacto el costo de envío ante cualquier cupón.
* **R4 — Snapshot Inmutable:** CUANDO se crea una orden (`Order`), EL SISTEMA DEBE persistir `shipping_cost`, `shipping_city`, `shipping_state`, `shipping_country` en la tabla `orders` de manera inmutable.

### Event-driven (Disparado por eventos)
* **R5 — Cotización para Cali (Local):** CUANDO el país seleccionado sea `CO` y la ciudad normalizada sea `cali` (o su alias `santiago-de-cali`), EL SISTEMA DEBE fijar el costo de envío en `10.000` COP.
* **R6 — Cotización para Colombia Nacional:** CUANDO el país seleccionado sea `CO` y la ciudad no sea Cali, EL SISTEMA DEBE fijar el costo de envío en `15.000` COP.
* **R7 — Cotización para Europa:** CUANDO el país seleccionado pertenezca a la lista de países europeos configurados (`europe`) y la moneda sea `EUR`, EL SISTEMA DEBE fijar el costo de envío en `3.000` centavos (30 €).
* **R8 — Cotización para USA / Latam:** CUANDO el país seleccionado pertenezca a la lista de países de América configurados (`americas`) y la moneda sea `USD`, EL SISTEMA DEBE fijar el costo de envío en `3.000` centavos ($30 USD).
* **R9 — Recálculo reactivo en Livewire:** CUANDO el usuario modifique la ciudad (`shippingCity`), el país (`shippingCountry`) o seleccione una dirección guardada (`shippingAddressId`), EL COMPONENTE Livewire DEBE invocar `loadPreview()` recalculando el desglose de envío y el total en pantalla de forma inmediata.
* **R10 — Autocompletado Google Places:** CUANDO el usuario seleccione una sugerencia de dirección en el buscador de Google Places, EL CLIENTE DEBE extraer `shortText` para el país ISO-2, `longText` para ciudad, estado y código postal, y asignarlos a las propiedades de Livewire.

### State-driven (Control de estado y cobertura)
* **R11 — Carga inicial (Mount):** MIENTRAS el usuario no haya seleccionado una dirección ni ingresado ciudad, EL CHECKOUT DEBE inicializar el costo de envío con la tarifa estándar de la moneda activa del carrito.
* **R12 — Selección de Dirección Guardada:** MIENTRAS un usuario autenticado tenga seleccionada una dirección guardada (`addressMode === 'saved'`), EL CHECKOUT DEBE aplicar la tarifa de envío correspondiente a la ciudad y país de dicha dirección.

### Unwanted Behavior / Edge Cases (Modos de fallo)
* **R13 — Destino sin cobertura (`allow_unlisted = false`):** SI un comprador ingresa un país que no pertenece a ninguna zona configurada y `allow_unlisted` está deshabilitado, EL SISTEMA DEBE rechazar la confirmación de la orden lanzando `UnsupportedShippingDestinationException` y mostrar un mensaje de validación traducido en el checkout.
* **R14 — Falla o bloqueo de Google Places:** SI el script de Google Places no carga (ad-blocker, timeout o credenciales ausentes), EL FORMULARIO DEBE mantener los campos de entrada accesibles para que el usuario escriba su dirección manualmente sin bloquear la compra.
* **R15 — Desajuste de moneda de carrito vs país (Currency Mismatch):** SI un carrito intenta enviarse a un país cubierto por una zona de envío cuya divisa está activa en el storefront pero difiere de la del carrito (ej. país europeo en carrito COP o Colombia en carrito EUR), EL SISTEMA DEBE sugerir el cambio a dicha divisa. SI la divisa de la zona no está activa en el storefront (ej. USD inactivo), EL SISTEMA DEBE tratar el país como destino sin cobertura (R13).
* **R16 — Reusabilidad de Autocompletado y Ciclo de Vida:** CUANDO el usuario elimine la dirección ingresada o los campos autocompletados y vuelva a escribir en el campo de dirección, EL CLIENTE DEBE desplegar nuevamente las sugerencias de Google Places de forma reactiva sin requerir recargar la página.
* **R17 — Invariante de Homogeneidad Monetaria en Zonas de Envío:** EL SISTEMA DEBE rechazar cualquier cálculo de tarifa de zona cuya divisa configurada no coincida de forma exacta con la divisa activa del carrito, impidiendo la asignación de montos arbitrarios de una zona en una divisa a un carrito en otra divisa.
