# F01 — Catálogo · Diseño técnico (admin Filament)

> **ID:** F01 · **Slug:** `01-catalog`  
> **Requirements:** [`requirements.md`](requirements.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md)  
> **Producto:** [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **Dominio:** `app/Models/{Category,Product,ProductVariant,ProductVariantPrice,ProductImage}`, `app/Enums/Commerce/CurrencyEnum.php`  
> **Layout código:** tipo primero, área después (`app/Actions/{Area}`, `app/DTOs/{Area}`, `app/Enums/{Area}`, …) — ver project-conventions  
> **Stack en alcance:** Filament v5 panel `admin`, PHPUnit, Sail  
> **Fuera de alcance F01:** storefront Livewire / UI de marca (R11–R13, R17 diferidos)

Este documento describe el **CÓMO** del admin. El **QUÉ** está en `requirements.md`.

---

## 1. Alcance técnico

| Incluye (F01) | Excluye / diferido |
|---------------|-------------------|
| Config ecommerce + gate de panel | Spatie Permission / roles |
| Actions + DTOs de catálogo | Carrito, stock reservado, cupones |
| Scopes de publicación en models (prep dominio) | **Storefront Livewire list/detail (diferido)** |
| Filament Resources Category + Product | Media Library de terceros |
| Feature tests PHPUnit **admin + dominio** | Selector multi-moneda UI; tests de UI pública como DoD |

Sin migrations de esquema nuevas salvo gap real documentado.

---

## 2. Configuración

### `config/ecommerce.php`

```php
return [
    'admin_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ADMIN_EMAILS', '')),
    ))),
    'default_currency' => env('ECOMMERCE_DEFAULT_CURRENCY', 'COP'),
];
```

- `admin_emails` → R14, R18.
- `default_currency` → dominio/scopes (F03+ y storefront futuro); no UI de tienda en F01.
- `.env.example`: `ADMIN_EMAILS`, `ECOMMERCE_DEFAULT_CURRENCY`.

### User + panel

- `User` implementa `FilamentUser`; `canAccessPanel` → email ∈ `config('ecommerce.admin_emails')`.
- Panel: `AdminPanelProvider` (`/admin`).
- Spatie puede reemplazar el gate después; no instalar en F01.

### Storage (admin uploads)

- Disco `public`, directory `products/`, `FileUpload` con `visibility('public')`.
- `ProductImage.path` = ruta relativa.
- `storage:link` en setup (nota operativa).

---

## 3. Dominio: Actions, DTOs, scopes

Actions invokables; DTOs readonly; sin Services 1:1.

### Categorías

| Clase | Rol |
|-------|-----|
| `CreateCategoryAction` | Alta; slug auto si vacío |
| `UpdateCategoryAction` | Update; slug editable |
| `DeleteCategoryAction` | Delete; productos quedan `category_id` null |

### Productos

| Clase | Rol |
|-------|-----|
| `CreateProductAction` | Producto + variantes + precios + imágenes en `DB::transaction` |
| `UpdateProductAction` | Igual en update |

DTOs: `UpsertProductDTO`, `UpsertProductVariantDTO`, `UpsertProductVariantPriceDTO`, `UpsertProductImageDTO` (si aplica).

**Publicación (R10/R15):** si `is_active`, exige ≥1 variante activa con ≥1 precio; si no → `ProductCannotBePublishedException` (mensaje claro en Filament).

**Primaria (R9):** una sola `is_primary` por producto en la misma transacción.

### Scopes (dominio; útiles para F03+ y storefront diferido)

| Model | Scope |
|-------|--------|
| `Product` | `scopeActive` |
| `Product` | `scopePublishedForStorefront(Builder, CurrencyEnum)` |
| `ProductVariant` | `scopeActive`, helpers de precio por moneda |

No son UI. Pueden testearse a nivel Action/model si se desea; **no** exigen rutas Livewire en F01.

---

## 4. Admin UI — Filament v5

### `CategoryResource`

- List / Create / Edit.
- Campos: name, slug (unique ignoreRecord), parent, sort_order.
- Create/Edit/Delete → Category Actions.

### `ProductResource`

- Datos producto + Repeaters (preferido) de variantes (precios anidados) e imágenes.
- `FileUpload`: disk public, directory `products`, visibility public.
- Create/Edit → `CreateProductAction` / `UpdateProductAction`.
- Navigation group: **Catálogo**.

Namespaces Filament v5: Forms `Filament\Forms\Components\*`, Schemas `Filament\Schemas\Components\*`, Actions `Filament\Actions\*`.

AuthZ: solo `canAccessPanel` (CRUD completo de catálogo para cualquier admin de la lista).

---

## 5. Storefront — **diferido** (fuera de F01)

No es entregable de F01. Cuando exista manual de marca:

- Livewire MFC list + detail.
- Rutas públicas (p. ej. `/products`, `/products/{slug}`).
- Criterios R11–R13, R17.
- Tests de storefront como DoD de **esa** slice.

Si el repo ya tiene componentes/rutas de catálogo público, se consideran **fuera del contrato F01** (adelanto o a rehacer con marca). No invertir más en ellos dentro de F01.

---

## 6. Flujo F01

```text
Admin (email ∈ admin_emails)
  → Filament CategoryResource / ProductResource
    → validate (borde)
    → Create/Update*Action (+ DB::transaction en producto)
      → Category | Product + Variants + Prices + Images
```

---

## 7. Tests (PHPUnit) — DoD F01

| Área | Escenarios |
|------|------------|
| AuthZ panel | Email en lista accede; otro email / guest no |
| Category admin | Create / update / delete; slug auto / unique; name required |
| Product admin | Create con variante+precio; update grafo; publish invariant; primaria; SKU unique; montos int |

**No forman DoD F01:** tests de listado/detalle Livewire (pueden existir en el repo sin cerrar el alcance).

```bash
vendor/bin/sail artisan test --compact tests/Feature/Catalog
# filtrar o ignorar Storefront* según política del equipo al correr “DoD F01”
vendor/bin/sail bin pint --dirty --format agent
```

---

## 8. Archivos en alcance F01

```text
config/ecommerce.php
app/Models/User.php                    # FilamentUser
app/Actions/Categories/Create|Update|DeleteCategoryAction.php
app/Actions/Products/Create|UpdateProductAction.php
app/DTOs/Products/UpsertProduct*.php
app/Enums/Commerce/CurrencyEnum.php
app/Exceptions/Products/ProductCannotBePublishedException.php
app/Filament/Resources/Categories/...
app/Filament/Resources/Products/...
tests/Feature/Catalog/Admin* Category* Product* Publish*
.env.example
```

**Fuera de DoD F01 (opcional en repo):** Livewire storefront, `StorefrontCatalogTest`, layout de tienda.

---

## 9. Riesgos y follow-ups

| Riesgo | Mitigación |
|--------|------------|
| `ADMIN_EMAILS` vacío | Documentar; seeder/dev admin |
| Producto activo solo con precio ≠ moneda default | Intencional para storefront futuro; opcional help text en Filament |
| Código Livewire “huérfano” confunde | Specs + roadmap marcan diferido; no expandir sin slice de marca |
| Spatie más adelante | Reemplazar gate; Actions no dependen de emails hardcodeados |

### Follow-ups

- Slice **Storefront catálogo** (manual de marca): R11–R13, R17.
- Spatie Permission / policies.
- Soft-delete UX productos en admin.

---

## 10. Mapa design → requirements

| Design | Requirements |
|--------|--------------|
| CategoryResource + Actions | R1–R3, R6, R7, R19 |
| ProductResource + Actions/DTOs | R4, R5, R8–R10, R15, R16, R19, R20 |
| Publish invariant | R10, R15 |
| Primary image | R9 |
| Integer money + CurrencyEnum | R16 (+ R13 cuando storefront) |
| `canAccessPanel` + config | R14, R18 |
| Storefront Livewire | **Diferido** R11–R13, R17 |
