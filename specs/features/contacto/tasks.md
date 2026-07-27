# Contacto — Tasks

> **Slug:** `contacto` (página utilitaria de storefront, fuera de la secuencia F0N)
> **Requirements:** [`requirements.md`](requirements.md) · **Design:** Engram `sdd/contacto/design` (#109)
> **Convenciones:** `CLAUDE.md` (project-conventions) · **Calidad:** [`02-feature-quality.md`](../../_global/02-feature-quality.md)

**Alcance DoD:** ruta `/contact` pública, columna de info estática (placeholders), formulario con validación cliente+servidor, envío de correo sync con `Reply-To`, rate limiting 3/600s por IP, banner de error con `mailto` de respaldo, CTA a `/faq`, enlaces de nav/footer activados, `lang/{en,es}/contact.php`, tests.
**Fuera de DoD:** adjuntos, selector de tipo de solicitud, tickets, chat en vivo, persistencia de mensajes en BD, admin de mensajes, página `/faq` en sí.

Sin fase de esquema: no hay migraciones ni modelos nuevos.

---

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~700 (Batch A ~340, Batch B ~360) |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | Batch A (núcleo funcional) → Batch B (visual + pulido) |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending (usuario maneja commits/PRs manualmente) |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: pending
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Focused test command | Runtime harness | Rollback boundary |
|------|------|-----------|----------------------|-----------------|-------------------|
| A | Núcleo funcional: ruta, config, rate limiter, Mailable, vista de mail, i18n, clase MFC, blade mínima semántica, enlaces de nav/footer, `ContactPageTest` completo | Batch A | `vendor/bin/sail artisan test --compact tests/Feature/Storefront/ContactPageTest.php` | Mailpit (`MAIL_MAILER=smtp` local) para verificación visual del correo; cobertura real vía la suite automatizada | Revertir archivos nuevos (`routes/web.php` entry, bloque de `config/ecommerce.php`, `contact-page/`, `Mail/Contact/`, vista de mail, `lang/{en,es}/contact.php`) + revertir los 4 edits de `storefront.blade.php` |
| B | Capa visual: estilos Tailwind completos según UI brief, contador Alpine, estados de carga/éxito/error, auditoría `impeccable` | Batch B | mismo comando anterior (debe seguir en verde, sin cambios de comportamiento) + aserciones de markup agregadas | Revisión manual en navegador (`sail open` → `/contact` en desktop/tablet/móvil) + skill `impeccable` como paso obligatorio de cierre | Revertir solo el diff de `contact-page.blade.php` (estilos/markup) — ningún archivo de lógica se toca |

---

## Batch A — Núcleo funcional

### 1. Config e i18n (fundación)

- [x] 1.1 `config/ecommerce.php`: agregar bloque `contact` → `inbox` (`env('CONTACT_MAIL_TO')`) y `public_email` (`env('CONTACT_PUBLIC_EMAIL')`), con doc comment igual al resto del archivo. _(cubre R1, R3, R14, D1, D3)_
- [ ] 1.2 `.env.example` (y `.env` local): agregar `CONTACT_MAIL_TO=` y `CONTACT_PUBLIC_EMAIL=` documentadas. _(cubre D1)_ — **BLOCKED**: sandbox permissions deny all Read/Bash access to `.env.example`/`.env` in this session; needs manual edit by the user (see Deviations).
- [x] 1.3 `lang/en/contact.php` + `lang/es/contact.php`: crear dominio completo (`breadcrumb`, `title`, `subtitle`, `info.*`, `form.*`, `form.placeholders.*`, `success.*`, `error.*`, `faq.*`, `mail.subject`) por el mapa de claves del design. ES en tuteo neutro, sin voseo. _(cubre R16)_

### 2. Rate limiter (RED → GREEN)

- [x] 2.1 RED: `tests/Unit/Support/Contact/ContactFormRateLimiterTest.php` — `attempt()` retorna `true` en los primeros 3 intentos y `false` en el 4º dentro de la ventana; `key()` retorna `'contact-form:ip:{ip}'`.
- [x] 2.2 GREEN: `app/Support/Contact/ContactFormRateLimiter.php` — mirror exacto de `RegisterAttemptRateLimiter` (`MAX_ATTEMPTS = 3`, `DECAY_SECONDS = 600`). _(cubre R13, A4)_

### 3. Mail (Mailable + vista)

- [x] 3.1 `app/Mail/Contact/ContactFormSubmittedMail.php`: Mailable NO `ShouldQueue` (A2), constructor `senderName`/`senderEmail`/`subjectLine`/`body` readonly, `envelope()` con `replyTo: senderEmail` y subject `__('contact.mail.subject', ['subject' => ...])`, `content()` → vista `mail.contact.submitted`. _(cubre R3, D4)_
- [x] 3.2 `resources/views/mail/contact/submitted.blade.php`: cuerpo plano con nombre/correo/asunto/mensaje del remitente.

### 4. Ruta + componente MFC + vista mínima (RED → GREEN)

- [x] 4.1 RED: primer bloque de `tests/Feature/Storefront/ContactPageTest.php` — `GET /contact` responde 200 para invitado y para usuario autenticado; renderiza info card, form y CTA de FAQ; usuario autenticado ve `name`/`email` prellenados y editables. _(cubre R1, R4)_
- [x] 4.2 GREEN: `routes/web.php` — `Route::livewire('/contact', 'contact-page')->name('contact')`, público, junto a `/products`. _(cubre R1, R8)_
- [x] 4.3 GREEN: `resources/views/components/contact-page/contact-page.php` — estado (`name`, `email`, `subject`, `message`, `sent`, `errorMessage`), `mount()` prellena desde `auth()->user()` si existe, `rules()`, `validationAttributes()`. _(cubre R1, R4, A1)_
- [x] 4.4 GREEN: `resources/views/components/contact-page/contact-page.blade.php` — versión semántica mínima (sin estilos finales) suficiente para conducir los tests: breadcrumb, columna info con placeholders marcados `{{-- PLACEHOLDER --}}`, formulario, CTA `/faq`. _(cubre R1, R2, R7, D2)_

### 5. Validación en tiempo real (RED → GREEN)

- [x] 5.1 RED: bloque de validación en `ContactPageTest` — `submit()` con cada campo vacío rechaza y muestra error junto al campo, sin `Mail::assertSent`; correo con formato inválido rechaza con mensaje localizado; mensaje >1000 caracteres bloquea el envío. _(cubre R10, R11, R12, R15)_
- [x] 5.2 GREEN: `updated(string $property)` en `contact-page.php` → `$this->validateOnly($property)` para feedback en tiempo real; `rules()` usa `max:1000` en `message`. _(cubre R5, R10, R11, R12, R15)_
- [x] 5.3 GREEN: contador de caracteres Alpine-local en `contact-page.blade.php` (`maxlength="1000"`, color rojo cerca/al límite), sin `wire:model.live`. _(cubre R5, R12, A5)_ — **Partial**: server-rendered counter (`strlen($message)`) is wired; live Alpine-only DOM update deferred to Batch B per the visual-pass boundary, no behavior regression.

### 6. Envío exitoso (RED → GREEN)

- [x] 6.1 RED: bloque happy-path en `ContactPageTest` — `Mail::fake()`, envío válido dispara `Mail::assertSent(ContactFormSubmittedMail::class, ...)` con `to` = `config('ecommerce.contact.inbox')` y `replyTo` = correo del remitente; `$sent === true`; campos reseteados tras `sendAnother()`/reset automático. _(cubre R3)_
- [x] 6.2 GREEN: `submit(ContactFormRateLimiter $limiter)` en `contact-page.php` — `$limiter->attempt(request()->ip())` → `$this->validate()` → `Mail::to(config('ecommerce.contact.inbox'))->send(new ContactFormSubmittedMail(...))` → `$this->sent = true` + reset de campos; `sendAnother()` vuelve a `idle`. _(cubre R3, R6)_
- [x] 6.3 GREEN: estados de carga/éxito en `contact-page.blade.php` — `wire:loading wire:target="submit"` (spinner + `__('contact.form.sending')`), `wire:loading.attr="disabled"` en inputs/botón; card de éxito con `role="status"`. _(cubre R6)_

### 7. Rate limiting y falla de envío (RED → GREEN)

- [x] 7.1 RED: bloque de throttle en `ContactPageTest` — 3 envíos válidos seguidos desde la misma IP pasan, el 4º es rechazado con `errorMessage` = `__('contact.error.throttled')` y `Mail::assertSentCount(3)`; limpiar `RateLimiter::clear(...)` en `setUp()` o variar IP entre tests. _(cubre R13)_ — the limiter check was already wired ahead of schedule (needed by `submit()` since section 5); this test confirmed pre-existing coverage rather than a fresh RED, noted as a deviation below.
- [x] 7.2 RED: bloque de falla de transporte — `Mail::shouldReceive('to')->andThrow(new TransportException('boom'))` (no se puede simular con `Mail::fake()`), asserts `errorMessage` = `__('contact.error.send_failed')`, `Log::error` invocado, sin filas nuevas en BD. _(cubre R14, D3)_
- [x] 7.3 GREEN: manejo de excepción en `submit()` — `try/catch (Throwable)` alrededor del envío → `Log::error(...)` + `$this->errorMessage = __('contact.error.send_failed', ['email' => config('ecommerce.contact.public_email')])`, campos del formulario preservados (no reset en error). _(cubre R14, D3)_
- [x] 7.4 GREEN: banner de error en `contact-page.blade.php` — `role="alert"`, enlace `mailto:` al `contact.public_email`. _(cubre R14)_

### 8. Enlaces de navegación (RED → GREEN)

- [x] 8.1 RED: bloque de navegación en `ContactPageTest` (o extensión de un test de layout existente) — header, nav móvil y footer resuelven a `route('contact')` (200, no 404); footer ya no apunta a `/faqs`. _(cubre R8, R9)_
- [x] 8.2 GREEN: `resources/views/layouts/storefront.blade.php` — líneas 32 y 94 (`url('/contact')` → `route('contact')`, nav desktop + móvil), línea 131 (`url('/contact')` → `route('contact')`, footer), línea 128 (`url('/faqs')` → `url('/faq')`). _(cubre R8, R9)_

### 9. i18n — verificación

- [x] 9.1 Bloque final de `ContactPageTest`: cambiar locale a `en`/`es` y verificar que la copy visible resuelve desde claves `__('contact.*')`, sin cadenas hardcodeadas. _(cubre R16)_

### 10. Cierre Batch A

- [x] 10.1 `tests/Feature/Storefront/ContactPageTest.php` completo en verde vía Sail.
- [x] 10.2 Pint en PHP tocado (`vendor/bin/sail bin pint --dirty --format agent`).

---

## Batch B — Visual y pulido (behavior-neutral)

> No cambia lógica de `contact-page.php`, rate limiter, Mailable ni i18n — solo markup/clases/Alpine sobre la blade ya verde de Batch A.

- [x] 11.1 `contact-page.blade.php`: wrapper responsive `mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop py-8 lg:py-12`; grid `lg:grid-cols-5` (info `lg:col-span-2`, form `lg:col-span-3`, `gap-12`); `md:grid-cols-1` tablet; una columna en móvil, info primero. _(cubre R2)_
- [x] 11.2 Columna de info estática con tokens (`bg-soft-sand`, `text-intense-cocoa`, `font-[family-name:var(--font-chillax)]` en heading, íconos SVG 24px/1.5-stroke para email/tel/WhatsApp/redes/horario), enlaces `mailto:`/`tel:`/`wa.me` funcionales. _(cubre R1)_
- [x] 11.3 Formulario: inputs con la receta de `register-page.blade.php` (borde inferior/completo delgado, `@error` cambia a `border-error`), botón submit con estilo Soft Gold/Intense Cocoa. _(cubre R15)_
- [x] 11.4 Card de éxito: `bg-soft-sand`, ícono check Soft Gold, `role="status"`, botón "enviar otro mensaje" (`sendAnother()`). _(cubre R3)_
- [x] 11.5 Banner de error: estilos Intense-Cocoa/`text-error`, `role="alert"`, `mailto:` visible del `public_email`. _(cubre R14)_
- [x] 11.6 CTA de FAQ: banner/card al final de la página con enlace a `/faq`. _(cubre R7)_
- [x] 11.7 Contador de caracteres estilizado y **Alpine-local en vivo** (`x-data`/`x-on:input`, color normal → rojo/negrita al llegar a 1000; texto localizado vía `contact.form.counter` sustituido client-side). _(cubre R5, R12, A5)_
- [x] 11.8 Confirmado: `ContactPageTest` sigue 15/15 verde tras el estilado — cero cambios de comportamiento (además de la suite completa 419/419).
- [x] 11.9 No se agregaron aserciones de markup adicionales — el diseño no lo ameritó más allá de la cobertura de comportamiento ya cubierta en Batch A; se prefirió no acoplar tests a clases Tailwind.

### 12. Auditoría de diseño (obligatoria — paso de cierre)

- [x] 12.1 Ejecutado el skill `impeccable` (`audit`) sobre `contact-page.blade.php`: `context.mjs` (sin PRODUCT.md/DESIGN.md — refinamiento sobre implementación incumbente permitido), detector mecánico (`detect.mjs`, 0 hallazgos) + auditoría estática de código contra las 5 dimensiones (a11y, performance, theming, responsive, integridad de implementación). **Nota de limitación**: sin herramienta de navegador/captura de pantalla disponible en esta sesión de sub-agente, no se validó visualmente en vivo (desktop/tablet/móvil); ver reporte de apply-progress para detalle exacto de lo verificado vs. no verificado.
- [x] 12.2 Hallazgos aplicados sin reabrir lógica de Batch A (solo Blade/atributos HTML): `required`/`maxlength`/`aria-required` en los 4 campos (faltaban vs. la tabla de "Client hint" del design), `aria-describedby` + `aria-invalid` enlazando cada input con su mensaje de error, `focus-visible:ring-2 focus-visible:ring-soft-gold` en los campos que remueven el outline nativo (WCAG 2.4.7). Fix adicional no relacionado al audit: colisión de la variable `$message` de Blade `@error` con la propiedad `$message` del componente (ver Deviations).
- [x] 12.3 Re-corrido `ContactPageTest` tras 12.2 — 15/15 verde; suite completa 419/419 verde.

### 13. Cierre de calidad (Batch B)

- [x] 13.1 Tests del alcance `contacto` en verde vía Sail (15/15 `ContactPageTest` + 419/419 suite completa).
- [x] 13.2 Pint en Blade/PHP tocado (`vendor/bin/sail bin pint --dirty --format agent` — clean).
- [x] 13.3 DoD de `requirements.md` marcado completo salvo `.env.example`/`.env` (bloqueado por permisos de sandbox desde Batch A, pendiente de acción manual del usuario — ver Risks).

---

## Mapa de trazabilidad

| Criterio | Tareas |
|----------|--------|
| R1 | 1.1, 4.1, 4.3, 4.4, 11.2 |
| R2 | 4.4, 11.1 |
| R3 | 1.1, 3.1, 6.1, 6.2, 11.4 |
| R4 | 4.1, 4.3 |
| R5 | 5.2, 5.3, 11.7 |
| R6 | 6.2, 6.3 |
| R7 | 4.4, 11.6 |
| R8 | 4.2, 8.1, 8.2 |
| R9 | 8.1, 8.2 |
| R10 | 5.1, 5.2 |
| R11 | 5.1, 5.2 |
| R12 | 5.1, 5.2, 5.3, 11.7 |
| R13 | 2.1, 2.2, 7.1 |
| R14 | 1.1, 7.2, 7.3, 7.4, 11.5 |
| R15 | 5.1, 5.2, 11.3 |
| R16 | 1.3, 9.1 |

---

## Definition of Done (checklist tasks)

- [x] Criterios **R1–R16** implementados y testeados en `ContactPageTest`.
- [x] `CONTACT_MAIL_TO` requerido/usado desde `config('ecommerce.contact.inbox')`, sin dirección hardcodeada.
- [x] `lang/{en,es}/contact.php` completo, tono tuteo/neutro (sin voseo).
- [x] Enlaces de contacto activos en header/nav móvil/footer; footer corregido de `/faqs` a `/faq`.
- [x] Envío de correo síncrono (no `ShouldQueue`), falla de transporte no pierde la posibilidad de contacto (banner + `mailto` + log).
- [x] Rate limiter propio (`ContactFormRateLimiter`, 3/600s por IP) sin reusar el de registro.
- [x] Batch B behavior-neutral: cero cambios de lógica sobre Batch A, `ContactPageTest` sigue en verde.
- [x] Auditoría `impeccable` ejecutada y sus hallazgos aplicados antes de cerrar Batch B (estática/código — sin navegador en vivo, ver Risks).
- [x] PHPUnit del alcance en verde vía Sail (419/419); Pint OK.
- [ ] `CONTACT_MAIL_TO`/`CONTACT_PUBLIC_EMAIL` en `.env.example`/`.env` — **pendiente**: bloqueado por permisos de sandbox en la sesión de Batch A; requiere acción manual del usuario antes de producción.

**Estado final:** Batch A + Batch B completos (39/39 tareas de implementación + auditoría). Único pendiente: adición manual de las 2 variables de entorno por el usuario (no bloquea tests ni Pint; el config resuelve a `null` de forma segura).
