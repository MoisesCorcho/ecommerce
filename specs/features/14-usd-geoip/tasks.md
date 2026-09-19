# Moneda USD + detección por país — Tasks

> **Feature:** F14 · **Slug:** `14-usd-geoip`
> **Requisitos:** [`requirements.md`](requirements.md)
> **Design:** [`design.md`](design.md)

---

## 1. Vocabulario y reglas de dinero

- [x] 1.1 Agregar el case `Usd = 'USD'` a `CurrencyEnum`. _(cubre R1, R2)_
- [x] 1.2 Agregar `minorUnits()` (COP 1, EUR/USD 100) como fuente de la precisión decimal. _(cubre R6, R7)_
- [x] 1.3 Agregar `symbol()` con `US$` para dólares, para desambiguar del `$` de COP. _(cubre R8)_
- [x] 1.4 Reescribir `format()` dirigido por `minorUnits()` en vez del caso especial de EUR. _(cubre R6, R7, R8)_
- [x] 1.5 Rutear USD a Stripe en `paymentProvider()`. _(cubre R9)_
- [x] 1.6 Convertir `label()` de `match` a clave dinámica `enums.currency.{value}`, como pide la convención de i18n. _(cubre R11)_
- [x] 1.7 Agregar `USD` a `lang/{en,es}/enums.php`. _(cubre R11)_

## 2. Envío

- [x] 2.1 Agregar el case USD a `ShippingCostService::standardCost()`, que era un `match` no exhaustivo. _(cubre R10)_
- [x] 2.2 Agregar `standard_cost_usd` al bloque `shipping` de `config/ecommerce.php`. _(cubre R10)_

## 3. Detección de país

- [x] 3.1 Crear `app/Support/Commerce/CountryCurrencyMap.php` con `CO` → COP, eurozona → EUR, resto → USD. _(cubre R1)_
- [x] 3.2 Incluir los 20 miembros del área euro más los 4 microestados con acuerdo monetario. _(cubre R1)_
- [x] 3.3 Devolver `null` ante código ausente, malformado, `XX` o `T1`, para que el llamador decida el fallback. _(cubre R15)_

## 4. Preferencia de moneda del visitante

- [x] 4.1 Agregar el bloque `currency_preference` a `config/ecommerce.php` con `cookie_name`, `cookie_lifetime` y `country_header`. _(cubre R3, R16)_
- [x] 4.2 Crear `app/Support/Commerce/CurrentCurrency.php` como punto único de lectura. _(cubre R5)_
- [x] 4.3 Crear `app/Http/Middleware/SetCurrency.php` con la cadena cookie → país → default. _(cubre R1, R3, R16)_
- [x] 4.4 Registrar el middleware en el grupo `web` de `bootstrap/app.php`, después de `StartSession`. _(cubre R1)_

## 5. Endpoint de conmutación

- [x] 5.1 Crear `UpdateCurrencyRequest` con `required` + `Rule::enum(CurrencyEnum::class)`. _(cubre R13, R14)_
- [x] 5.2 Crear `UpdateCurrencyController` invocable que mueva el carrito **antes** de persistir la preferencia. _(cubre R2, R12)_
- [x] 5.3 Capturar **solo** `CartCurrencyChangeBlockedException` y devolver `back()->withErrors()` sin cambiar nada. _(cubre R12)_
- [x] 5.4 **No** capturar `CartAccessDeniedException`: es señal de seguridad, no resultado de dominio. _(cubre R17)_
- [x] 5.5 Registrar `Route::post('/currency', ...)->name('currency.update')` en el grupo `web`, heredando CSRF. _(cubre R2)_

## 6. Unificación de la vitrina

- [x] 6.1 Recablear `catalog-list` de `config('ecommerce.default_currency')` a `CurrentCurrency::get()`. _(cubre R5)_
- [x] 6.2 Recablear `product-detail`. _(cubre R5)_
- [x] 6.3 Recablear `product-quick-view`. _(cubre R5)_
- [x] 6.4 Recablear `wishlist-page`. _(cubre R5)_
- [x] 6.5 Recablear `featured-products-grid`. _(cubre R5)_

## 7. Selector

- [x] 7.1 Crear `lang/{en,es}/commerce.php` con la copy del selector, en tuteo neutro para `es`. _(cubre R11)_
- [x] 7.2 Crear `resources/views/components/currency-switcher.blade.php` con variantes `dropdown` e `inline`. _(cubre R11)_
- [x] 7.3 Montar el dropdown en el navbar desktop y la variante inline en el menú móvil. _(cubre R11)_

## 8. Datos

- [x] 8.1 Derivar precios USD en `ProductSeeder` siguiendo el patrón existente de EUR, y agregar el estado `usd()` a `ProductVariantPriceFactory`. _(cubre R1, R5)_
- [x] 8.2 Actualizar los dos tests que usaban `USD` como ejemplo de moneda **no soportada** (`CartHttpTest`, `ProductAdminTest`), cambiando el ejemplo a `GBP`. Se preserva la intención original del test; no se elimina ninguno. _(cubre R13)_

## 9. Tests

- [x] 9.1 Crear `tests/Unit/Commerce/CurrencyEnumTest.php` cubriendo formato, símbolo, proveedor y completitud del enum. _(cubre R6–R9)_
- [x] 9.2 Crear `tests/Feature/Storefront/CurrencySwitcherTest.php` cubriendo resolución, precedencia, rechazos y bordes. _(cubre R1–R4, R11, R13–R16)_
- [x] 9.3 Cubrir que una línea de carrito sin precio bloquea el cambio completo, sin mover vitrina ni carrito. _(cubre R12, R17)_
- [x] 9.4 Crear `tests/Browser/CurrencySwitcherTest.php` (Dusk) para el panel de Alpine y la variante móvil. _(cubre R11)_

## 10. Cierre

- [x] 10.1 Correr `vendor/bin/sail bin pint --dirty`.
- [x] 10.2 Correr la suite completa y confirmar cero regresiones.
- [x] 10.3 Correr los Dusk.
- [x] 10.4 Verificar contra el servidor los tres mercados por cabecera de país y la sobrescritura manual.
- [x] 10.5 Marcar los checkboxes y actualizar el estado en `requirements.md`.
