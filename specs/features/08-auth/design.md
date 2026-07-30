# F08 — Auth · Diseño técnico

> **ID:** F08 · **Slug:** `08-auth`
> **Requirements:** [`requirements.md`](requirements.md)
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md)
> **Producto:** [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)
> **Dominio:** `User` (existente); nuevas tablas de `spatie/laravel-permission` (roles/permissions)
> **Layout código:** tipo primero, área **Auth** (`app/Actions/Auth`, `app/DTOs/Auth` si aplica, `app/Exceptions/Auth`)
> **Stack:** Laravel 13, Livewire v4 MFC (mismo patrón que `checkout-page`), Filament v5, PHPUnit, Sail
> **Paquetes nuevos:** `laravel/fortify`, `spatie/laravel-permission` — **requieren aprobación explícita antes de `composer require`** (regla del proyecto: no cambiar dependencias sin OK)
> **UI:** reutilizar `layouts.storefront` y tokens de marca ya usados en `checkout-page`

Este documento describe el **CÓMO**. El **QUÉ** está en `requirements.md`.

---

## 1. Alcance técnico

| Incluye (F08) | Excluye |
|---------------|---------|
| Registro, login, logout, verificación email, reset password | Perfil, direcciones, pedidos, reseñas propias (F09) |
| Roles `customer`/`admin` (Spatie) | UI de gestión de roles en Filament (asignación solo por artisan/seeder) |
| Migración `canAccessPanel()` → `hasRole('admin')` | Wishlist (F10) |
| Throttle de login | Login social, 2FA |
| `lang/es/auth.php`, `lang/es/passwords.php` | Vincular pedidos de invitado pasados |

**Decisión de integración (ya resuelta en exploración, no repetir el debate):** Fortify se usa **solo como librería de Actions/reglas**, nunca sus rutas ni vistas. Los componentes Livewire MFC son dueños de rutas/vistas/submit, igual que `checkout-page`.

---

## 2. Modelo de datos

### `users` (existente — sin migración de columnas)

Ya tiene `email_verified_at` (cast `datetime`) y `password` (cast `hashed`). Solo cambia el **modelo** (`app/Models/User.php`):

```php
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, HasRoles, MustVerifyEmailTrait, Notifiable, SoftDeletes;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin');
    }
}
```

`HasRoles` (verificado en docs `spatie/laravel-permission` v7) agrega `assignRole()`/`hasRole()`.

### Nuevas tablas (Spatie)

Vía `php artisan vendor:publish --tag="permission-migrations"` + `migrate`: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`. Sin cambios a `users`.

### Seeder de roles + backfill (orden crítico — mitiga riesgo de lockout)

`database/seeders/RoleSeeder.php` (o `RoleAndAdminBackfillSeeder.php`):

```php
Role::firstOrCreate(['name' => 'admin']);
Role::firstOrCreate(['name' => 'customer']);

$adminEmails = config('ecommerce.admin_emails', []);

User::query()->each(function (User $user) use ($adminEmails) {
    $user->assignRole(in_array($user->email, $adminEmails, true) ? 'admin' : 'customer');
});
```

**Debe correr en el mismo deploy, antes de que `canAccessPanel()` use `hasRole('admin')`** — si se separa en dos deploys, hay una ventana donde ningún admin tiene rol asignado. Ejecutar como parte de `php artisan migrate --force` (seeder llamado desde una migración de datos) o como paso explícito de release, no como seeder manual post-deploy.

---

## 3. Flujo de extremo a extremo

```text
[Visitante] login-page / register-page (Livewire MFC, layouts.storefront)
       │  submit → método del componente
       ▼
RegisterUserAction (implements CreatesNewUsers)  /  Auth::attempt() + LoginRateLimiter
       │  valida (PasswordValidationRules de Fortify) + persiste / autentica
       ▼
Auth::login($user) → dispara Illuminate\Auth\Events\Login / Registered (nativo)
       │
       ├─→ MergeGuestCartOnLoginListener (ya existe, auto-discovered) → fusiona carrito invitado
       └─→ SendEmailVerificationNotification (listener nativo de Laravel en Registered)
       ▼
[Cliente autenticado, no verificado] → wishlist/reviews/checkout autenticado bloqueados (R13)
       │  clic en enlace de verificación
       ▼
hasVerifiedEmail() = true → acciones de compromiso habilitadas
```

### Registro (R1, R9, R10)

1. `register-page` valida con Livewire rules (name, email unique, password + confirmation, terms checkbox).
2. Llama `app(CreatesNewUsers::class)->create($input)` (contrato Fortify, implementado por `RegisterUserAction`).
3. `RegisterUserAction::create()` valida de nuevo a nivel de dominio (defensa en profundidad, reutiliza `PasswordValidationRules::passwordRules()`), crea el `User`, **asigna rol `customer`** (`$user->assignRole('customer')` — nunca `admin`), dispara `event(new Registered($user))` (nativo → envía verificación).
4. `Auth::login($user)`.

### Login (R3, R11, R12)

1. `login-page` inyecta `Laravel\Fortify\LoginRateLimiter` (clase verificada en docs Fortify) en el método `login()`.
2. Si `tooManyAttempts()` → error R12, no intenta autenticar.
3. `Auth::attempt(['email' => ..., 'password' => ...])`. Si falla → `increment()` + mensaje genérico (R11). Si ok → `clear()`.
4. Éxito dispara `Login` nativo → merge de carrito (listener existente, sin código nuevo).

### Logout (R4)

`Auth::logout()` + invalidar sesión (`session()->invalidate()`, `regenerateToken()`) desde el método del componente o una ruta simple `POST /logout`.

### Password reset (R5, R6, R14)

- Solicitud: `Password::sendResetLink(['email' => ...])` (facade nativa de Laravel, reutiliza `password_reset_tokens` ya existente). Respuesta no revela si el email existe (R5).
- Confirmación: `app(ResetsUserPasswords::class)->reset($user, $input)` (contrato Fortify, implementado por `ResetPasswordAction`, reutiliza `passwordRules()`). Token inválido/vencido → `Password::INVALID_TOKEN` (R14).

### Verificación de email + gate de acciones de compromiso (R2, R13)

- `MustVerifyEmail` trait nativo de Laravel maneja `hasVerifiedEmail()` / `markEmailAsVerified()` / firma de URL.
- Gate: en los entrypoints de wishlist (F10, futuro), reviews (F07, ya existe con su propio Policy) y en `checkout-page.php::confirm()` **agregar** (no reescribir) una guarda mínima al inicio:

```php
if (Auth::check() && ! Auth::user()->hasVerifiedEmail()) {
    $this->errorMessage = __('auth.verify_email_required');
    return null;
}
```

Guest checkout (`Auth::check() === false`) **no entra en esta rama** — sin cambio de comportamiento (D2/R16).

---

## 4. Capas de aplicación

### Actions (`app/Actions/Auth/`)

| Action | Responsabilidad | Contrato Fortify |
|--------|------------------|-------------------|
| `RegisterUserAction` | R1, R9, R10 — crea user, asigna rol `customer` | `Laravel\Fortify\Contracts\CreatesNewUsers` |
| `ResetPasswordAction` | R6, R14 — valida + guarda nueva contraseña | `Laravel\Fortify\Contracts\ResetsUserPasswords` |

No se usan los stubs por defecto de `vendor:publish --tag=fortify-support` (se publican como `app/Actions/Fortify/CreateNewUser.php`, sin sufijo `Action` y en carpeta nombrada por el paquete, no por área) — **violan la convención de sufijos y de área del proyecto**. En su lugar, se implementan los contratos directamente en `app/Actions/Auth/*Action.php` y se registran:

```php
// app/Providers/FortifyServiceProvider.php::boot()
Fortify::createUsersUsing(RegisterUserAction::class);
Fortify::resetUserPasswordsUsing(ResetPasswordAction::class);
Fortify::ignoreRoutes();
```

`config/fortify.php`: `'views' => false` (evita registrar controllers de vista aunque no se usen).

Login/Logout no necesitan Action propio (son una llamada directa a `Auth::attempt()`/`Auth::logout()` + `LoginRateLimiter` dentro del componente Livewire) — no hay orquestación multi-modelo que justifique una Action según convención (`Actions vs Services`).

### Exceptions (`app/Exceptions/Auth/`)

Ninguna nueva obligatoria: los rechazos de R9-R14 se resuelven con validación Livewire + respuestas nativas de Fortify/Laravel (`ValidationException`, `Password::INVALID_TOKEN`). Si `RegisterUserAction` necesita distinguir un error de dominio propio, usar `DuplicateEmailException` — evaluar en implementación, no forzarla ahora.

### Provider

Nuevo `app/Providers/FortifyServiceProvider.php` (convención estándar de instalación de Fortify — mantiene `AppServiceProvider` enfocado en lo que ya tiene: cart listener, Blade paths, rate limiters de pagos/cupones). Registrar en `bootstrap/providers.php`.

---

## 5. Filament

Sin recurso nuevo. Único cambio: `canAccessPanel()` (sección 2). **No** se agrega UI de gestión de roles en F08 (D5/D6: asignación de `admin` solo por seeder/artisan) — evita ceremonia no pedida (`filament-shield` fue evaluado y descartado en la exploración).

---

## 6. Storefront (Livewire MFC)

Mismo patrón que `resources/views/components/checkout-page/`:

| Componente | Ruta | Responsabilidad |
|------------|------|------------------|
| `login-page` | `GET /login` | Form login, `LoginRateLimiter`, `Auth::attempt()` |
| `register-page` | `GET /register` | Form registro, llama `RegisterUserAction` vía contrato |
| `forgot-password-page` | `GET /forgot-password` | Form solicitud reset, `Password::sendResetLink()` |
| `reset-password-page` | `GET /reset-password/{token}` | Form nueva contraseña, llama `ResetPasswordAction` vía contrato |
| `verify-email-notice` | `GET /verify-email` | Aviso + botón reenviar (`$user->sendEmailVerificationNotification()`) |

Todos con `#[Layout('layouts.storefront')]`, validación Livewire-native (`$this->validate($this->rules())`), mismo estilo visual que `checkout-page`. `routes/web.php` los registra con `Route::livewire()`, middleware `guest`/`auth` según corresponda (mismo mecanismo nativo de Laravel, sin rutas de Fortify).

Ruta de verificación de email firmada: usa el `EmailVerificationRequest` nativo de Laravel (valida firma + hash), no requiere componente propio de validación de firma.

---

## 7. i18n

`lang/es/auth.php` + `lang/es/passwords.php` — traducir las claves default de Laravel (`failed`, `password`, `throttle`, `sent`, `reset`, `token`, `user`) más una clave nueva propia: `auth.verify_email_required` (usada en el gate de la sección 3).

---

## 8. Rutas (`routes/web.php`)

```php
Route::livewire('/login', 'login-page')->middleware('guest')->name('login');
Route::livewire('/register', 'register-page')->middleware('guest')->name('register');
Route::livewire('/forgot-password', 'forgot-password-page')->middleware('guest')->name('password.request');
Route::livewire('/reset-password/{token}', 'reset-password-page')->middleware('guest')->name('password.reset');
Route::livewire('/verify-email', 'verify-email-notice')->middleware('auth')->name('verification.notice');
Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['auth', 'signed'])->name('verification.verify');
Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');
```

`name('login')` es importante: Laravel's `Auth::authenticate()`/middleware `auth` redirige ahí por defecto en 401.

---

## 9. Tests (mapa)

| Archivo sugerido | Cubre |
|-------------------|--------|
| `tests/Feature/Auth/RegisterTest.php` | R1, R9, R10 |
| `tests/Feature/Auth/LoginTest.php` | R3, R11, R12 |
| `tests/Feature/Auth/LogoutTest.php` | R4 |
| `tests/Feature/Auth/PasswordResetTest.php` | R5, R6, R14 |
| `tests/Feature/Auth/EmailVerificationTest.php` | R2, R13 |
| `tests/Feature/Auth/AdminPanelAccessTest.php` | R7, R15 (rol admin vs sin rol) |
| `tests/Feature/Checkout/GuestCheckoutRegressionTest.php` | R16 (extiende suite existente de F04/F05, no la reemplaza) |
| `tests/Feature/Auth/RoleBackfillSeederTest.php` | Seeder asigna `admin` a `admin_emails` y `customer` al resto |

---

## 10. Riesgos y mitigaciones

| Riesgo | Mitigación |
|--------|------------|
| Lockout de admins durante cutover | Seeder de roles corre en el mismo deploy, antes del cambio de `canAccessPanel()` (sección 2); test explícito de backfill |
| Duplicar registro del listener de carrito | No tocar `MergeGuestCartOnLoginListener` ni su registro manual en `AppServiceProvider` — fuera de alcance F08, ya documentado como deuda separada |
| Stubs de Fortify rompen convención de nombres | No se publican; se implementan los contratos directamente con nombres/área del proyecto (sección 4) |
| Gate de verificación toca `checkout-page.php` (código de F04/F05) | Cambio aditivo de 3 líneas al inicio de `confirm()`; guest (`Auth::check() === false`) no entra en la rama — test de regresión explícito (R16) |
| `lang/es/*` de auth/passwords faltantes | Tarea explícita en `tasks.md` |

---

## 11. Orden de implementación sugerido

1. Paquetes (`laravel/fortify`, `spatie/laravel-permission`) — **solo tras aprobación** — + publish migrations Spatie + `FortifyServiceProvider`.
2. Seeder de roles + backfill + test.
3. `User` model: `HasRoles`, `MustVerifyEmail`, `canAccessPanel()`.
4. `RegisterUserAction`, `ResetPasswordAction` + bindings Fortify.
5. Rutas + componentes Livewire (login, register, forgot/reset password, verify-email).
6. Gate de verificación en checkout/wishlist-futuro/reviews-futuro (solo checkout aplica hoy).
7. `lang/es/auth.php`, `lang/es/passwords.php`.
8. Tests (tabla §9) + regresión guest checkout.
9. Pint + marcar roadmap F08 → Completa.

---

## 12. Referencias de código existente

| Pieza | Path |
|-------|------|
| Model | `app/Models/User.php` |
| Guard/provider | `config/auth.php` |
| Patrón guest/user ya validado | `app/Models/Cart.php`, `app/Listeners/Cart/MergeGuestCartOnLoginListener.php` |
| Componente Livewire MFC de referencia | `resources/views/components/checkout-page/` |
| Layout storefront | `resources/views/layouts/storefront.blade.php` |
| Rate limiters existentes (patrón) | `app/Providers/AppServiceProvider.php` (`payments-start`, `coupons-preview`) |
| Config admin whitelist (a reemplazar) | `config/ecommerce.php` (`admin_emails`) |
| i18n existente sin traducir | `lang/en/auth.php`, `lang/en/passwords.php` |
