# Diseño Técnico — F-05: Regla de Descuento Progresivo en Carrito

> **Feature:** `17-cart-threshold-discount` (F-05)  
> **Alcance:** `CartPricingService`, `CartViewDTO`, DTOs de checkout, Livewire `CartPage` & `CartDrawer`, persistencia en snapshot de `Order`.

---

### 1.1 Configuración Centralizada en `config/ecommerce.php`

```php
'cart_threshold_discount' => [
    'enabled' => (bool) env('ECOMMERCE_THRESHOLD_DISCOUNT_ENABLED', true),
    'percentage' => (int) env('ECOMMERCE_THRESHOLD_DISCOUNT_PERCENTAGE', 10),
    'min_amounts' => [
        'COP' => (int) env('ECOMMERCE_THRESHOLD_MIN_COP', 1_200_000), // $1.200.000 COP (pesos)
        'EUR' => (int) env('ECOMMERCE_THRESHOLD_MIN_EUR', 30_000),     // €300,00 EUR (centavos)
        'USD' => (int) env('ECOMMERCE_THRESHOLD_MIN_USD', 32_000),     // $320,00 USD (centavos)
    ],
],
```

### 1.2 Extensión del Enum Existente `App\Enums\Commerce\CurrencyEnum`

El enum `CurrencyEnum` ya existe en el proyecto (`app/Enums/Commerce/CurrencyEnum.php`). Para mantener el dominio limpio y desacoplado, se le agrega el método helper que lee la configuración:

```php
namespace App\Enums\Commerce;

enum CurrencyEnum: string implements HasLabel
{
    case Cop = 'COP';
    case Eur = 'EUR';
    case Usd = 'USD';

    // ... métodos existentes (label, paymentProvider, minorUnits, symbol, format) ...

    /**
     * Monto mínimo para aplicar el 10% de descuento por volumen en carrito.
     */
    public function thresholdDiscountMinAmount(): int
    {
        return (int) config("ecommerce.cart_threshold_discount.min_amounts.{$this->value}", 0);
    }
}
```

---

## 2. Modificaciones en el Dominio

### 2.1 Actualización en `CartViewDTO`

Se amplía `CartViewDTO` con los campos calculados:
- `subtotal`: Subtotal bruto de las líneas del carrito.
- `thresholdDiscountAmount`: Monto descontado (0 si no califica, 10% del subtotal si califica).
- `thresholdMinAmount`: Monto mínimo requerido según la moneda.
- `remainingForThreshold`: Monto faltante para alcanzar el umbral (0 si ya lo alcanzó).
- `thresholdReached`: Booleano `true` si `subtotal >= thresholdMinAmount`.
- `total`: `subtotal - thresholdDiscountAmount`.

### 2.2 Lógica en `CartPricingService`

```php
public function view(Cart $cart): CartViewDTO
{
    // ... carga de líneas y cálculo de $subtotal ...
    
    $thresholdMin = $cart->currency->thresholdDiscountMinAmount();
    $thresholdReached = $subtotal >= $thresholdMin;
    $thresholdDiscountAmount = $thresholdReached ? (int) floor($subtotal * 0.10) : 0;
    $remainingForThreshold = max(0, $thresholdMin - $subtotal);
    $total = max(0, $subtotal - $thresholdDiscountAmount);

    return new CartViewDTO(
        // ...
        subtotal: $subtotal,
        thresholdDiscountAmount: $thresholdDiscountAmount,
        thresholdMinAmount: $thresholdMin,
        remainingForThreshold: $remainingForThreshold,
        thresholdReached: $thresholdReached,
        total: $total,
    );
}
```

---

## 3. Integración en Storefront (Livewire / Blade)

- Componentes afectados:
  - `resources/views/components/cart-page/cart-page.blade.php`
  - `resources/views/livewire/cart-drawer.blade.php`
- Elemento visual:
  - Barra de progreso interactiva (Tailwind CSS) con los colores de marca Leen (`bg-soft-gold`).
  - Formato de moneda con helpers del proyecto (`Money::format`).

---

## 4. Persistencia en Órdenes (`Order`)

- Al ejecutar `CreateOrderFromCartAction`, se almacena el desglose:
  - Si `thresholdDiscountAmount > 0`, se registra en el snapshot de descuentos de la orden (`Order::discount_amount` o campo específico `Order::threshold_discount_amount`).

---

## 5. Estrategia de Testing (TDD)

1. **Unit / Domain Tests (`tests/Unit/Services/CartPricingServiceTest.php` o `tests/Feature/Domain/CartThresholdDiscountTest.php`):**
   - Carrito en EUR con 290 EUR: 0% descuento, faltan 10 EUR.
   - Carrito en EUR con 300 EUR: 10% descuento (30 EUR de ahorro), total 270 EUR.
   - Carrito en USD con 350 USD: 10% descuento (35 USD de ahorro), total 315 USD.
   - Carrito en COP con 1.500.000 COP: 10% descuento (150.000 COP de ahorro), total 1.350.000 COP.
2. **Feature Tests de Checkout / Órdenes (`tests/Feature/Orders/CreateOrderWithThresholdDiscountTest.php`):**
   - Conversión de carrito calificado a orden congelando el descuento.
3. **Storefront Livewire Tests (`tests/Feature/Livewire/CartThresholdBannerTest.php`):**
   - Renderizado del banner y barra de progreso al añadir/quitar ítems.
