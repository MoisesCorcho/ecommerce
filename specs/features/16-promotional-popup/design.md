# Diseño Técnico — F-04: Pop-up Promocional Administrable

> **Feature:** `16-promotional-popup` (F-04)  
> **Alcance:** Modelo, migración, Filament Resource (Filament v5 Schemas), Componente Blade Modal + Alpine.js, soporte para cupones, Spatie Translatable e i18n.

---

## 1. Arquitectura y Modelo de Datos

Siguiendo el precedente canónico establecido en [`specs/features/15-announcement-bar/`](../15-announcement-bar/) y [`app/Models/Announcement.php`](../../../app/Models/Announcement.php), la internacionalización se maneja mediante [`spatie/laravel-translatable`](../../../composer.json) y [`App\Enums\Localization\LocaleEnum`](../../../app/Enums/Localization/LocaleEnum.php), almacenando campos traducibles como JSON en la base de datos.

### 1.1 Migración: `create_promotional_popups_table`

```php
Schema::create('promotional_popups', function (Blueprint $table): void {
    $table->id();
    $table->json('title');
    $table->json('subtitle')->nullable();
    $table->string('image_path', 2048)->nullable();
    $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
    $table->json('cta_text')->nullable();
    $table->string('cta_url', 2048)->nullable();
    $table->unsignedSmallInteger('delay_seconds')->default(5);
    $table->boolean('is_active')->default(true)->index();
    $table->unsignedInteger('sort_order')->default(0)->index();
    $table->timestamp('starts_at')->nullable()->index();
    $table->timestamp('ends_at')->nullable()->index();
    $table->timestamps();
});
```

### 1.2 Modelo: `App\Models\PromotionalPopup`

- **Traits:** `HasFactory`, `Spatie\Translatable\HasTranslations`
- **Fillable:** `['title', 'subtitle', 'image_path', 'coupon_id', 'cta_text', 'cta_url', 'delay_seconds', 'is_active', 'sort_order', 'starts_at', 'ends_at']`
- **Translatable:** `['title', 'subtitle', 'cta_text']`
- **Casts:**
  - `'is_active' => 'boolean'`
  - `'sort_order' => 'integer'`
  - `'delay_seconds' => 'integer'`
  - `'starts_at' => 'datetime'`
  - `'ends_at' => 'datetime'`
- **Relations:** `belongsTo(Coupon::class)`
- **Scopes:**
  - `scopeActive(Builder $query): Builder`: Filtra `is_active = true`, `starts_at <= now()` (o nulo) y `ends_at >= now()` (o nulo).
  - `scopeOrdered(Builder $query): Builder`: Ordena por `sort_order ASC, id DESC`.
- **Métodos auxiliares de localización:**
  - `getLocalizedTitle(?string $locale = null): string`: Retorna la traducción en `$locale` (o fallback a `es`).
  - `getLocalizedSubtitle(?string $locale = null): ?string`: Retorna la traducción en `$locale` (o fallback a `es`).
  - `getLocalizedCtaText(?string $locale = null): ?string`: Retorna la traducción en `$locale` (o fallback a `es`).
  - `hasValidCoupon(): bool`: Verifica si `coupon` existe, `is_active = true` y no está expirado (`expires_at === null || expires_at >= now()`).

---

## 2. Panel de Administración (Filament v5)

Descompuesto bajo la convención `app/Filament/Resources/PromotionalPopups/`:

```text
app/Filament/Resources/PromotionalPopups/
├── PromotionalPopupResource.php
├── Schemas/
│   ├── PromotionalPopupForm.php
│   └── PromotionalPopupsTable.php
└── Pages/
    ├── ListPromotionalPopups.php
    ├── CreatePromotionalPopup.php
    └── EditPromotionalPopup.php
```

### 2.1 Formulario: `PromotionalPopupForm`
- **Pestañas dinámicas de traducción:** `Tabs::make('Translations')` iterando sobre `LocaleEnum::cases()` para `title`, `subtitle` y `cta_text`.
- **Llamado a la acción y Cupón:**
  - `FileUpload::make('image_path')->image()->disk('public')->directory('popups')->nullable()`
  - `Select::make('coupon_id')->relationship('coupon', 'code')->searchable()->preload()->nullable()`
  - `TextInput::make('cta_url')->url()->maxLength(2048)->nullable()`
- **Comportamiento y Vigencia:**
  - `TextInput::make('delay_seconds')->numeric()->default(5)->minValue(1)->maxValue(60)->required()`
  - `TextInput::make('sort_order')->numeric()->default(0)->required()`
  - `Toggle::make('is_active')->default(true)`
  - `DateTimePicker::make('starts_at')->nullable()`
  - `DateTimePicker::make('ends_at')->nullable()->afterOrEqual('starts_at')`

### 2.2 Tabla: `PromotionalPopupsTable`
- Columnas: `ImageColumn` (`image_path`), `TextColumn` (`title` en idioma actual), `TextColumn` (`coupon.code`), `ToggleColumn` / `IconColumn` (`is_active`), `TextColumn` (`sort_order`), `TextColumn` (`starts_at`/`ends_at`).
- Filtros: `TernaryFilter` para `is_active`, filtro de vigencia por fechas.

---

## 3. Storefront: Componente Blade y Alpine.js

### 3.1 Componente: `resources/views/components/promotional-popup.blade.php`

- **Consulta optimizada (Eager Loading):**
  `$popup = App\Models\PromotionalPopup::active()->with('coupon')->ordered()->first();`
- **Alpine.js Logic:**
  - Estado: `show: false, copied: false, id: {{ $popup->id }}, delay: {{ $popup->delay_seconds }}`
  - `x-init`:
    ```javascript
    const dismissedKey = 'leen_popup_dismissed_' + id;
    const dismissedAt = localStorage.getItem(dismissedKey);
    const oneDayMs = 24 * 60 * 60 * 1000;
    if (!dismissedAt || (Date.now() - parseInt(dismissedAt, 10)) > oneDayMs) {
        setTimeout(() => { show = true; }, delay * 1000);
    }
    ```
  - Copiar Cupón: `navigator.clipboard.writeText('{{ $popup->coupon?->code }}'); copied = true; setTimeout(() => copied = false, 2500)`
  - Cerrar Modal: `show = false; localStorage.setItem('leen_popup_dismissed_' + id, Date.now().toString())`
- **Estilos:** Modal centrado con backdrop blur (`backdrop-blur-sm bg-black/50`), tarjeta `bg-soft-sand`, acentos `text-soft-gold` e `intense-cocoa`, botón de cierre `✕` accesible, badge de descuento condicional (`hasValidCoupon()`).

---

## 4. Estrategia de Testing (TDD)

1. **Dominio (`tests/Feature/Domain/PromotionalPopupTest.php`):**
   - Factory, persistencia y casts.
   - Traducciones Spatie (`HasTranslations`) con `LocaleEnum` y fallbacks.
   - Relación con `Coupon` y método `hasValidCoupon()`.
   - Scopes `active()` (fechas y switch) y `ordered()` (`sort_order ASC, id DESC`).
2. **Filament (`tests/Feature/Filament/PromotionalPopupResourceTest.php`):**
   - Políticas de acceso (403 para no-admins, 200 para admins).
   - Creación y edición con pestañas de traducción, subida de imagen y asociación de cupón.
3. **Storefront (`tests/Feature/Storefront/PromotionalPopupTest.php`):**
   - Renderizado del componente cuando existe un pop-up activo con eager loading.
   - Inclusión del cupón y badge cuando está vigente.
   - Ocultación del cupón si está inactivo/expirado.
   - Fallback bilingüe en vista según `app()->getLocale()`.

