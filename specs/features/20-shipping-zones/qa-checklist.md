# QA Verification Checklist: 20-shipping-zones — Zonas de Envío y Google Places Autocomplete

> **Feature Specs:** [`specs/features/20-shipping-zones/`](requirements.md)  
> **Fecha:** 2026-09-07  
> **Alcance:** Verificación integral de flujos de checkout, cálculo de tarifas diferenciadas (Cali, resto de Colombia, Europa, EE. UU. / Latam), autocompletado con Google Places API (New), fallbacks manuales y pruebas de no regresión.

---

### 1. 🌟 Happy Path & Flujos Principales
*Casos de uso normales de inicio a fin.*

- [ ] **TC-01 — Cálculo de tarifa local de Cali ($10.000 COP) mediante autocompletado**
  * **Precondición:** Carrito activo en moneda COP con ítems por valor de $50.000 COP. Usuario en pantalla de checkout (`/checkout`).
  * **Pasos:** 
    1. Escribir en el campo de dirección: `"Calle 5 # 38-25, Cali, Colombia"`.
    2. Seleccionar la sugerencia de Google Places Autocomplete.
  * **Resultado Esperado:**
    - Los campos ocultos o sincronizados `shippingCity` y `shippingCountry` reciben `"Cali"` y `"CO"`.
    - El resumen del pedido actualiza en vivo el costo de envío a `$10.000 COP`.
    - Total de la orden refleja `$60.000 COP`.
    - Al confirmar el pedido, la tabla `orders` almacena `shipping_cost = 10000`, `total = 60000` y `shipping_city = 'Cali'`.
  * **Criterios EARS:** `R1`, `R2`, `R9`, `R10`

- [ ] **TC-02 — Cálculo de tarifa nacional para Colombia fuera de Cali ($15.000 COP)**
  * **Precondición:** Carrito activo en moneda COP ($50.000 COP).
  * **Pasos:**
    1. Ingresar o autocompletar dirección con ciudad `"Medellín"` o `"Bogotá"` y país `"Colombia"` (`CO`).
  * **Resultado Esperado:**
    - El resumen de Livewire actualiza el costo de envío a `$15.000 COP`.
    - Total de la orden refleja `$65.000 COP`.
    - La orden creada congela `shipping_cost = 15000`.
  * **Criterios EARS:** `R5`, `R9`, `R11`

- [ ] **TC-03 — Tarifa plana internacional para Europa (30 € = 3.000 cents EUR)**
  * **Precondición:** Carrito con moneda EUR (€50,00).
  * **Pasos:**
    1. Ingresar dirección con país España (`ES`) o Francia (`FR`).
  * **Resultado Esperado:**
    - Costo de envío se calcula en `30,00 €` (`3.000 cents`).
    - Total se actualiza en vivo a `80,00 €`.
    - La orden creada congela `shipping_cost = 3000`, `currency = 'EUR'`.
  * **Criterios EARS:** `R6`, `R9`, `R11`

- [ ] **TC-04 — Tarifa plana internacional para EE. UU. y Latinoamérica ($30 USD = 3.000 cents USD)**
  * **Precondición:** Carrito con moneda USD ($50.00).
  * **Pasos:**
    1. Ingresar dirección con país Estados Unidos (`US`) o México (`MX`).
  * **Resultado Esperado:**
    - Costo de envío se calcula en `$30.00 USD` (`3.000 cents`).
    - Total se actualiza en vivo a `$80.00 USD`.
    - La orden creada congela `shipping_cost = 3000`, `currency = 'USD'`.
  * **Criterios EARS:** `R7`, `R9`, `R11`

- [ ] **TC-05 — Selección de dirección guardada en libreta de direcciones**
  * **Precondición:** Usuario autenticado con dirección guardada con ciudad `"Cali"` y país `"CO"`.
  * **Pasos:**
    1. Acceder al checkout.
    2. El selector de direcciones selecciona la dirección predeterminada de Cali.
  * **Resultado Esperado:**
    - Livewire dispara reactivamente la sincronización de `shippingCity` y `shippingCountry`.
    - El desglose de envío muestra `$10.000 COP` sin necesidad de escribir manualmente.
  * **Criterios EARS:** `R12`

---

### 2. 🎚️ Valores Límite y Validaciones de Entrada
*Comprobación de límites, mayúsculas, tildes y variantes de nombres.*

- [ ] **TC-06 — Variaciones ortográficas y mayúsculas en nombre de Cali**
  * **Precondición:** Carrito COP.
  * **Pasos:**
    1. Ingresar manualmente `"cali"`, `"CALI"`, `"CaLi"` o `"Santiago de Cali"`.
  * **Resultado Esperado:**
    - La normalización `Str::slug()` unifica los términos y reconoce la regla de zona local.
    - El envío siempre se liquida en `$10.000 COP`.
  * **Criterios EARS:** `R1`, `R5`, `D8`

- [ ] **TC-07 — Campos de dirección editables manualmente (Fallback resiliente)**
  * **Precondición:** Variable `GOOGLE_PLACES_API_KEY` vacía o bloqueo de red del script de Google Maps.
  * **Pasos:**
    1. Escribir manualmente dirección, ciudad y país en los inputs del formulario.
  * **Resultado Esperado:**
    - Los inputs son interactivos y aceptan entrada manual.
    - El evento `wire:model.live.blur` recalcula las tarifas en el desenfoque sin arrojar errores de JavaScript.
  * **Criterios EARS:** `R14`

---

### 3. 💥 Inyección de Errores y Modos de Fallo
*Comportamiento ante destinos sin cobertura o APIs no disponibles.*

- [ ] **TC-08 — País no soportado con `allow_unlisted = false`**
  * **Precondición:** `config('ecommerce.shipping.zones.allow_unlisted') === false`.
  * **Pasos:**
    1. En un carrito USD, seleccionar un país no listado (ej: Japón `"JP"`, Sudáfrica `"ZA"`).
  * **Resultado Esperado:**
    - Se produce y captura `UnsupportedShippingDestinationException`.
    - La interfaz muestra un mensaje de error claro: `"Lo sentimos, actualmente no realizamos envíos a JP. Por favor contáctanos si requieres asistencia."`.
    - El botón de confirmación de pedido (`[data-checkout-submit]`) se deshabilita (`disabled`).
    - Si se intenta enviar la petición por backend, la acción no genera la orden en la BD.
  * **Criterios EARS:** `R8`, `R13`

- [ ] **TC-09 — País no soportado con `allow_unlisted = true` (Fallback activado)**
  * **Precondición:** Configurar temporalmente `allow_unlisted = true` con `unlisted_fallback_cost_usd = 3000`.
  * **Pasos:**
    1. Seleccionar país Japón `"JP"`.
  * **Resultado Esperado:**
    - No se genera excepción.
    - Se aplica la tarifa de fallback de $30.00 USD.
    - El botón de confirmación permanece habilitado.
  * **Criterios EARS:** `R8`

- [ ] **TC-10 — Error de carga del script de Google Places**
  * **Precondición:** Simular script bloqueado por adblocker o URL inválida.
  * **Pasos:**
    1. Cargar la página de checkout.
  * **Resultado Esperado:**
    - La consola no muestra errores no controlados.
    - El checkout sigue operativo mediante entrada manual estándar.
  * **Criterios EARS:** `R14`

---

### 4. ⚡ Concurrencia y Condiciones de Carrera (Race Conditions)
*Acciones simultáneas y coherencia en cobros.*

- [ ] **TC-11 — Cambio veloz de país/ciudad antes de confirmar**
  * **Pasos:**
    1. En checkout, cambiar rápidamente de `"Cali"` a `"Bogotá"` e inmediatamente hacer clic en confirmar.
  * **Resultado Esperado:**
    - El backend valida el snapshot de la dirección con `CreateOrderFromCartAction` de forma autoritativa.
    - El costo congelado en `orders.shipping_cost` es consistente con la ciudad final procesada (ej: 15.000 para Bogotá).
  * **Criterios EARS:** `R2`, `R3`

- [ ] **TC-12 — Aislamiento del costo de envío ante cupones del 100% (Free Cart)**
  * **Precondición:** Carrito de $50.000 COP con cupón del 100% de descuento sobre productos.
  * **Pasos:**
    1. Aplicar cupón del 100% en checkout con destino Cali ($10.000 COP envío).
  * **Resultado Esperado:**
    - El subtotal tiene descuento de -$50.000 COP.
    - El costo de envío de $10.000 COP permanece inalterado.
    - El total a pagar es exactamente `$10.000 COP`.
    - La orden permanece en estado `pending` (no se marca como `paid` gratis porque existe costo de envío pendiente de pasarela).
  * **Criterios EARS:** `R4`

---

### 5. 🔒 Permisos, Autorización y Seguridad
*Protección de datos y verificación de credenciales.*

- [ ] **TC-13 — Protección de Google Places API Key**
  * **Pasos:**
    1. Inspeccionar el código fuente HTML de la página de checkout.
  * **Resultado Esperado:**
    - La API key cargada en el `<script>` coincide con `GOOGLE_PLACES_API_KEY`.
    - La API key cuenta con restricciones de HTTP referrer (dominio) y API restrictions en Google Cloud Console.
    - No se exponen secretos de pasarelas ni claves privadas.
  * **Criterios EARS:** `D5`

- [ ] **TC-14 — Integridad de cálculos en backend (No confiar en el cliente)**
  * **Pasos:**
    1. Interceptar petición HTTP de confirmación e intentar alterar el valor de `shippingCost` o `total`.
  * **Resultado Esperado:**
    - El backend ignora cualquier parámetro de total enviado por el frontend.
    - `CreateOrderFromCartAction` recalcula autoritativamente el valor llamando a `ShippingCostService`.
  * **Criterios EARS:** `R2`, `R3`

---

### 6. 🎨 UI/UX, Accesibilidad e i18n
*Diseño visual, consistencia tipográfica y traducciones.*

- [ ] **TC-15 — Desglose de totales en el resumen del pedido**
  * **Pasos:**
    1. Visualizar el bloque de desglose de totales en pantalla de escritorio y móvil.
  * **Resultado Esperado:**
    - Línea `"Envío estándar"` claramente visible con etiqueta de zona/tarifa correspondiente.
    - Formato tabular con alineación a la derecha y tipografía monospace (`tabular-nums`).
    - Alerta de error en contenedor accesible (`role="alert"`).
  * **Criterios EARS:** `R9`, `R13`

- [ ] **TC-16 — Cadenas de localización ES / EN**
  * **Pasos:**
    1. Cambiar `APP_LOCALE=en` y verificar checkout y mensajes de error.
    2. Cambiar `APP_LOCALE=es` y verificar checkout.
  * **Resultado Esperado:**
    - En inglés: `"Standard shipping"`, `"We currently do not ship to..."`.
    - En español: `"Envío estándar"`, `"Lo sentimos, actualmente no realizamos envíos a..."`.
    - Cero textos crudos sin traducir.
  * **Criterios EARS:** `R13`

---

### 7. 🛡️ Pruebas de Regresión (Impacto Colateral)
*Verificación de que ninguna funcionalidad previa se ha degradado.*

- [ ] **TC-17 — Suite completa de órdenes y cupones intacta**
  * **Pasos:**
    1. Ejecutar `vendor/bin/sail artisan test --compact tests/Feature/Orders/ tests/Feature/Coupons/`.
  * **Resultado Esperado:**
    - 100% de las pruebas existentes pasan en verde (32 tests de dominio, 21 tests de shipping).
    - Los pedidos con promociones de envío gratis (`standard_cost_cop = 0`) se marcan como `paid` inmediatamente si el total es cero.
  * **Criterios EARS:** `R2`, `R3`

- [ ] **TC-18 — Desajuste de moneda con divisa activa en storefront (Currency Mismatch)**
  * **Precondición:** Carrito activo en COP. Seleccionar un destino de zona cuya moneda esté activa en el storefront (ej. España `ES` en zona `europe` con divisa `EUR`).
  * **Pasos:**
    1. En el checkout, asignar o autocompletar país `ES`.
  * **Resultado Esperado:**
    - Se lanza `UnsupportedShippingDestinationException::currencyMismatch`.
    - La interfaz muestra advertencia prescriptiva: *"Para envíos a ES, tu pedido debe procesarse en Euro (EUR)"*.
    - El usuario puede acudir al selector del navbar y cambiar su moneda a EUR para continuar.
  * **Criterios EARS:** `R15`

- [ ] **TC-19 — Destino con divisa inactiva en storefront (Degradación Limpia)**
  * **Precondición:** Carrito activo en COP o EUR. Seleccionar un destino de zona cuya moneda esté desactivada en el storefront (ej. Estados Unidos `US` o Argentina `AR` en zona `americas` con divisa `USD` inactiva).
  * **Pasos:**
    1. En el checkout, asignar o autocompletar país `US` o `AR`.
  * **Resultado Esperado:**
    - El sistema NO sugiere cambiar a USD (evita callejón sin salida).
    - Se lanza `UnsupportedShippingDestinationException::forCountry`.
    - La interfaz muestra destino no soportado: *"Actualmente no realizamos envíos al país o destino seleccionado (:country)"*.
  * **Criterios EARS:** `R13`, `R15`

- [ ] **TC-20 — Ciclo de vida y reusabilidad del buscador Google Places**
  * **Precondición:** API Key configurada.
  * **Pasos:**
    1. Escribir una dirección en el buscador y seleccionar una sugerencia.
    2. Borrar manualmente el texto del input de dirección.
    3. Volver a escribir una dirección diferente.
  * **Resultado Esperado:**
    - El dropdown de sugerencias vuelve a desplegarse reactivamente.
    - No se producen errores de JavaScript ni miembros privados de clases nativas envueltas en proxies reactivos.
  * **Criterios EARS:** `R10`, `R16`

- [ ] **TC-21 — Invariante de homogeneidad monetaria en zonas**
  * **Precondición:** Cálculo de costos en `ShippingCostService`.
  * **Pasos:**
    1. Invocar `calculate()` con combinaciones donde la regla de zona tenga una divisa distinta a la del carrito.
  * **Resultado Esperado:**
    - El servicio nunca mezcla valores de una moneda en el total de otra divisa.
    - Se rechaza el cálculo o se ignora la tarifa si no coincide con la moneda del carrito.
  * **Criterios EARS:** `R17`
