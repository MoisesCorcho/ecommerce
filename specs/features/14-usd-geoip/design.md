# Moneda USD + detección por país — Design

> **Feature:** F14 · **Slug:** `14-usd-geoip`
> **Requisitos:** [`requirements.md`](requirements.md)
> **Tasks:** [`tasks.md`](tasks.md)

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Invariantes de dinero, carrito y pasarelas | skill `marketplace-security` |
| Convenciones de código | `.ai/guidelines/project-conventions.md` |
| Patrón de preferencia de visitante | [`13-i18n-locale-switcher/design.md`](../13-i18n-locale-switcher/design.md) |
| Cambio de moneda del carrito | `app/Actions/Cart/ChangeCartCurrencyAction.php` |

---

## Arquitectura

### El problema que resuelve

Antes había **dos nociones de moneda que no se hablaban**:

```
vitrina  → config('ecommerce.default_currency')   estática, igual para todos
carrito  → carts.currency                          conmutable, por carrito
```

Después hay **una sola**, resuelta por request y compartida:

```
                    ┌──────────────────────────┐
   cookie ────────► │  SetCurrency (web group) │
   CF-IPCountry ──► │  cookie › país › default │
   default_currency │  → session('currency')   │
                    └────────────┬─────────────┘
                                 │
                    ┌────────────▼─────────────┐
                    │  CurrentCurrency::get()  │
                    └────────────┬─────────────┘
                                 │
     ┌───────────────┬───────────┼───────────┬────────────────┐
 catalog-list  product-detail  quick-view  wishlist  featured-grid
                                 │
                          carts.currency  ◄── movido por POST /currency
```

### Capas

| Pieza | Ubicación | Rol |
|-------|-----------|-----|
| `CurrencyEnum` | `app/Enums/Commerce/` | Vocabulario + reglas de formato y proveedor |
| `CountryCurrencyMap` | `app/Support/Commerce/` | ISO 3166-1 alpha-2 → moneda de mercado |
| `CurrentCurrency` | `app/Support/Commerce/` | Lectura de la moneda vigente. Punto único para toda la vitrina |
| `SetCurrency` | `app/Http/Middleware/` | Resuelve y fija la moneda en sesión, una vez |
| `UpdateCurrencyRequest` | `app/Http/Requests/Commerce/` | Validación contra el enum |
| `UpdateCurrencyController` | `app/Http/Controllers/Commerce/` | Mueve carrito, persiste preferencia, `back()` |
| `x-currency-switcher` | `resources/views/components/` | Dropdown desktop + variante inline móvil |

---

## `CurrencyEnum` — lo que se arregló

`format()` era esto:

```php
if ($this === self::Eur) { /* ÷100, 2 decimales, € */ }
/* todo lo demás: entero, sin decimales, $ */
```

Un caso especial de EUR con el resto cayendo en la forma de COP. **USD habría mostrado 4999 centavos como "$ 4.999"** — cien veces mal, sin excepción, sin log, solo mal. Y con el mismo `$` que los pesos.

Ahora está dirigido por datos:

| Método | COP | EUR | USD |
|--------|-----|-----|-----|
| `minorUnits()` | 1 | 100 | 100 |
| `symbol()` | `$` | `€` | `US$` |
| `paymentProvider()` | Bold | Stripe | Stripe |

`format()` deriva los decimales de `minorUnits()`. Agregar una cuarta moneda es agregar tres filas de `match`, no reescribir la función. Y `label()` dejó de ser un `match` para resolver la clave dinámicamente (`enums.currency.{value}`), que es lo que la convención de i18n del repo pedía desde siempre.

> **Por qué `US$` y no `$`:** COP ya usa `$`. Con las dos monedas vivas, "$ 4.000" es ambiguo entre un bolso de cuatro mil pesos y uno de cuatro mil dólares. El prefijo cuesta dos caracteres y elimina la ambigüedad.

---

## Resolución de la moneda

`SetCurrency`, apéndice del grupo `web` (después de `StartSession`), fija la moneda en sesión **solo si todavía no hay una**:

1. **Cookie** → elección explícita previa. Gana siempre (R3/D6).
2. **Cabecera de país** → `CountryCurrencyMap::resolve()`.
3. **`default_currency`** → fallback.

`CountryCurrencyMap` devuelve `null` —no una moneda— cuando el código no es usable, para que el llamador decida el fallback en vez de que el mapa adivine. Descarta `XX` (cliente desconocido) y `T1` (salida Tor), que Cloudflare envía y no son países.

> **D2 en concreto:** la cabecera es configurable (`ECOMMERCE_COUNTRY_HEADER`). Hoy **no hay CDN adelante** y la cabecera no llega: la detección se saltea y manda el default. Cuando pongan Cloudflare, la feature empieza a detectar sin tocar una línea de código. No se agregó dependencia de GeoIP ni consulta HTTP externa: meter una llamada de red en el camino de cada visitante nuevo es cambiar un problema de configuración por un punto de falla permanente.

---

## Vitrina y carrito se mueven juntos

Esta es la decisión que más importa, y es de seguridad antes que de UX.

`UpdateCurrencyController` **mueve el carrito primero**. Si el carrito no puede moverse, la preferencia no se persiste y la vitrina no cambia:

```
moveCart()  ──► CartCurrencyChangeBlockedException ──► back()->withErrors(), nada cambió
     │
     └── OK ──► session + cookie ──► back()
```

`ChangeCartCurrencyAction` declara dos excepciones y **hay que tratarlas distinto**:

| Excepción | Naturaleza | Tratamiento |
|-----------|------------|-------------|
| `CartCurrencyChangeBlockedException` | dominio — una línea sin precio en la moneda pedida | Se captura, se muestra al visitante, no cambia nada |
| `CartAccessDeniedException` | **seguridad** — violación de propiedad de carrito | **No se captura.** Burbujea |

> Un `catch (Throwable)` genérico acá se tragaría la excepción de seguridad junto con la de dominio. La propiedad del carrito se asierta dentro de la Action (H9 de `marketplace-security`); descartar esa señal en silencio convierte un control en decoración.

**Por qué no se deja la vitrina en USD con el carrito en COP:** porque el comprador lee un precio en una moneda y paga en otra. Es preferible que no pueda cambiar de moneda con ese carrito, a que pueda y se lleve una sorpresa en el checkout.

---

## Cobertura de precios

`Product::scopePublishedForStorefront($currency)` ya exigía una fila de precio en la moneda pedida. Eso significa que **una moneda sin precios cargados no rompe: vacía el catálogo**, en silencio.

No se cambió ese comportamiento —es correcto: mejor no mostrar un producto que mostrarlo sin precio—, pero se documenta como el riesgo operativo número uno de la feature. El seeder y la factory cargan USD para datos de demostración; los precios reales de producción los define quien fija precios (D4).

---

## Testing

`tests/Unit/Commerce/CurrencyEnumTest.php` — el formato, que es donde estaba el dinero mal.

| Test | Cubre |
|------|-------|
| Unidades menores con dos decimales | R6 |
| COP sin decimales | R7 |
| USD no sale con `$` pelado | R8 |
| Toda moneda resuelve proveedor | R9 |
| Toda moneda resuelve etiqueta y símbolo | — (guarda contra un case nuevo sin traducción) |

`tests/Feature/Storefront/CurrencySwitcherTest.php` — la resolución y el endpoint.

| Test | Cubre |
|------|-------|
| Conmutación manual, cookie y redirección | R2 |
| Persistencia entre requests | R4 |
| Cookie gana sobre geografía | R3 |
| País → moneda para los tres mercados | R1 |
| Sin cabecera → default | R16 |
| `XX`, `T1`, vacío, `ZZZ`, `12` → default | R15 |
| Moneda no soportada / campo ausente | R13, R14 |
| **Línea sin precio bloquea todo el cambio** | R12, R17 |
| Navbar renderiza las tres monedas | R11 |

`tests/Browser/CurrencySwitcherTest.php` — Dusk, para el panel de Alpine que los tests de feature no ejecutan (R11).

---

## Dependencias

Ninguna nueva. Sin paquete de GeoIP, sin llamadas externas.

---

## Riesgos

| # | Riesgo | Mitigación |
|---|--------|------------|
| 1 | **USD sin precios vacía el catálogo en silencio** para ese mercado | Documentado en D4; el seeder cubre demo; producción exige carga explícita antes de anunciar el mercado |
| 2 | La detección no funciona hasta que haya CDN adelante | Aceptado y explícito (D2). La cabecera es configurable; el día que exista, funciona sin cambio de código |
| 3 | Un cupón de monto fijo en una moneda deja de aplicar al cambiar de moneda | Comportamiento preexistente de F06 (C7): los cupones de monto fijo exigen moneda coincidente. No se alteró |
| 4 | Los tests de Moisés usaban `USD` como ejemplo de moneda inválida | Se cambió el ejemplo a `GBP`, preservando la intención del test. Ver `tasks.md` 8.2 |
| 5 | Un cuarto mercado exigiría revisar `minorUnits`, `symbol` y `paymentProvider` | Los `match` son exhaustivos: el compilador obliga a completarlos, no fallan en runtime |
