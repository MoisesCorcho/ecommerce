# Diseño Técnico — F-07: Barra de Anuncios Administrable

> **Feature:** `15-announcement-bar` (F-07)  
> **Alcance:** Modelo, migración, Filament Resource, Componente Blade Storefront, Alpine.js y Tests.

---

## 1. Arquitectura y Modelo de Datos

### 1.1 Migración: `create_announcements_table`

```php
Schema::create('announcements', function (Blueprint $table) {
    $table->id();
    $table->string('text_es', 255);
    $table->string('text_en', 255);
    $table->string('url', 2048)->nullable();
    $table->boolean('is_active')->default(true)->index();
    $table->unsignedInteger('sort_order')->default(0)->index();
    $table->timestamp('starts_at')->nullable()->index();
    $table->timestamp('ends_at')->nullable()->index();
    $table->timestamps();
});
```

### 1.2 Modelo: `App\Models\Announcement`

- **Fillable:** `['text_es', 'text_en', 'url', 'is_active', 'sort_order', 'starts_at', 'ends_at']`
- **Casts:**
  - `is_active` => `boolean`
  - `sort_order` => `integer`
  - `starts_at` => `datetime`
  - `ends_at` => `datetime`
- **Scopes:**
  - `scopeActive(Builder $query): Builder`: Filtra `is_active = true`, `starts_at <= now()` (o nulo) y `ends_at >= now()` (o nulo).
  - `scopeOrdered(Builder $query): Builder`: Ordena por `sort_order asc`, `id desc`.
- **Métodos auxiliares:**
  - `getLocalizedText(?string $locale = null): string`: Retorna el texto según el locale activo (`es` o `en`), con fallback a `text_es`.

---

## 2. Panel de Administración (Filament v5)

### 2.1 Recurso: `App\Filament\Resources\Announcements\AnnouncementResource`

- **Grupo de Navegación:** `Marketing` / `__('navigation.groups.marketing')` (o `__('announcements.navigation.group')`).
- **Formulario:**
  - `Section` "Contenido del Anuncio":
    - `TextInput::make('text_es')->label(__('announcements.fields.text_es'))->required()->maxLength(255)`
    - `TextInput::make('text_en')->label(__('announcements.fields.text_en'))->required()->maxLength(255)`
    - `TextInput::make('url')->label(__('announcements.fields.url'))->url()->nullable()->maxLength(2048)`
  - `Section` "Visibilidad y Programación":
    - `Toggle::make('is_active')->label(__('announcements.fields.is_active'))->default(true)`
    - `TextInput::make('sort_order')->label(__('announcements.fields.sort_order'))->numeric()->default(0)->required()`
    - `DateTimePicker::make('starts_at')->label(__('announcements.fields.starts_at'))->nullable()`
    - `DateTimePicker::make('ends_at')->label(__('announcements.fields.ends_at'))->nullable()`
- **Tabla:**
  - `TextColumn::make('text_es')->searchable()->limit(50)`
  - `TextColumn::make('text_en')->searchable()->limit(50)`
  - `IconColumn::make('is_active')->boolean()`
  - `TextColumn::make('sort_order')->sortable()`
  - `TextColumn::make('starts_at')->dateTime()->sortable()`
  - `TextColumn::make('ends_at')->dateTime()->sortable()`
  - `Actions`: `EditAction`, `DeleteAction`.

---

## 3. Storefront: Componente Blade y Alpine.js

### 3.1 Componente: `resources/views/components/announcement-bar.blade.php`

- **Consulta:** Obtiene el anuncio activo con mayor prioridad:
  `$announcement = \App\Models\Announcement::active()->ordered()->first();` (o inyectado a través de View Composer / Service / Componente Blade).
- **Comportamiento Cliente (Alpine.js):**
  - `x-data="{ dismissed: false, id: {{ $announcement->id }} }"`
  - `x-init="dismissed = localStorage.getItem('leen_announcement_dismissed_' + id) === '1'"`
  - `x-show="!dismissed"`
  - Botón cerrar: `@click="dismissed = true; localStorage.setItem('leen_announcement_dismissed_' + id, '1')"`
- **Estilos:**
  - Fondo de marca Leen: `bg-intense-cocoa text-silk-cream` con acento `text-soft-gold` si hay enlace.
  - Centrado de texto, botón "X" a la derecha, responsive y accesible (`aria-label="{{ __('announcements.close') }}"`).

---

## 4. Internacionalización (i18n)

Archivos:
- `lang/es/announcements.php`
- `lang/en/announcements.php`

Claves:
- `title`, `singular`, `plural`, `fields.text_es`, `fields.text_en`, `fields.url`, `fields.is_active`, `fields.sort_order`, `fields.starts_at`, `fields.ends_at`, `close`.

---

## 5. Estrategia de Testing (TDD)

1. **Unit / Feature Tests de Dominio (`tests/Feature/Domain/AnnouncementTest.php`):**
   - Creación de anuncio con factory.
   - Scope `active()` excluye inactivos (`is_active = false`).
   - Scope `active()` excluye anuncios con `starts_at` en el futuro.
   - Scope `active()` excluye anuncios con `ends_at` en el pasado.
   - Scope `ordered()` ordena por `sort_order` ascendente y luego `id` descendente.
   - Método `getLocalizedText()` retorna el texto según el locale configurado.
2. **Filament Admin Tests (`tests/Feature/Filament/AnnouncementResourceTest.php`):**
   - Admin autorizado puede ver la tabla y crear/editar/eliminar anuncios.
   - Usuario no admin recibe 403 Forbidden.
   - Validación de campos requeridos (`text_es`, `text_en`).
3. **Storefront View Tests (`tests/Feature/Storefront/AnnouncementBarTest.php`):**
   - Renderizado del anuncio en la página principal cuando está activo.
   - No renderiza nada si no hay anuncios activos o vigentes.
   - Renderiza el enlace correspondiente si `url` está presente.
   - Renderiza `text_en` cuando el locale es `en`, y `text_es` cuando es `es`.
