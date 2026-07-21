# F02 — Cuentas y direcciones · Diseño técnico (admin Filament)

> **ID:** F02 · **Slug:** `02-accounts-addresses`  
> **Requirements:** [`requirements.md`](requirements.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md)  
> **Producto:** [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)  
> **Dominio:** `app/Models/User.php`, `app/Models/Address.php`, migrations `users` / `addresses`  
> **Layout código:** tipo primero, área después (`app/Actions/{Area}`, `app/DTOs/{Area}`, …)  
> **Stack en alcance:** Filament v5 panel `admin`, PHPUnit, Sail  
> **Fuera de alcance F02:** Livewire storefront de cuentas (login, registro, perfil, libreta pública)

Este documento describe el **CÓMO** del admin. El **QUÉ** está en `requirements.md`.

---

## 1. Alcance técnico

| Incluye (F02) | Excluye |
|---------------|---------|
| Actions + DTOs Users / Addresses | Livewire storefront de cuenta |
| Filament `UserResource` + RelationManager de direcciones | Spatie Permission |
| Invariante `is_default` por usuario | Checkout / pagos / carrito |
| Feature tests PHPUnit admin + dominio | Schema nuevo (salvo gap real) |
| Reutilizar gate `admin_emails` (F01) | Hard delete obligatorio de users |

Sin migrations de esquema nuevas salvo gap real documentado en tasks.

---

## 2. Configuración y acceso

- Reutilizar `config/ecommerce.php` → `admin_emails` (F01).
- `User::canAccessPanel()` ya implementado en F01; no reimplementar salvo bug.
- Panel: `AdminPanelProvider` (`/admin`).
- AuthZ de recursos: cualquier admin de la lista tiene CRUD completo de users/addresses (sin policies granulares en F02).

---

## 3. Dominio: Actions y DTOs

Actions invokables; DTOs readonly; sin Services 1:1.

### Área `Users`

| Clase | Rol |
|-------|-----|
| `App\Actions\Users\CreateUserAction` | Alta de usuario; hashea password vía cast del model |
| `App\Actions\Users\UpdateUserAction` | Update; password solo si viene no vacío en el DTO |
| `App\Actions\Users\DeleteUserAction` | Soft delete (`$user->delete()`) |

DTO sugerido:

| Clase | Campos |
|-------|--------|
| `App\DTOs\Users\UpsertUserDTO` | `name`, `email`, `phone` (`?string`), `password` (`?string` — null en update = no cambiar) |

Validación de formato/unicidad: preferible en el borde Filament; Actions asumen payload ya validado o revalidan reglas de dominio mínimas (email único, password required en create).

### Área `Addresses`

| Clase | Rol |
|-------|-----|
| `App\Actions\Addresses\CreateAddressAction` | Alta con `user_id`; aplica invariante default |
| `App\Actions\Addresses\UpdateAddressAction` | Update; aplica invariante default |
| `App\Actions\Addresses\DeleteAddressAction` | Delete de la dirección |

DTO sugerido:

| Clase | Campos |
|-------|--------|
| `App\DTOs\Addresses\UpsertAddressDTO` | `userId`, `label?`, `fullName`, `phone`, `addressLine1`, `addressLine2?`, `city`, `state`, `country`, `postalCode?`, `isDefault` |

**Invariante default (R8):** dentro de `DB::transaction` al crear/actualizar con `isDefault = true`, poner `is_default = false` en las demás direcciones del mismo `user_id` y luego guardar la actual. Si `isDefault = false` y era la única default, se permite (puede quedar el usuario sin default).

No hace falta Service separado: la invariante vive en las Actions de Address.

### Models

- `User`: ya tiene `fillable`, `SoftDeletes`, relación `addresses()`.
- `Address`: ya tiene `fillable`, cast `is_default` boolean, `user()`.
- No cambiar el grafo salvo necesidad real descubierta en implementación.

---

## 4. Admin UI — Filament v5

### `UserResource`

- Ubicación: `app/Filament/Resources/Users/` (mismo estilo que Categories/Products).
- Páginas: List / Create / Edit.
- Formulario:
  - `name` (required)
  - `email` (required, email, unique ignoreRecord)
  - `phone` (nullable)
  - `password` (required en create; dehydrate solo si filled en edit; `password` / `passwordConfirmation` si el proyecto ya usa ese patrón; no mostrar valor actual)
- Tabla: name, email, phone, created_at; búsqueda name/email.
- Delete → soft delete vía `DeleteUserAction` o delete nativo + Action según cableado del resource.
- Navigation group: **Cuentas** (o **Clientes**). Sort estable respecto a **Catálogo**.

### RelationManager `AddressesRelationManager`

- En `EditUser` (y opcionalmente View si se agrega): gestión HasMany `addresses`.
- Campos: label, full_name, phone, address_line_1, address_line_2, city, state, country (max 2, default `CO`), postal_code, is_default (toggle).
- Create / Edit / Delete cableados a Address Actions (no lógica de invariante solo en el form).
- Alternativa aceptable: `AddressResource` global con filtro por user **además** del RelationManager; **mínimo requerido:** RelationManager en User (ownership claro, R5–R7).

Namespaces Filament v5: Forms `Filament\Forms\Components\*`, Schemas `Filament\Schemas\Components\*`, Actions `Filament\Actions\*`, RelationManagers según estructura del proyecto/artisan.

### UX admin (alineado a skill filament-admin-standards cuando se implemente)

- Labels en español.
- Empty state del listado de users y de direcciones del user.
- Helper texts breves en password (edit) y en default address.

---

## 5. Flujo F02

```text
Admin (email ∈ admin_emails)
  → Filament UserResource
    → validate (borde)
    → Create/Update/DeleteUserAction
      → User (soft delete en delete)

  → AddressesRelationManager (contexto user)
    → validate (borde)
    → Create/Update/DeleteAddressAction (+ transaction si is_default)
      → Address (user_id fijo del owner)
```

---

## 6. Tests (PHPUnit) — DoD F02

| Área | Escenarios |
|------|------------|
| AuthZ panel | Admin lista users; no-admin / guest denegados (R9, R10) |
| User create | Happy path + required fields + email único (R2, R11, R12) |
| User update | Cambia datos; password vacío no rota hash (R3) |
| User delete | Soft delete y ausente del listado default (R4) |
| Address CRUD | Create/update/delete bajo un user (R5, R6, R7, R13) |
| Default | Marcar default desmarca la anterior del mismo user (R8) |
| Country | País inválido (no 2 letras) rechazado (R14) |

Usar factories `User` / `Address`. Tests de Filament Livewire (`livewire(ListUsers::class)` etc.) siguiendo patrones de F01 si existen; si no, tests de Actions + HTTP panel como mínimo, preferir feature tests del resource.

---

## 7. Riesgos y no-objetivos

| Riesgo | Mitigación |
|--------|------------|
| Confundir F02 con storefront de cuenta | Specs y DoD excluyen Livewire explícitamente |
| Doble default por race | Transaction en Actions al setear default |
| Admin edita password sin querer | Campo opcional en edit; vacío = no-op |
| Soft delete vs FK addresses | Soft delete no dispara cascade físico; documentado en D5 |

**No-objetivos:** roles, impersonation, export GDPR, verificación email, API REST de users.
