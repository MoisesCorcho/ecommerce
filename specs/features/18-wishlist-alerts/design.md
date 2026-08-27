# Diseño Técnico — F-03: Notificaciones Automáticas de Wishlist

> **Feature:** `18-wishlist-alerts` (F-03)  
> **Alcance:** Migración logs, comando Artisan `app:send-wishlist-alerts`, Mailables, plantillas Blade de email y Scheduler.

---

## 1. Modelo de Datos y Migraciones

### 1.1 Migración: `add_price_when_added_to_wishlists_table`

```php
Schema::table('wishlists', function (Blueprint $table) {
    $table->unsignedInteger('price_when_added')->nullable()->after('product_variant_id');
});
```

### 1.2 Migración: `create_wishlist_notification_logs_table`

```php
Schema::create('wishlist_notification_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
    $table->string('notification_type', 32); // 'price_drop' | 'low_stock'
    $table->timestamp('sent_at')->useCurrent()->index();
    $table->timestamps();

    $table->index(['user_id', 'product_variant_id', 'notification_type'], 'user_variant_type_idx');
});
```

---

## 2. Lógica de Negocio y Comando Artisan

### 2.1 Comando: `App\Console\Commands\SendWishlistAlertsCommand`

- **Firma:** `app:send-wishlist-alerts`
- **Flujo:**
  1. Itera sobre los usuarios con ítems en su wishlist activa.
  2. Para cada ítem, compara el precio actual de la variante (`$currentPrice`) con:
     - El precio guardado (`$wishlist->price_when_added`).
     - O si la variante tiene oferta activa (`compare_at_price > price`).
  3. Verifica en `WishlistNotificationLog` si ya se envió una notificación para ese `user_id` + `product_variant_id` en los últimos 7 días.
  4. Si `$currentPrice < $wishlist->price_when_added` o `hasDiscount()`: despacha `WishlistPriceDropMail` vía `Mail::to($user->email)->queue(...)`.
  5. Si el stock está entre 1 y 3 unidades (`stock > 0 && stock <= 3`): despacha `WishlistLowStockMail`.
  6. Registra el log de envío en base de datos.

### 2.2 Mailables y Vistas de Correo

- `App\Mail\WishlistPriceDropMail` $\rightarrow$ `resources/views/emails/wishlist-price-drop.blade.php`
- `App\Mail\WishlistLowStockMail` $\rightarrow$ `resources/views/emails/wishlist-low-stock.blade.php`
- Componentes visuales de correo: logo Leen, imagen de variante, botón CTA directo a tienda.

---

## 3. Programación en Scheduler (`routes/console.php`)

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:send-wishlist-alerts')
    ->dailyAt('10:00')
    ->withoutOverlapping();
```

---

## 4. Estrategia de Testing (TDD)

1. **Feature Tests de Alertas (`tests/Feature/Console/SendWishlistAlertsCommandTest.php`):**
   - Disparo de correo de rebaja de precio cuando el precio baja.
   - Disparo de correo de stock bajo cuando el inventario es $\le 3$.
   - Supresión de envío si ya se envió en los últimos 7 días (anti-spam).
   - No envío si el stock es 0 (agotado) o si el usuario no tiene email verificado.
   - Registro correcto en `wishlist_notification_logs`.
