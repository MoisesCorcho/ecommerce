# F20 — Zonas de Envío y Google Places Autocomplete: Diseño Técnico

> **Feature:** F20 · `20-shipping-zones`  
> **Estado:** Documentación de diseño  
> **Alineación:** [`AGENTS.md`](../../../AGENTS.md), [`marketplace-security`](../../../.agents/skills/marketplace-security/SKILL.md), [`specs/features/20-shipping-zones/requirements.md`](requirements.md)

---

## 1. Arquitectura y Flujo de Datos

El cálculo y la asignación del costo de envío siguen un flujo autoritativo en el backend:

```
┌──────────────────────────────────────────────────────────┐
│ Navegador / Checkout Storefront                         │
│  1. Input Google Places Autocomplete (<gmp-place-auto>)  │
│  2. Evento 'gmp-select' -> extrae country (ISO-2), city  │
│  3. $wire.set('shippingCity', ...) & 'shippingCountry'   │
└────────────────────────────┬─────────────────────────────┘
                             │ Livewire Request (loadPreview)
┌────────────────────────────▼─────────────────────────────┐
│ ValidateCartForCheckoutAction                           │
│  - Recibe $cartId, $owner, $couponCode, $country, $city  │
│  - Invoca ShippingCostService::calculate(...)            │
│  - Devuelve CheckoutPreviewDTO con shippingCost exacto   │
└────────────────────────────┬─────────────────────────────┘
                             │ Confirm (Crear Pedido)
┌────────────────────────────▼─────────────────────────────┐
│ CreateOrderFromCartAction                                │
│  - DB::transaction con lockForUpdate()                   │
│  - Recalcula autoritativamente ShippingCostService       │
│  - Congela snapshot inmutable en la tabla `orders`       │
└──────────────────────────────────────────────────────────┘
```

---

## 2. Esquema de Configuración (`config/ecommerce.php`)

Se reemplaza la estructura de tarifa plana única por la matriz de zonas jerárquica:

```php
'shipping' => [
    'google_places_api_key' => env('GOOGLE_PLACES_API_KEY', ''),

    'zones' => [
        // 1. Excepciones de tarifas por ciudad específica dentro de un país (ISO-2)
        'cities' => [
            'CO' => [
                'cali' => [
                    'currency' => \App\Enums\Commerce\CurrencyEnum::Cop,
                    'cost' => 10_000, // $10.000 COP
                    'aliases' => ['santiago-de-cali'],
                ],
            ],
        ],

        // 2. Tarifa plana por país (nacional)
        'countries' => [
            'CO' => [
                'currency' => \App\Enums\Commerce\CurrencyEnum::Cop,
                'cost' => 15_000, // $15.000 COP
            ],
        ],

        // 3. Regiones macro-geográficas / internacionales
        'regions' => [
            'europe' => [
                'currency' => \App\Enums\Commerce\CurrencyEnum::Eur,
                'cost' => 3_000, // 30.00 EUR (en centavos)
                'countries' => [
                    'ES', 'FR', 'DE', 'IT', 'PT', 'GB', 'NL', 'BE', 'CH', 'AT', 'SE', 'NO',
                    'DK', 'FI', 'IE', 'PL', 'CZ', 'GR', 'RO', 'HU',
                ],
            ],
            'americas' => [
                'currency' => \App\Enums\Commerce\CurrencyEnum::Usd,
                'cost' => 3_000, // $30.00 USD (en centavos)
                'countries' => [
                    'US', 'MX', 'PA', 'CR', 'EC', 'PE', 'CL', 'AR', 'UY', 'PY',
                    'BO', 'BR', 'DO', 'GT', 'SV', 'HN', 'NI',
                ],
            ],
        ],

        // 4. Política para destinos no listados
        'allow_unlisted' => (bool) env('ECOMMERCE_SHIPPING_ALLOW_UNLISTED', false),
        'unlisted_fallback_cost_usd' => (int) env('ECOMMERCE_SHIPPING_UNLISTED_COST_USD', 3_000),
    ],
],
```

---

## 3. Capa de Dominio y Servicios

### `ShippingCostService` (`app/Services/Orders/ShippingCostService.php`)

Responsabilidad única: calcular el costo de envío según moneda y destino geográfico.

```php
namespace App\Services\Orders;

use App\Enums\Commerce\CurrencyEnum;
use App\Exceptions\Orders\UnsupportedShippingDestinationException;
use Illuminate\Support\Str;

class ShippingCostService
{
    /**
     * Resuelve el costo de envío según la jerarquía de zonas.
     *
     * @throws UnsupportedShippingDestinationException
     */
    public function calculate(CurrencyEnum $currency, ?string $country = null, ?string $city = null): int
    {
        // 1. Si no hay destino especificado (p.ej. al entrar al checkout), retorna default por moneda
        if ($country === null || trim($country) === '') {
            return $this->defaultCostForCurrency($currency);
        }

        $countryCode = strtoupper(trim($country));
        $citySlug = $city !== null ? Str::slug(trim($city)) : '';

        // 2. Evaluar excepciones de ciudad específica
        $cityCost = $this->resolveCityOverride($countryCode, $citySlug, $currency);
        if ($cityCost !== null) {
            return $cityCost;
        }

        // 3. Evaluar tarifa nacional por país
        $countryCost = $this->resolveCountryRate($countryCode, $currency);
        if ($countryCost !== null) {
            return $countryCost;
        }

        // 4. Evaluar regiones internacionales
        $regionCost = $this->resolveRegionRate($countryCode, $currency);
        if ($regionCost !== null) {
            return $regionCost;
        }

        // 5. Destinos no listados (fallback o bloqueo)
        if ((bool) config('ecommerce.shipping.zones.allow_unlisted', false)) {
            return (int) config('ecommerce.shipping.zones.unlisted_fallback_cost_usd', 3_000);
        }

        throw UnsupportedShippingDestinationException::forCountry($countryCode);
    }

    public function defaultCostForCurrency(CurrencyEnum $currency): int
    {
        return match ($currency) {
            CurrencyEnum::Cop => (int) (config('ecommerce.shipping.zones.countries.CO.cost') ?? 15_000),
            CurrencyEnum::Eur => (int) (config('ecommerce.shipping.zones.regions.europe.cost') ?? 3_000),
            CurrencyEnum::Usd => (int) (config('ecommerce.shipping.zones.regions.americas.cost') ?? 3_000),
        };
    }
}
```

---

## 4. Actualización de Casos de Uso (Actions)

### `ValidateCartForCheckoutAction` (`app/Actions/Orders/ValidateCartForCheckoutAction.php`)
* Se amplía la firma de `__invoke` para recibir opcionalmente destino:
  ```php
  public function __invoke(
      int $cartId,
      CartOwnerDTO $owner,
      ?string $couponCode = null,
      ?string $shippingCountry = null,
      ?string $shippingCity = null,
  ): CheckoutPreviewDTO
  ```
* Se reemplaza `$this->shippingCostService->standardCost($cart->currency)` por:
  ```php
  $shippingCost = $this->shippingCostService->calculate(
      $cart->currency,
      $shippingCountry,
      $shippingCity,
  );
  ```

### `CreateOrderFromCartAction` (`app/Actions/Orders/CreateOrderFromCartAction.php`)
* En el paso de cálculo de totales, se evalúa con el `shippingSnapshot`:
  ```php
  $shippingSnapshot = $this->resolveShippingSnapshot($dto);
  $shippingCost = $this->shippingCostService->calculate(
      $cart->currency,
      $shippingSnapshot['shipping_country'] ?? null,
      $shippingSnapshot['shipping_city'] ?? null,
  );
  ```
* Si el destino no es soportado, `UnsupportedShippingDestinationException` aborta la transacción limpiamente.

---

## 5. Excepciones de Dominio

### `UnsupportedShippingDestinationException` (`app/Exceptions/Orders/UnsupportedShippingDestinationException.php`)
* Extiende `DomainException` o `RuntimeException`.
* Provee un método `storefrontMessage()` traducible mediante `__('orders.errors.unsupported_destination')`.

---

## 6. Frontend: Integración Google Places en Livewire

### Componente `resources/views/components/checkout-page/checkout-page.php`
* En `mount()`: invoca `$this->loadPreview($validateCartForCheckout)`.
* Agrega hooks reactivos para ubicación:
  ```php
  public function updatedShippingCity(ValidateCartForCheckoutAction $validateCartForCheckout): void
  {
      $this->loadPreview($validateCartForCheckout);
  }

  public function updatedShippingCountry(ValidateCartForCheckoutAction $validateCartForCheckout): void
  {
      $this->loadPreview($validateCartForCheckout);
  }

  public function updatedShippingAddressId(): void
  {
      // Llena propiedades desde Address guardada y recarga preview
      // ...
      $this->loadPreview(app(ValidateCartForCheckoutAction::class));
  }
  ```

### Template `checkout-page.blade.php`
* En la cabecera del checkout o pie de página: carga condicional del SDK de Google Maps si `config('ecommerce.shipping.google_places_api_key')` está presente.
* En `shippingAddressLine1`: inicializa el componente de Autocomplete.
* Al emitirse la selección:
  ```javascript
  // Extractor defensivo compatible con Places API New y Legacy
  const getComp = (types, useShort = false) => {
      const c = place.addressComponents?.find(item => types.some(t => item.types.includes(t)));
      if (!c) return '';
      return useShort ? (c.shortText ?? c.short_name ?? '') : (c.longText ?? c.long_name ?? '');
  };

  const country = getComp(['country'], true);
  const city = getComp(['locality', 'sublocality', 'postal_town']);
  const state = getComp(['administrative_area_level_1']);
  const postalCode = getComp(['postal_code']);
  const addressLine1 = (getComp(['route']) + ' ' + getComp(['street_number'])).trim() || place.formattedAddress;

  $wire.set('shippingAddressLine1', addressLine1);
  $wire.set('shippingCity', city);
  $wire.set('shippingState', state);
  $wire.set('shippingCountry', country);
  $wire.set('shippingPostalCode', postalCode);
  ```

---

## 7. Internacionalización (`lang/{en,es}/orders.php`)

Claves nuevas a agregar:
* `orders.shipping.unsupported_country`: *"Actualmente no realizamos envíos al país seleccionado."*
* `orders.shipping.zone_cali`: *"Envío local Cali"*
* `orders.shipping.zone_national`: *"Envío nacional Colombia"*
* `orders.shipping.zone_international`: *"Envío internacional"*
* `orders.checkout.placeholders.search_address`: *"Escribe tu calle o dirección..."*

---

## 8. Estrategia de Testing (TDD)

1. **Unit Tests (`tests/Unit/Orders/ShippingCostServiceTest.php`)**:
   * Cotización para Cali con slug exacto y alias.
   * Cotización para otras ciudades de Colombia (tarifa nacional 15.000).
   * Cotización para países europeos en EUR (30€).
   * Cotización para países de América en USD ($30 USD).
   * Excepción al cotizar país no soportado cuando `allow_unlisted = false`.
   * Fallback al cotizar país no soportado cuando `allow_unlisted = true`.
2. **Feature Tests (`tests/Feature/Orders/ShippingZonesCheckoutTest.php`)**:
   * Livewire: cambiar `shippingCity` a "Cali" recalcula total con 10.000 COP.
   * Livewire: cambiar `shippingCity` a "Bogotá" recalcula total con 15.000 COP.
   * Livewire: seleccionar dirección guardada de Cali aplica 10.000 COP.
   * Order Create: `CreateOrderFromCartAction` guarda `shipping_cost` congelado.
   * Cupones: un cupón del 100% sobre subtotal sigue cobrando el envío exacto.
