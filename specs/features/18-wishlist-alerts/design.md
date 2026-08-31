# Diseño Técnico — F18: Notificaciones Automáticas de Wishlist

> **Feature:** `18-wishlist-alerts` (F18 / F-03)  
> **Alcance:** Migraciones de esquema, Enum de notificaciones, DTO de resultados, Action de dominio `SendWishlistAlertsAction`, extensión de `ToggleWishlistAction`, Mailables en `App\Mail\Wishlist\`, vistas Blade responsive en `resources/views/mail/wishlist/`, comando Artisan wrapper `app:send-wishlist-alerts`, Scheduler y suite de pruebas TDD.

---

## 1. Fuentes canónicas y referencias de steering

- **Roadmap y alcance:** [`specs/_global/01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)
- **Calidad y EARS:** [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md)
- **Convenciones de arquitectura:** [`AGENTS.md`](../../../AGENTS.md)
- **Modelos existentes:** `app/Models/{Wishlist,ProductVariant,ProductVariantPrice,User}.php`
- **Monedas e i18n:** `app/Enums/Commerce/CurrencyEnum.php`, `app/Support/Commerce/CurrentCurrency.php`

---

## 2. Modelo de Datos y Migraciones

### 2.1 Migración: `add_price_and_currency_to_wishlists_table`

```php
Schema::table('wishlists', function (Blueprint $table) {
    $table->unsignedInteger('price_when_added')->nullable()->after('product_variant_id');
    $table->string('currency_when_added', 3)->nullable()->after('price_when_added');
});
```

### 2.2 Migración: `create_wishlist_notification_logs_table`

```php
Schema::create('wishlist_notification_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
    $table->string('notification_type', 32); // Cast a WishlistNotificationTypeEnum
    $table->timestamp('sent_at')->useCurrent()->index();
    $table->timestamps();

    $table->index(
        ['user_id', 'product_variant_id', 'notification_type', 'sent_at'],
        'wishlist_notifications_cooldown_idx'
    );
});
```

---

## 3. Enums, DTOs y Dominio

### 3.1 Enum: `App\Enums\Wishlist\WishlistNotificationTypeEnum`

```php
namespace App\Enums\Wishlist;

use Filament\Support\Contracts\HasLabel;

enum WishlistNotificationTypeEnum: string implements HasLabel
{
    case PriceDrop = 'price_drop';
    case LowStock = 'low_stock';

    public function getLabel(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return __('wishlist.notification_types.'.$this->value);
    }
}
```

### 3.2 DTO: `App\DTOs\Wishlist\WishlistAlertResultDTO`

```php
namespace App\DTOs\Wishlist;

readonly class WishlistAlertResultDTO
{
    public function __construct(
        public int $priceDropsSent,
        public int $lowStockSent,
        public int $skippedCooldown,
        public int $skippedExcluded,
    ) {}

    public function totalSent(): int
    {
        return $this->priceDropsSent + $this->lowStockSent;
    }
}
```

---

## 4. Arquitectura de Dominio y Actions

### 4.1 Extensión de `App\Actions\Wishlist\ToggleWishlistAction`

Se extiende la firma de `ToggleWishlistAction` para capturar el snapshot de precio y moneda sin romper compatibilidad hacia atrás:

```php
public function __invoke(User $user, ProductVariant $variant, ?CurrencyEnum $currency = null): bool
```

1. Si el registro ya existe: lo elimina y retorna `false`.
2. Si no existe: resuelve `$currency ?? CurrentCurrency::get()`, obtiene `$variant->priceIn($currency)?->price` y persiste `price_when_added` y `currency_when_added => $currency->value`. Retorna `true`.

### 4.2 Action Principal: `App\Actions\Wishlist\SendWishlistAlertsAction`

Encapsula la evaluación y despacho de alertas:

```php
namespace App\Actions\Wishlist;

final class SendWishlistAlertsAction
{
    public function __invoke(): WishlistAlertResultDTO
    {
        // 1. Obtener usuarios activos con email verificado que tengan items en wishlist
        // 2. Iterar items agrupados por usuario aplicando límite de seguridad (max 3 por corrida)
        // 3. Evaluar invariantes (producto activo, variante activa, no preventa, stock > 0)
        // 4. Evaluar rebaja de precio:
        //    - current_price < price_when_added (en la moneda guardada o default)
        //    - O compare_at_price > current_price (oferta activa)
        // 5. Evaluar stock crítico:
        //    - stock >= 1 && stock <= config('ecommerce.wishlist_alerts.low_stock_threshold', 3)
        // 6. Verificar cooldown de 7 días contra WishlistNotificationLog
        // 7. Despachar Mailable encolado (Mail::to($user->email)->queue(...)) y registrar log
        // 8. Retornar DTO con métricas de ejecución
    }
}
```

---

## 5. Mailables, Vistas e i18n

### 5.1 Mailables en `app/Mail/Wishlist/`

- `App\Mail\Wishlist\WishlistPriceDropMail`:
  - Propiedades: `User $user`, `ProductVariant $variant`, `int $oldPrice`, `int $newPrice`, `CurrencyEnum $currency`.
  - Vista Markdown: `mail.wishlist.price-drop`.
  - Asunto: `__('wishlist.mail.price_drop_subject', ['product' => $productName])`.
- `App\Mail\Wishlist\WishlistLowStockMail`:
  - Propiedades: `User $user`, `ProductVariant $variant`, `int $stockRemaining`, `CurrencyEnum $currency`.
  - Vista Markdown: `mail.wishlist.low-stock`.
  - Asunto: `__('wishlist.mail.low_stock_subject', ['product' => $productName])`.

### 5.2 Plantillas Blade en `resources/views/mail/wishlist/`

- `price-drop.blade.php`:
  - Encabezado con logo Leen (`images/logos/leen-brown.png`).
  - Imagen del bolso, nombre y variante.
  - Precio tachado anterior vs. precio rebajado en negrita (`$currency->format($newPrice)`).
  - Botón CTA directo al producto o carrito (`<x-mail::button>`).
  - Pie de página con copy institucional.
- `low-stock.blade.php`:
  - Badge de advertencia *"¡Últimas unidades disponibles!"*.
  - Indicador de stock exacto restante (1 a 3 unidades).
  - Botón CTA directo para añadir al carrito.

### 5.3 Archivos de Traducción

- `lang/es/wishlist.php` y `lang/en/wishlist.php` conteniendo todas las claves de correo y enum:
  - `notification_types.price_drop`, `notification_types.low_stock`
  - `mail.price_drop_subject`, `mail.price_drop_heading`, `mail.price_drop_body`, `mail.price_drop_cta`
  - `mail.low_stock_subject`, `mail.low_stock_heading`, `mail.low_stock_body`, `mail.low_stock_cta`
  - `mail.footer_note`

---

## 6. Configuración y Scheduler

### 6.1 `config/ecommerce.php`

```php
'wishlist_alerts' => [
    'enabled' => (bool) env('ECOMMERCE_WISHLIST_ALERTS_ENABLED', true),
    'low_stock_threshold' => (int) env('ECOMMERCE_WISHLIST_LOW_STOCK_THRESHOLD', 3),
    'cooldown_days' => (int) env('ECOMMERCE_WISHLIST_COOLDOWN_DAYS', 7),
    'max_alerts_per_user' => (int) env('ECOMMERCE_WISHLIST_MAX_ALERTS_PER_USER', 3),
],
```

### 6.2 Comando Artisan `App\Console\Commands\SendWishlistAlertsCommand`

```php
protected $signature = 'app:send-wishlist-alerts';
protected $description = 'Evalúa y despacha alertas automáticas de rebaja de precio y stock crítico para wishlists';

public function handle(SendWishlistAlertsAction $action): int
{
    $result = $action();
    $this->info("Alertas procesadas: {$result->totalSent()} enviadas ({$result->priceDropsSent} rebajas, {$result->lowStockSent} stock bajo). Omitidas por cooldown: {$result->skippedCooldown}.");
    return self::SUCCESS;
}
```

### 6.3 Programación en `routes/console.php`

```php
Schedule::command('app:send-wishlist-alerts')
    ->dailyAt('10:00')
    ->withoutOverlapping();
```

---

## 7. Matriz Exhaustiva de Testing TDD

La implementación se ejecutará bajo **TDD estricto (Red-Green-Refactor)** cubriendo todas las capas:

| Suite / Archivo de Prueba | Escenarios de Prueba | Criterio EARS |
|---------------------------|----------------------|---------------|
| `tests/Feature/Domain/WishlistNotificationLogTest.php` | 1. Persistencia de log con cast a `WishlistNotificationTypeEnum`.<br>2. Relación correcta con `User` y `ProductVariant`.<br>3. `sent_at` automático en UTC/datetime. | **R3** |
| `tests/Feature/Wishlist/ToggleWishlistActionTest.php` | 1. Al guardar favorito sin moneda explícita, captura moneda y precio actual.<br>2. Al guardar favorito con moneda explícita (`CurrencyEnum::Eur`), persiste `price_when_added` y `currency_when_added = 'EUR'`.<br>3. Eliminar favorito sigue funcionando de forma idempotente. | **R5** |
| `tests/Feature/Mail/WishlistMailsTest.php` | 1. `WishlistPriceDropMail` renderiza asunto traducido, logo, imagen, precio anterior y precio formateado con `CurrencyEnum::format()`.<br>2. `WishlistLowStockMail` renderiza advertencia de stock, unidades restantes y botón CTA.<br>3. Soporte multi-idioma verificado en ES y EN. | **R4** |
| `tests/Feature/Wishlist/SendWishlistAlertsActionTest.php` | 1. Despacha `WishlistPriceDropMail` cuando el precio actual es menor al registrado en wishlist.<br>2. Despacha `WishlistPriceDropMail` para items legacy (sin precio guardado) si tienen `compare_at_price > price`.<br>3. Despacha `WishlistLowStockMail` cuando stock está entre 1 y 3 unidades.<br>4. Omite alertas si ya se envió notificación del mismo tipo en los últimos 7 días.<br>5. Permite alerta de stock si la previa fue de precio hace menos de 7 días (cooldown por tipo).<br>6. Excluye variantes con stock = 0 (agotadas).<br>7. Excluye productos en preventa (`is_preorder = true`).<br>8. Excluye variantes inactivas o productos despublicados.<br>9. Excluye usuarios sin email verificado (`email_verified_at = null`) o eliminados.<br>10. Limita a un máximo de 3 correos por usuario por corrida. | **R1, R2, R3, R6, R7, R8, R9** |
| `tests/Feature/Console/SendWishlistAlertsCommandTest.php` | 1. Comando `app:send-wishlist-alerts` ejecuta exitosamente y retorna código 0.<br>2. Imprime métricas de salida correctas basadas en el DTO.<br>3. Scheduler tiene el comando registrado con `dailyAt('10:00')` y `withoutOverlapping`. | **R1, R2, R3** |

