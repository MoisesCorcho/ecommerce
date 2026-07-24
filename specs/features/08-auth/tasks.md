# F08 — Auth · Tasks

> **ID:** F08 · **Slug:** `08-auth`
> **Requirements:** [`requirements.md`](requirements.md) · **Design:** [`design.md`](design.md)
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md) · **Calidad:** [`02-feature-quality.md`](../../_global/02-feature-quality.md)

**Alcance DoD:** registro, login, logout, verificación de email, reset de contraseña, roles cliente/admin, migración de acceso al panel, i18n, tests.
**Fuera de DoD:** perfil/direcciones/pedidos/reseñas (F09), wishlist (F10), login social, 2FA, vincular pedidos de invitado pasados.

**Estado de implementación:** No iniciada.

---

## 0. Gate de dependencias (bloqueante — requiere aprobación explícita del usuario)

- [ ] 0.1 **STOP.** Confirmar con el usuario antes de ejecutar `composer require laravel/fortify spatie/laravel-permission` — regla del proyecto: no cambiar dependencias sin OK explícito. No continuar con la Fase 1 sin esta confirmación.

## 1. Fundación: paquetes, config, roles

- [ ] 1.1 `composer require laravel/fortify spatie/laravel-permission` (tras 0.1). _(prerequisito de R1, R3, R5, R6, R7)_
- [ ] 1.2 `php artisan vendor:publish --tag=fortify-config` → `config/fortify.php` con `'views' => false`. _(cubre R8)_
- [ ] 1.3 `php artisan vendor:publish --tag="permission-migrations"` + migrar tablas `roles`/`permissions`/`model_has_roles`. _(prerequisito de R5, R7, R15)_
- [ ] 1.4 Crear `app/Providers/FortifyServiceProvider.php`: `Fortify::ignoreRoutes()`, registrar en `bootstrap/providers.php`. _(cubre R1, R3, R5, R6)_
- [ ] 1.5 `database/seeders/RoleAndAdminBackfillSeeder.php`: crea roles `admin`/`customer`; asigna `admin` a usuarios en `config('ecommerce.admin_emails')` y `customer` al resto. _(cubre R5, R7 — bloqueante de 2.1)_
- [ ] 1.6 Correr y **verificar** el seeder en el entorno de destino antes de tocar `canAccessPanel()` (tarea 2.1) — ningún administrador actual debe quedar sin rol. _(cubre R7 — gate de la Fase 2)_

## 2. Domain: modelo y Actions

- [ ] 2.1 `app/Models/User.php`: agregar `HasRoles`, implementar `MustVerifyEmail`, reemplazar `canAccessPanel()` por `hasRole('admin')`. **Solo después de 1.6 verificado.** _(cubre R7, R15)_
- [ ] 2.2 `app/Actions/Auth/RegisterUserAction.php` (implementa `CreatesNewUsers`): valida (reusa `PasswordValidationRules::passwordRules()`), crea `User`, asigna rol `customer`, dispara `Registered`. _(cubre R1, R9, R10)_
- [ ] 2.3 `app/Actions/Auth/ResetPasswordAction.php` (implementa `ResetsUserPasswords`): valida y guarda nueva contraseña. _(cubre R6, R14)_
- [ ] 2.4 Bindings en `FortifyServiceProvider::boot()`: `Fortify::createUsersUsing(RegisterUserAction::class)`, `Fortify::resetUserPasswordsUsing(ResetPasswordAction::class)`. _(cubre R1, R6)_

## 3. Storefront: componentes Livewire

- [ ] 3.1 `login-page` (MFC, `layouts.storefront`): form + `LoginRateLimiter` (inyectado) + `Auth::attempt()` + merge de carrito nativo. _(cubre R3, R11, R12)_
- [ ] 3.2 `register-page`: form + llamada a `app(CreatesNewUsers::class)->create()` + `Auth::login()`. _(cubre R1, R9, R10)_
- [ ] 3.3 `forgot-password-page`: form + `Password::sendResetLink()`, respuesta sin revelar existencia de email. _(cubre R5)_
- [ ] 3.4 `reset-password-page`: form + `app(ResetsUserPasswords::class)->reset()`, manejo de token inválido/vencido. _(cubre R6, R14)_
- [ ] 3.5 `verify-email-notice`: aviso + botón reenviar verificación. _(cubre R2, R13)_

## 4. Integración: rutas, logout, gate de verificación

- [ ] 4.1 Registrar rutas en `routes/web.php` (`login`, `register`, `password.request`, `password.reset`, `verification.notice`, `verification.verify` con `EmailVerificationRequest`, `logout`). _(cubre R1, R3, R4, R5, R6, R2, R8)_
- [ ] 4.2 `LogoutController` (o método simple): `Auth::logout()` + invalidar sesión. _(cubre R4)_
- [ ] 4.3 Guardia de verificación de email: agregar 3 líneas al inicio de `checkout-page.php::confirm()` (`Auth::check() && ! hasVerifiedEmail()` → error). **No modificar la rama guest.** _(cubre R13)_

## 5. i18n

- [ ] 5.1 `lang/es/auth.php` (traducción de claves default: `failed`, `password`, `throttle`) + clave nueva `verify_email_required`. _(cubre R17)_
- [ ] 5.2 `lang/es/passwords.php` (traducción de claves default: `sent`, `reset`, `throttled`, `token`, `user`). _(cubre R17)_

## 6. Tests (PHPUnit)

- [ ] 6.1 `tests/Feature/Auth/RoleBackfillSeederTest.php`: admins de `admin_emails` reciben rol `admin`; el resto `customer`. _(cubre R5, R7)_
- [ ] 6.2 `tests/Feature/Auth/RegisterTest.php`: registro válido asigna `customer` y loguea; email duplicado rechaza; password inválida/sin confirmar rechaza; sin aceptar términos rechaza. _(cubre R1, R9, R10)_
- [ ] 6.3 `tests/Feature/Auth/LoginTest.php`: credenciales válidas loguea y fusiona carrito invitado; credenciales inválidas rechaza con mensaje genérico; throttle tras intentos excesivos. _(cubre R3, R11, R12)_
- [ ] 6.4 `tests/Feature/Auth/LogoutTest.php`: cierre de sesión efectivo. _(cubre R4)_
- [ ] 6.5 `tests/Feature/Auth/PasswordResetTest.php`: solicitud no revela existencia de email; enlace válido cambia contraseña; enlace vencido/usado rechaza. _(cubre R5, R6, R14)_
- [ ] 6.6 `tests/Feature/Auth/EmailVerificationTest.php`: enlace marca verificado; acción de compromiso (checkout autenticado) bloqueada sin verificar. _(cubre R2, R13)_
- [ ] 6.7 `tests/Feature/Auth/AdminPanelAccessTest.php`: usuario con rol `admin` accede al panel; usuario sin rol denegado. _(cubre R7, R15)_
- [ ] 6.8 Extender suite de checkout existente (F04/F05) con caso explícito de regresión: compra completa **sin cuenta** funciona igual que antes de F08. _(cubre R16)_
- [ ] 6.9 UI con estilo de marca: verificación (manual o snapshot) de que login/registro usan `layouts.storefront`. _(cubre R8)_

## 7. Cierre de calidad

- [ ] 7.1 Tests del alcance F08 en verde vía Sail.
- [ ] 7.2 Pint en PHP tocado (`vendor/bin/sail bin pint --dirty --format agent`).
- [ ] 7.3 Estado F08 = **Completa** en requirements + roadmap al cerrar implementación (desbloquea F09/F10).

---

## Mapa de trazabilidad

| Criterio | Tareas |
|----------|--------|
| R1 | 1.1, 1.4, 2.2, 2.4, 3.2, 4.1, 6.2 |
| R2 | 3.5, 4.1, 6.6 |
| R3 | 1.1, 1.4, 3.1, 4.1, 6.3 |
| R4 | 4.1, 4.2, 6.4 |
| R5 | 1.1, 1.4, 1.5, 1.6, 3.3, 4.1, 6.1, 6.5 |
| R6 | 1.1, 1.4, 2.3, 2.4, 3.4, 4.1, 6.5 |
| R7 | 1.5, 1.6, 2.1, 6.1, 6.7 |
| R8 | 1.2, 4.1, 6.9 |
| R9 | 2.2, 3.2, 6.2 |
| R10 | 2.2, 3.2, 6.2 |
| R11 | 3.1, 6.3 |
| R12 | 3.1, 6.3 |
| R13 | 3.5, 4.3, 6.6 |
| R14 | 2.3, 3.4, 6.5 |
| R15 | 2.1, 6.7 |
| R16 | 4.3, 6.8 |
| R17 | 5.1, 5.2 |

---

## Definition of Done (checklist tasks)

- [ ] Criterios **R1–R17** implementados y testeados.
- [ ] Ningún administrador existente pierde acceso al panel durante el despliegue (1.5–1.6 antes de 2.1).
- [ ] Checkout de invitado sin regresión (6.8 en verde).
- [ ] `lang/es/auth.php` y `lang/es/passwords.php` completos.
- [ ] Actions en área **Auth** (`app/Actions/Auth`), sin usar stubs por defecto de Fortify.
- [ ] PHPUnit del alcance en verde vía Sail; Pint OK.
- [ ] Specs + roadmap con estado **Completa** (al cerrar implementación).
