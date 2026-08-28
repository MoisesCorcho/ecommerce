# Diseño Técnico — F-07: Barra de Anuncios Administrable

> **Feature:** `15-announcement-bar` (F-07)  
> **Alcance:** Modelo, migración, Spatie Translatable, Filament Resource (Schemas), Componente Blade Storefront, Alpine.js y Tests.

---

## 1. Dependencias y Configuración Global

### 1.1 Paquetes Requeridos
* `spatie/laravel-translatable:^6.0`
* `filament/spatie-laravel-translatable-plugin:^5.0`

### 1.2 Registro en PanelProvider (`app/Providers/Filament/AdminPanelProvider.php`)
```php
use Filament\SpatieLaravelTranslatablePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(
            SpatieLaravelTranslatablePlugin::make()
                ->defaultLocales(['es', 'en']),
        );
}
```

---

## 2. Arquitectura y Modelo de Datos

### 2.1 Migración: `create_announcements_table`

```php
Schema::create('announcements', function (Blueprint $table) {
    $table->id();
    $table->json('text'); // {"es": "Texto en español", "en": "Text in english"}
    $table->string('url', 2048)->nullable();
    $table->boolean('is_active')->default(true)->index();
    $table->unsignedInteger('sort_order')->default(0)->index();
    $table->timestamp('starts_at')->nullable()->index();
    $table->timestamp('ends_at')->nullable()->index();
    $table->timestamps();
});
```

### 2.2 Modelo: `App\Models\Announcement`

- **Traits:** `HasFactory`, `Spatie\Translatable\HasTranslations`
- **Fillable:** `#[Fillable(['text', 'url', 'is_active', 'sort_order', 'starts_at', 'ends_at'])]`
- **Translatable:** `public array $translatable = ['text'];`
- **Casts:**
  - `is_active` => `boolean`
  - `sort_order` => `integer`
  - `starts_at` => `datetime`
  - `ends_at` => `datetime`
- **Scopes:**
  - `scopeActive(Builder $query): Builder`: Filtra `is_active = true`, `starts_at <= now()` (o nulo) y `ends_at >= now()` (o nulo).
  - `scopeOrdered(Builder $query): Builder`: Ordena por `sort_order asc`, `id desc`.
- **Métodos auxiliares:**
  - `getLocalizedText(?string $locale = null): string`: Retorna `$this->getTranslation('text', $locale ?? app()->getLocale(), useFallbackLocale: true)`.

---

## 3. Panel de Administración (Filament v5)

### 3.1 Estructura del Recurso
```text
app/Filament/Resources/Announcements/
  AnnouncementResource.php
  Pages/
    ListAnnouncements.php
    CreateAnnouncement.php
    EditAnnouncement.php
  Schemas/
    AnnouncementForm.php
    AnnouncementsTable.php
```

### 3.2 `AnnouncementResource`
- **Grupo de Navegación:** `Marketing` / `__('navigation.groups.marketing')` (o `__('announcements.navigation.group')`).
- **Icono:** `Heroicon::OutlinedMegaphone` / `Heroicon::OutlinedSpeakerWave`.

### 3.3 Páginas con Trait `Translatable`
- `ListAnnouncements`, `CreateAnnouncement`, `EditAnnouncement` implementan sus respectivos concerns:
  - `use ListRecords\Concerns\Translatable;`
  - `use CreateRecord\Concerns\Translatable;`
  - `use EditRecord\Concerns\Translatable;`
- En las cabeceras se agrega la acción `Actions\LocaleSwitcher::make()`.

### 3.4 Formulario (`Schemas/AnnouncementForm.php`)
- `Section` "Contenido del Anuncio":
  - `TextInput::make('text')->label(__('announcements.fields.text'))->required()->maxLength(255)`
  - `TextInput::make('url')->label(__('announcements.fields.url'))->nullable()->maxLength(2048)`
- `Section` "Visibilidad y Programación":
  - `Toggle::make('is_active')->label(__('announcements.fields.is_active'))->default(true)`
  - `TextInput::make('sort_order')->label(__('announcements.fields.sort_order'))->numeric()->default(0)->required()`
  - `DateTimePicker::make('starts_at')->label(__('announcements.fields.starts_at'))->nullable()`
  - `DateTimePicker::make('ends_at')->label(__('announcements.fields.ends_at'))->nullable()->afterOrEqual('starts_at')`

### 3.5 Tabla (`Schemas/AnnouncementsTable.php`)
- `TextColumn::make('text')->label(__('announcements.fields.text'))->searchable()->limit(60)`
- `ToggleColumn::make('is_active')->label(__('announcements.fields.is_active'))`
- `TextColumn::make('sort_order')->label(__('announcements.fields.sort_order'))->sortable()`
- `TextColumn::make('starts_at')->label(__('announcements.fields.starts_at'))->dateTime()->sortable()`
- `TextColumn::make('ends_at')->label(__('announcements.fields.ends_at'))->dateTime()->sortable()`
- `Actions`: `EditAction`, `DeleteAction`.

---

## 4. Storefront: Componente Blade y Alpine.js

### 4.1 Componente: `resources/views/components/announcement-bar.blade.php`

- **Consulta:**
  ```php
  $announcement = \App\Models\Announcement::active()->ordered()->first();
  ```
- **Comportamiento Cliente (Alpine.js):**
  - `x-data="{ dismissed: false, id: {{ $announcement->id }} }"`
  - `x-init="dismissed = localStorage.getItem('leen_announcement_dismissed_' + id) === '1'"`
  - `x-show="!dismissed"`
  - `x-cloak` para evitar parpadeo.
  - Botón cerrar: `@click="dismissed = true; localStorage.setItem('leen_announcement_dismissed_' + id, '1')"`
- **Enlace:**
  - Si `url` empieza por `http://` o `https://`, se incluye `target="_blank" rel="noopener noreferrer"`.
  - Si `url` es relativa, navega normalmente.
- **Estilos:**
  - Fondo: `bg-intense-cocoa text-silk-cream` con texto centrado, botón "X" a la derecha, accesible con `aria-label="{{ __('announcements.close') }}"`.

### 4.2 Ubicación en `resources/views/layouts/storefront.blade.php`
- Se incluye `<x-announcement-bar />` inmediatamente antes del `<header class="sticky top-0 ...">` (o como primer elemento dentro de él).

---

## 5. Internacionalización (i18n)

Archivos:
- `lang/es/announcements.php`
- `lang/en/announcements.php`

Claves mínimas:
- `navigation.group`, `navigation.label`, `model.label`, `model.plural`, `fields.text`, `fields.url`, `fields.is_active`, `fields.sort_order`, `fields.starts_at`, `fields.ends_at`, `close`.

---

## 6. Estrategia de Testing (TDD)

1. **Unit / Feature Tests de Dominio (`tests/Feature/Domain/AnnouncementTest.php`):**
   - Creación de anuncio con factory y traducciones bilingües.
   - Scope `active()` excluye inactivos (`is_active = false`).
   - Scope `active()` excluye anuncios con `starts_at` futuro o `ends_at` pasado.
   - Scope `ordered()` ordena por `sort_order` asc e `id` desc.
   - Método `getLocalizedText()` retorna el texto según el locale configurado y aplica fallback a `es`.
2. **Filament Admin Tests (`tests/Feature/Filament/AnnouncementResourceTest.php`):**
   - Admin autorizado puede acceder a la tabla, crear con locale, editar y alternar estado activo.
   - Usuario sin permisos recibe 403 Forbidden.
   - Validación de campo requerido `text` y regla `ends_at >= starts_at`.
3. **Storefront View Tests (`tests/Feature/Storefront/AnnouncementBarTest.php`):**
   - Renderizado del anuncio activo en el layout storefront.
   - No renderiza nada si no hay anuncios activos o están vencidos.
   - Renderizado en inglés cuando `app()->getLocale() == 'en'` y en español cuando es `'es'`.
   - Renderizado de enlace externo con `target="_blank"` y enlace interno normal.

