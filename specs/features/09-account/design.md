# Design: F09 — Cuenta/perfil del comprador

> Referencia: criterios R1-R19 y decisiones D1-D6 en [`requirements.md`](./requirements.md). Este documento cubre el CÓMO; no repite el QUÉ.

## Enfoque técnico

Reusa el grafo de dominio existente sin migraciones. Cuatro áreas bajo `/profile/*` con middleware `auth`: perfil, direcciones, pedidos, reseñas — cada una siguiendo el patrón MFC de F08 para lo mutable/interactivo, y Controller invocable + Blade de F04 para el único caso de solo lectura (detalle de pedido). Reusa Actions ya existentes de F02 (`Addresses`) y F07 (`Reviews`) tal cual están; solo agrega piezas nuevas donde realmente no existía capacidad previa (perfil, ownership de direcciones).

## Decisiones de arquitectura

### D-A1 — Livewire MFC vs Blade, aplicación concreta

| Ruta | Nombre | Tipo | Justificación |
|---|---|---|---|
| `/profile` | `profile` | Livewire MFC | Formulario reactivo perfil+password, mismo patrón `login-page` |
| `/profile/addresses` | `profile.addresses` | Livewire MFC | CRUD + marcar default inline sin reload |
| `/profile/orders` | `profile.orders` | Livewire MFC | Paginación reactiva; alcance ya acotado a 4 estados por D3 |
| `/profile/orders/{order}` | `profile.orders.show` | Blade + Controller invocable | Solo lectura — mismo patrón que `OrderThankYouController` (F04) |
| `/profile/reviews` | `profile.reviews` | Livewire MFC | Editar/eliminar inline sin reload |

El detalle de pedido es el único caso de solo-lectura puro; meter Livewire ahí repetiría el error que el listado de pedidos evita — overhead sin interacción que lo justifique.

### D-A2 — Invariante de dirección default: NO se extrae Service (revisa hallazgo de exploración)

Verificado en código real: `app/Actions/Addresses/{Create,Update,Delete}AddressAction.php` YA encapsulan la invariante D4 de F02 (clear-default-then-set vía `UpsertAddressDTO->userId`), y `DeleteAddressAction` YA NO reasigna default automáticamente al borrar — cumple D5/R18 sin cambios. La exploración inicial sugería extraer un Service; al leer el código se confirma que **no hace falta**: las Actions ya son el punto de reuso compartido entre admin (F02) y storefront (F09). Extraer un Service ahora sería indirección sin beneficio.

### D-A3 — Autorización: nueva `AddressPolicy`, reuso de `ReviewPolicy`/`OrderPolicy`

`OrderPolicy::view()` y `ReviewPolicy::{view,update,delete}()` ya resuelven ownership (`user_id === auth()->id()`) — se reusan sin cambios desde los componentes de cuenta. No existe `AddressPolicy` (F02 autorizaba solo por gate de panel Filament, nunca por ownership de usuario final) — se crea nueva con la misma forma que las otras dos.

### D-A4 — Cambio de email + re-verificación

`User implements MustVerifyEmail`; en Laravel 13 el contrato incluye `markEmailAsUnverified()` (confirmado vía Boost `search-docs`, upgrade notes 13.0). `UpdateProfileAction` detecta `$dto->email !== $user->email`, llama `$user->markEmailAsUnverified()` y `$user->sendEmailVerificationNotification()` dentro de la misma operación de guardado — mismo mecanismo nativo que ya dispara el registro en F08, sin notificación custom.

### D-A5 — Password y listados: sin Service ni Action de lectura

`UpdatePasswordAction` es la única pieza nueva para contraseña (verifica `Hash::check` de la actual antes de `Hash::make` de la nueva). Para "mis pedidos"/"mis reseñas" NO se crean `GetUserOrdersAction`/`GetUserReviewsAction`: son lecturas triviales de un solo caller — se agregan scopes de modelo (`Order::scopeVisibleInAccountHistory()`, `Review::scopeOwnedBy()`), mismo nivel que `Review::scopeApproved()` ya existente. Crear una Action que solo reenvía a una query violaría la regla de project-conventions contra Action+Service que solo se reenvían.

## Cambios de archivos

| Archivo | Acción | Descripción |
|---|---|---|
| `routes/web.php` | Modificar | 5 rutas nuevas dentro del grupo `Route::middleware('auth')` existente |
| `resources/views/components/profile-page/` | Nuevo | Livewire MFC: perfil + password |
| `resources/views/components/profile-addresses-page/` | Nuevo | Livewire MFC: libreta de direcciones |
| `resources/views/components/profile-orders-page/` | Nuevo | Livewire MFC: listado de pedidos |
| `resources/views/components/profile-reviews-page/` | Nuevo | Livewire MFC: mis reseñas |
| `app/Http/Controllers/Account/ProfileOrderDetailController.php` | Nuevo | Detalle de pedido, invocable, solo lectura |
| `resources/views/account/orders/show.blade.php` | Nuevo | Vista Blade del detalle |
| `app/Actions/Account/UpdateProfileAction.php` | Nuevo | Datos básicos + trigger de re-verify |
| `app/Actions/Account/UpdatePasswordAction.php` | Nuevo | Cambio de contraseña |
| `app/DTOs/Account/UpdateProfileDTO.php` | Nuevo | DTO de perfil |
| `app/Policies/AddressPolicy.php` | Nuevo | Ownership de direcciones storefront |
| `app/Models/Order.php` | Modificar | Scope `scopeVisibleInAccountHistory` |
| `app/Models/Review.php` | Modificar | Scope `scopeOwnedBy` |
| `app/Enums/Orders/OrderStatusEnum.php` | Modificar | Helper `accountHistoryStatuses(): array` (Paid/Processing/Shipped/Delivered) |
| `app/Actions/Addresses/*` | Reusar | Sin cambios |
| `app/Actions/Reviews/{Update,Delete}ReviewAction.php` | Reusar | Sin cambios |
| `lang/{en,es}/account.php` | Nuevo | Copy storefront de esta feature |

## Contratos

```php
final readonly class UpdateProfileDTO
{
    public function __construct(
        public int $userId,
        public string $name,
        public string $email,
        public ?string $phone,
    ) {}
}
```

- `UpdateProfileAction::__invoke(User $user, UpdateProfileDTO $dto): User` — lanza `ValidationException` si el email ya pertenece a otra cuenta.
- `UpdatePasswordAction::__invoke(User $user, string $currentPassword, string $newPassword): void` — lanza `ValidationException` si `Hash::check($currentPassword, $user->password)` falla.
- `AddressPolicy::{view,update,delete}(User $user, Address $address): bool` — `(int) $address->user_id === (int) $user->id`.

## Estrategia de pruebas

| Capa | Qué | Cómo |
|---|---|---|
| Feature (Livewire) | R1, R2, R13, R14 — perfil/password | `livewire(ProfilePage::class)->set(...)->call('save')` |
| Feature (Livewire) | R3-R6, R15, R18 — direcciones | Reusa factories de F02; agrega caso ownership (R16) |
| Feature (Livewire) | R7, R9-R11 — listados y mutación inline | Paginación scoped a paid+; edición vuelve a pendiente |
| Feature (Controller) | R8 — detalle de pedido | HTTP test; 403 si el pedido no es del usuario autenticado |
| Feature | R17, R19 | Redirect a login sin sesión; perfil editable con email no verificado |
| Unit | Scopes `visibleInAccountHistory`, `ownedBy` | Factories con estados mixtos (pending/paid/cancelled, otro usuario) |

## Migración / Rollout

No requiere migraciones de esquema. Todo el trabajo vive en `feature/09-account`, aislado de `develop`.

## Preguntas abiertas

Ninguna — las decisiones quedaron cerradas en D-A1 a D-A5.
