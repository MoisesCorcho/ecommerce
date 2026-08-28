# Diseño Técnico — F-04: Pop-up Promocional Administrable

> **Feature:** `16-promotional-popup` (F-04)  
> **Alcance:** Modelo, migración, Filament Resource, Componente Blade Modal + Alpine.js, soporte para cupones e i18n.

---

## 1. Arquitectura y Modelo de Datos

### 1.1 Migración: `create_promotional_popups_table`

```php
Schema::create('promotional_popups', function (Blueprint $table) {
    $table->id();
    $table->string('title_es', 255);
    $table->string('title_en', 255);
    $table->text('subtitle_es')->nullable();
    $table->text('subtitle_en')->nullable();
    $table->string('image_path', 2048)->nullable();
    $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
    $table->string('cta_text_es', 100)->nullable();
    $table->string('cta_text_en', 100)->nullable();
    $table->string('cta_url', 2048)->nullable();
    $table->unsignedSmallInteger('delay_seconds')->default(5);
    $table->boolean('is_active')->default(true)->index();
    $table->timestamp('starts_at')->nullable()->index();
    $table->timestamp('ends_at')->nullable()->index();
    $table->timestamps();
});
```

### 1.2 Modelo: `App\Models\PromotionalPopup`

- **Fillable:** `['title_es', 'title_en', 'subtitle_es', 'subtitle_en', 'image_path', 'coupon_id', 'cta_text_es', 'cta_text_en', 'cta_url', 'delay_seconds', 'is_active', 'starts_at', 'ends_at']`
- **Relations:** `belongsTo(Coupon::class)`
- **Scopes:**
  - `scopeActive(Builder $query): Builder`: Filtra `is_active = true`, `starts_at <= now()` (o nulo) y `ends_at >= now()` (o nulo).
- **Métodos auxiliares:**
  - `getLocalizedTitle(?string $locale = null): string`
  - `getLocalizedSubtitle(?string $locale = null): ?string`
  - `getLocalizedCtaText(?string $locale = null): ?string`

---

## 2. Panel de Administración (Filament v5)

### 2.1 Recurso: `App\Filament\Resources\PromotionalPopups\PromotionalPopupResource`

- **Grupo:** `Marketing` / `__('navigation.groups.marketing')`.
- **Formulario:**
  - `Section` "Contenido Visual y Textos":
    - `TextInput::make('title_es')->required()->maxLength(255)`
    - `TextInput::make('title_en')->required()->maxLength(255)`
    - `Textarea::make('subtitle_es')->rows(2)`
    - `Textarea::make('subtitle_en')->rows(2)`
    - `FileUpload::make('image_path')->image()->disk('public')->directory('popups')`
  - `Section` "Llamado a la Acción y Cupón":
    - `Select::make('coupon_id')->relationship('coupon', 'code')->searchable()->preload()->nullable()`
    - `TextInput::make('cta_text_es')->maxLength(100)`
    - `TextInput::make('cta_text_en')->maxLength(100)`
    - `TextInput::make('cta_url')->url()->maxLength(2048)`
  - `Section` "Comportamiento y Vigencia":
    - `TextInput::make('delay_seconds')->numeric()->default(5)->minValue(1)->maxValue(60)->required()`
    - `Toggle::make('is_active')->default(true)`
    - `DateTimePicker::make('starts_at')->nullable()`
    - `DateTimePicker::make('ends_at')->nullable()`

---

## 3. Storefront: Componente Blade y Alpine.js

### 3.1 Componente: `resources/views/components/promotional-popup.blade.php`

- **Alpine.js Logic:**
  - Estado: `show: false, copied: false, id: {{ $popup->id }}, delay: {{ $popup->delay_seconds }}`
  - `x-init`: Si no está en `localStorage` (`leen_popup_dismissed_` + id), dispara `setTimeout(() => show = true, delay * 1000)`.
  - Acción Copiar: `navigator.clipboard.writeText('{{ $coupon->code }}'); copied = true; setTimeout(() => copied = false, 2500)`.
  - Acción Cerrar: `show = false; localStorage.setItem('leen_popup_dismissed_' + id, Date.now())`.
- **Estilos:** Modal centrado con overlay oscuro difuminado, tipografía Chillax/Montserrat, colores de marca Leen (`bg-soft-sand`, acentos `text-soft-gold` e `intense-cocoa`), botón de cierre visible y responsive para móviles.

---

## 4. Estrategia de Testing (TDD)

1. **Dominio (`tests/Feature/Domain/PromotionalPopupTest.php`):**
   - Factory y persistencia.
   - Relación con modelo `Coupon`.
   - Scopes `active()` con vigencia de fechas y estado booleano.
2. **Filament (`tests/Feature/Filament/PromotionalPopupResourceTest.php`):**
   - Políticas de acceso de administrador.
   - Creación, actualización con cupón y validación de campos bilingües.
3. **Storefront (`tests/Feature/Storefront/PromotionalPopupTest.php`):**
   - Renderizado del componente cuando existe un pop-up activo.
   - Inclusión del código del cupón cuando está asociado.
   - No renderizado si está inactivo o fuera de fecha.
