# F20 — Zonas de Envío y Google Places Autocomplete: Checklist de Tareas

> **Feature:** F20 · `20-shipping-zones`  
> **Estado:** Completa  
> **Criterios EARS:** [`requirements.md`](requirements.md) · **Diseño:** [`design.md`](design.md) · **QA Checklist:** [`qa-checklist.md`](qa-checklist.md)

---

## Fase 1: Configuración y Capa de Dominio (TDD)

- [x] **1.1 Matriz de Zonas en Configuración** `_(cubre R1, R5, R6, R7, R8, D1, D2, D3)_`
  - Actualizar `config/ecommerce.php` agregando el bloque jerárquico `shipping.zones` (ciudades, países, regiones, `allow_unlisted`).
  - Agregar `google_places_api_key` leyendo `env('GOOGLE_PLACES_API_KEY')`.
  - Agregar variables correspondientes a `.env.example`.

- [x] **1.2 Excepción de Destino no Soportado** `_(cubre R13, D3)_`
  - Crear `app/Exceptions/Orders/UnsupportedShippingDestinationException.php`.
  - Agregar claves de internacionalización en `lang/en/orders.php` y `lang/es/orders.php`.

- [x] **1.3 Tests Unitarios de `ShippingCostService` (RED)** `_(cubre R1, R2, R5, R6, R7, R8, R13, D8)_`
  - Crear `tests/Unit/Orders/ShippingCostServiceTest.php`.
  - Probar Cali ($10.000 COP) con mayúsculas y alias ("santiago-de-cali").
  - Probar Colombia resto ($15.000 COP).
  - Probar España y Europa (3.000 cents EUR).
  - Probar USA y Latam (3.000 cents USD).
  - Probar excepción ante país no listado con `allow_unlisted = false`.
  - Probar fallback ante país no listado con `allow_unlisted = true`.

- [x] **1.4 Refactorización de `ShippingCostService` (GREEN)** `_(cubre R1, R2, R5, R6, R7, R8, R13, D8)_`
  - Implementar método `calculate(CurrencyEnum $currency, ?string $country = null, ?string $city = null): int`.
  - Implementar método `defaultCostForCurrency(CurrencyEnum $currency): int`.
  - Ejecutar tests unitarios hasta que pasen en verde.

---

## Fase 2: Casos de Uso y Acciones del Checkout

- [x] **2.1 Actualizar `ValidateCartForCheckoutAction`** `_(cubre R2, R9, R11)_`
  - Permitir `$shippingCountry` y `$shippingCity` opcionales en el método invokable.
  - Llamar a `ShippingCostService::calculate(...)` con la ubicación recibida o default.
  - Adaptar o verificar tests existentes de checkout para mantener compatibilidad hacia atrás.

- [x] **2.2 Actualizar `CreateOrderFromCartAction`** `_(cubre R2, R3, R4, R13, D7)_`
  - Resolver `shippingSnapshot` antes del cálculo de totales.
  - Calcular autoritativamente el envío pasando `shipping_country` y `shipping_city` del snapshot.
  - Asegurar que el cálculo de `total` mantenga `shipping_cost` aislado de descuentos de cupones.

- [x] **2.3 Tests de Feature para Acciones de Pedido** `_(cubre R3, R4, R13)_`
  - Verificar que la orden creada con destino Cali congele `shipping_cost = 10000`.
  - Verificar que un cupón de 100% sobre subtotal conserve el cobro de envío.
  - Verificar que un destino sin cobertura aborte la creación del pedido.

---

## Fase 3: Checkout Storefront y Google Places Autocomplete

- [x] **3.1 Reactividad en Livewire `checkout-page.php`** `_(cubre R9, R11, R12)_`
  - Pasar `$this->shippingCountry` y `$this->shippingCity` a `$validateCartForCheckout(...)` en `loadPreview()`.
  - Implementar ganchos reactivos `updatedShippingCity()`, `updatedShippingCountry()`.
  - En `updatedShippingAddressId()`: sincronizar ciudad y país de la dirección guardada y recargar el preview.

- [x] **3.2 Integración Frontend en `checkout-page.blade.php`** `_(cubre R10, R14, D4)_`
  - Cargar condicionalmente el script de Google Maps JS API si la API Key está configurada.
  - Implementar inicialización de Autocomplete en `shippingAddressLine1`.
  - Configurar listener para evento de selección con Session Tokens automáticos y Field Masking a `addressComponents`.
  - Extraer defensivamente `shortText ?? short_name` y `longText ?? long_name` para asignar a Livewire.
  - Mantener inputs visibles y editables como fallback manual resiliente.

- [x] **3.3 Feedback de Destino no Soportado en UI** `_(cubre R13)_`
  - Mostrar alerta amigable en el checkout si el país seleccionado no tiene cobertura de envío.
  - Deshabilitar el botón de confirmación mientras el destino no sea válido.

---

## Fase 4: Pruebas de Integración y Verificación

- [x] **4.1 Tests de Integración Livewire** `_(cubre R5, R6, R9, R12)_`
  - Crear `tests/Feature/Orders/ShippingZonesCheckoutLivewireTest.php`.
  - Test: Cambiar `shippingCity` a "Cali" actualiza el total con 10.000 COP en vivo.
  - Test: Cambiar `shippingCity` a "Bogotá" actualiza el total con 15.000 COP en vivo.
  - Test: Cambiar `shippingCountry` a "ES" en carrito EUR actualiza con 30 €.
  - Test: Seleccionar dirección guardada de Cali aplica 10.000 COP.

- [x] **4.2 Formato y Calidad de Código**
  - Ejecutar Pint: `vendor/bin/sail bin pint --dirty --format agent`.
  - Ejecutar suite de pruebas de órdenes: `vendor/bin/sail artisan test --compact --filter=Shipping`.

- [x] **4.3 Preflight Audit & Checklist de QA**
  - Ejecutar skill `feature-qa-checklist` para generar la matriz de verificación.
  - Certificación mediante RDD (`gentle-ai review`).
