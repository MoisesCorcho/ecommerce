# Selector de idioma (i18n Front) — Tasks

> **Feature:** F13 · **Slug:** `13-i18n-locale-switcher`
> **Requisitos:** [`requirements.md`](requirements.md)
> **Design:** [`design.md`](design.md)

---

## 1. Vocabulario de dominio

- [x] 1.1 Crear `app/Enums/Localization/LocaleEnum.php` como enum backed de string con los casos `Es = 'es'` y `En = 'en'`, implementando `HasLabel`. _(cubre R1, R2, R12)_
- [x] 1.2 Implementar `label()` y `getLabel()` devolviendo el nombre nativo hardcodeado ("Español", "English"), sin pasar por `__()`. Documentar en PHPDoc por qué es la excepción a la convención de enums. _(cubre R9)_
- [x] 1.3 Implementar `tryFromValid(?string $value): ?self` para resolución tolerante desde sesión y cookie. _(cubre R14)_

## 2. Configuración

- [x] 2.1 Agregar bloque `locale` a `config/ecommerce.php` con `cookie_name` y `cookie_lifetime`, siguiendo el estilo de comentarios de los bloques existentes. _(cubre R4)_

## 3. Middleware de resolución

- [x] 3.1 Crear `app/Http/Middleware/SetLocale.php` con la cadena de resolución sesión → cookie → `config('app.locale')`. _(cubre R3, R4, R5, R6)_
- [x] 3.2 Re-sembrar en sesión el idioma resuelto desde cookie, para que la request siguiente resuelva en un solo salto. _(cubre R4)_
- [x] 3.3 Descartar en silencio valores no reconocidos en sesión o cookie, cayendo al siguiente escalón sin excepción. _(cubre R14)_
- [x] 3.4 Registrar el middleware en el grupo `web` de `bootstrap/app.php` con `appendToGroup`, garantizando que corra después de `StartSession`. _(cubre R3, R18)_

## 4. Endpoint de conmutación

- [x] 4.1 Crear `app/Http/Requests/Localization/UpdateLocaleRequest.php` con regla `required` + `Rule::enum(LocaleEnum::class)`. _(cubre R12, R13)_
- [x] 4.2 Crear `app/Http/Controllers/Localization/UpdateLocaleController.php` invocable que persista en sesión y encole la cookie. _(cubre R1, R2, R4)_
- [x] 4.3 Devolver `back()` para retornar a la URL de origen. _(cubre R1, R2)_
- [x] 4.4 Registrar `Route::post('/locale', UpdateLocaleController::class)->name('locale.update')` en `routes/web.php`, dentro del grupo `web` para heredar CSRF. _(cubre R1, R15)_

## 5. Internacionalización del propio selector

- [x] 5.1 Crear `lang/en/locale.php` con la etiqueta accesible del selector y el título del panel. _(cubre R16)_
- [x] 5.2 Crear `lang/es/locale.php` con las traducciones, en **tuteo neutro**. _(cubre R16)_

## 6. Componente del selector

- [x] 6.1 Crear `resources/views/components/locale-switcher.blade.php` como componente Blade anónimo con estado Alpine `open`. _(cubre R7)_
- [x] 6.2 Implementar el botón disparador con el nombre nativo del idioma activo, `aria-haspopup`, `:aria-expanded` y `aria-label` localizado. _(cubre R9, R16)_
- [x] 6.3 Implementar el panel con un `<form method="POST">` + `@csrf` + input oculto por cada idioma soportado. _(cubre R1, R2, R15)_
- [x] 6.4 Marcar el idioma activo con `aria-current` y estilo diferenciado. _(cubre R16)_
- [x] 6.5 Agregar cierre por `escape` y por clic fuera; asegurar recorrido por teclado vía botones nativos. _(cubre R17)_
- [x] 6.6 Aplicar tokens de diseño existentes del navbar (colores, tipografía, transiciones). Sin banderas de países. _(cubre R7, R9)_

## 7. Montaje en el layout

- [x] 7.1 Montar `<x-locale-switcher />` en el bloque derecho del navbar desktop de `resources/views/layouts/storefront.blade.php`, antes del ícono de wishlist. _(cubre R7)_
- [x] 7.2 Montar el selector dentro del `nav` desplegable móvil, al final del listado de enlaces. _(cubre R8)_
- [x] 7.3 Verificar que el `<html lang>` existente (línea 2) refleje el idioma activo sin cambios adicionales. _(cubre R18)_

## 8. Traducciones de framework faltantes

- [x] 8.1 Crear `lang/es/validation.php` traduciendo desde `lang/en/validation.php` del propio repo, con paridad exacta de claves y tono tuteo neutro. _(cubre R10)_
- [x] 8.2 Crear `lang/es/pagination.php` traduciendo desde `lang/en/pagination.php`. _(cubre R11)_
- [x] 8.3 Verificar paridad de claves entre `lang/es` y `lang/en` en todos los dominios; reportar faltantes preexistentes en lugar de completarlos por cuenta propia. _(cubre R10, R11)_

## 9. Tests

- [x] 9.1 Crear `tests/Feature/Storefront/LocaleSwitcherTest.php` (PHPUnit). _(cubre R1–R18)_
- [x] 9.2 Cubrir conmutación a `es` y a `en` con redirección al origen. _(cubre R1, R2)_
- [x] 9.3 Cubrir persistencia entre requests. _(cubre R3)_
- [x] 9.4 Cubrir restauración desde cookie y precedencia de sesión sobre cookie. _(cubre R4, R5)_
- [x] 9.5 Cubrir el fallback a `APP_LOCALE` sin preferencia previa. _(cubre R6)_
- [x] 9.6 Cubrir rechazo de idioma no soportado y de request sin campo. _(cubre R12, R13)_
- [x] 9.7 Cubrir cookie con valor inválido ignorada sin excepción. _(cubre R14)_
- [x] 9.8 Cubrir render del selector en el navbar con ambas opciones y nombres nativos. _(cubre R7, R9)_
- [x] 9.9 Cubrir que `<html lang>` refleje el idioma activo. _(cubre R18)_
- [x] 9.10 Crear `tests/Browser/LocaleSwitcherTest.php` (Dusk) para la interacción real de Alpine, que los tests de feature no ejercitan: apertura del panel, conmutación por clic, cierre con `escape` y variante inline en móvil. _(cubre R7, R8, R17)_

## 10. Cierre

- [x] 10.1 Correr `vendor/bin/sail bin pint --dirty` sobre los archivos PHP tocados.
- [x] 10.2 Correr la suite completa (`vendor/bin/sail artisan test --compact`) y confirmar que no hay regresiones en los tests que asumen inglés.
- [x] 10.3 Verificar en navegador que el selector conmuta y persiste en desktop y móvil.
- [x] 10.4 Marcar los checkboxes de este archivo y actualizar el estado en `requirements.md`.
