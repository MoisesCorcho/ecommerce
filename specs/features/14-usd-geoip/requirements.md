# Moneda USD + detección por país — Requirements

> **Estado:** Completa
> **ID:** F14 · **Slug:** `14-usd-geoip` (expansión de mercado, fuera de la secuencia F0N del roadmap)
> **Prerequisitos:** [`13-i18n-locale-switcher`](../13-i18n-locale-switcher/requirements.md) — comparte el patrón de preferencia de visitante
> **Desbloquea:** —

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Invariantes de dinero y pagos | skill `marketplace-security` § Payments & money invariants |
| Convenciones de código y enums | `.ai/guidelines/project-conventions.md` |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Patrón de preferencia de visitante | [`13-i18n-locale-switcher/design.md`](../13-i18n-locale-switcher/design.md) |
| Cambio de moneda del carrito | `ChangeCartCurrencyAction`, `CartCurrencyChangeBlockedException` |
| Pagos y ruteo por moneda | [`05-payments`](../05-payments/) |

> **Nota de partida — el estado real, que no coincide con lo que se asumía:**
>
> 1. **La vitrina estaba clavada en una sola moneda para todos.** Cinco componentes (`catalog-list`, `product-detail`, `product-quick-view`, `wishlist-page`, `featured-products-grid`) leían `config('ecommerce.default_currency')` de forma estática. No existía ninguna preferencia de moneda por visitante.
> 2. **El carrito sí tenía la suya**, en `carts.currency`, conmutable por `POST /cart/currency`. Vitrina y carrito podían mostrar monedas distintas al mismo tiempo.
> 3. **`CurrencyEnum::format()` era incorrecto para cualquier moneda de unidades menores que no fuera EUR**: hacía un caso especial de EUR y todo lo demás caía en la rama de COP. USD habría mostrado 4999 centavos como "$ 4.999" en lugar de "US$ 49,99" — cien veces mal, sin excepción.
> 4. **Tres `match` no exhaustivos** (`ShippingCostService::standardCost`, `CurrencyEnum::label`, `CurrencyEnum::paymentProvider`) habrían tirado `UnhandledMatchError` al agregar el case.
> 5. **No hay Cloudflare.** Ni staging ni el dominio de producción están detrás de un CDN: `leen.moisescorchodev.tech` responde `server: nginx` sin `cf-ray`, y `leenhandbags.com` sigue parkeado. La cabecera `CF-IPCountry` que se propuso originalmente no llega hoy.

## User stories

1. **Como** visitante de Estados Unidos, **quiero** ver los precios en dólares, **para** saber cuánto voy a pagar sin hacer conversiones mentales.
2. **Como** visitante colombiano, **quiero** que la tienda arranque en pesos, **para** ver el precio en mi moneda desde el primer segundo.
3. **Como** visitante europeo, **quiero** ver euros, **para** lo mismo.
4. **Como** visitante, **quiero** poder elegir la moneda a mano, **para** sobrescribir lo que el sitio dedujo de mi ubicación.
5. **Como** visitante, **quiero** que mi moneda elegida siga vigente cuando vuelva, **para** no reconfigurarla cada visita.
6. **Como** visitante, **quiero** que el carrito esté siempre en la misma moneda que la vitrina, **para** no leer un precio y que me cobren otro.
7. **Como** comprador, **quiero** que los dólares se muestren distinguibles de los pesos, **para** no confundir un bolso de 4.000 pesos con uno de 4.000 dólares.

## Alcance de esta feature

**Incluye:**

- `CurrencyEnum::Usd`, ruteado a Stripe como proveedor de pago.
- Corrección de `format()` para que sea dirigida por datos (`minorUnits()`) en vez de un caso especial de EUR.
- Símbolo `US$` para dólares, para desambiguar del `$` de COP.
- Preferencia de moneda por visitante en sesión + cookie, con detección por cabecera de país.
- `CountryCurrencyMap`: `CO` → COP, eurozona (24 países) → EUR, resto → USD.
- Unificación: los 5 componentes de vitrina pasan a leer la preferencia; la vitrina y el carrito se mueven juntos.
- Endpoint `POST /currency` y selector en navbar desktop y menú móvil.
- Costo de envío en USD (`standard_cost_usd`).
- Precios USD en el seeder y estado `usd()` en la factory.

**No incluye (diferido):**

- Conversión automática por tipo de cambio. Ver D3.
- Precios reales en USD del catálogo de producción. Ver D4.
- Cualquier moneda más allá de COP, EUR y USD.
- Infraestructura de Cloudflare — es trabajo de despliegue, no de aplicación. Ver D2.
- Impuestos, aranceles o envío internacional diferenciado por país.

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | Una sola moneda activa | Vitrina y carrito **siempre** en la misma moneda. Si el carrito no puede moverse, la vitrina tampoco se mueve. Un estado partido permitiría leer un precio en dólares y pagar en pesos. |
| D2 | Fuente del país | Una **cabecera configurable** (`ECOMMERCE_COUNTRY_HEADER`, por defecto `CF-IPCountry`). Sin Cloudflare la cabecera no llega, la detección se saltea sin error y manda `default_currency`. No se agregó ninguna dependencia de GeoIP ni llamada externa: una consulta de red en el camino de cada visitante nuevo es un punto de falla que no compensa. |
| D3 | Sin conversión automática | Los precios se cargan explícitamente por moneda en `product_variant_prices`. No hay tipo de cambio en tiempo real. Un precio de catálogo que fluctúa con el mercado es una decisión comercial que nadie pidió. |
| D4 | Cobertura de precios | Un producto sin precio en la moneda activa **no aparece** en el catálogo (`publishedForStorefront` ya funcionaba así). Los precios USD de producción los define quien fija precios; el seeder los deriva solo para datos de demostración. |
| D5 | Símbolo del dólar | `US$`, no `$`. COP ya usa `$`, y un signo sin calificar deja al comprador sin saber qué está mirando. |
| D6 | Precedencia | Elección manual (cookie) **gana** sobre geografía. Una preferencia explícita del pasado vale más que desde dónde se conecta hoy. |
| D7 | Códigos de país no usables | `XX` (desconocido) y `T1` (salida Tor) de Cloudflare, y cualquier cosa que no sean dos letras, se descartan y caen al default. |
| D8 | Eurozona | Los 20 miembros del área euro más Andorra, Mónaco, San Marino y Ciudad del Vaticano, que lo usan por acuerdo monetario. |

---

## Criterios de aceptación (EARS)

### Happy path

### R1 — Moneda por país

CUANDO un visitante sin preferencia previa accede con una cabecera de país reconocida,
EL SISTEMA DEBE aplicar la moneda de ese mercado (`CO` → COP, eurozona → EUR, resto → USD),
a toda la vitrina.

### R2 — Elección manual

CUANDO un visitante envía `POST /currency` con una moneda soportada,
EL SISTEMA DEBE aplicarla a la vitrina y al carrito,
y DEBE redirigir a la URL de origen.

### R3 — La elección gana sobre la geografía

CUANDO existe una cookie de moneda y la cabecera de país indica otro mercado,
EL SISTEMA DEBE aplicar la de la **cookie**.

### R4 — Persistencia

CUANDO el visitante ya eligió moneda y navega a otra página,
EL SISTEMA DEBE mantenerla,
SIN requerir que la vuelva a elegir.

### R5 — Vitrina unificada

CUANDO la moneda activa cambia,
EL SISTEMA DEBE reflejarla en catálogo, ficha de producto, vista rápida, destacados y lista de deseos,
SIN que ninguna superficie quede en la moneda anterior.

### R6 — Formato de unidades menores

CUANDO se muestra un monto en una moneda con unidades menores (EUR, USD),
EL SISTEMA DEBE dividirlo por 100 y mostrarlo con dos decimales.

### R7 — Formato sin unidades menores

CUANDO se muestra un monto en COP,
EL SISTEMA DEBE mostrarlo como entero, sin decimales.

### R8 — Símbolo distinguible

CUANDO se muestra un monto en USD,
EL SISTEMA DEBE prefijarlo con `US$`,
para que no se confunda con el `$` de COP.

### R9 — Ruteo de pasarela

CUANDO se inicia un pago en USD,
EL SISTEMA DEBE rutearlo a Stripe,
igual que EUR, y SIN que el cliente pueda elegir proveedor.

### R10 — Envío en USD

CUANDO se calcula el envío estándar con USD activo,
EL SISTEMA DEBE tomarlo de `ecommerce.shipping.standard_cost_usd`.

### R11 — Selector visible

CUANDO el storefront se renderiza,
EL SISTEMA DEBE mostrar el selector de moneda en el navbar desktop y en el menú móvil,
con la moneda activa indicada.

---

### Errores y bordes

### R12 — Carrito que no puede moverse

CUANDO el visitante pide una moneda en la que alguna línea de su carrito no tiene precio,
EL SISTEMA DEBE rechazar el cambio completo y mostrar el error,
SIN mover la vitrina y SIN mover el carrito.

### R13 — Moneda no soportada

CUANDO se envía `POST /currency` con un valor que no corresponde a una moneda soportada,
EL SISTEMA DEBE rechazar la solicitud con error de validación,
SIN modificar la preferencia vigente.

### R14 — Solicitud sin moneda

CUANDO se envía `POST /currency` sin el campo `currency`,
EL SISTEMA DEBE rechazar la solicitud con error de validación.

### R15 — País no usable

CUANDO la cabecera de país trae `XX`, `T1`, vacío o algo que no sean dos letras,
EL SISTEMA DEBE ignorarla y aplicar `default_currency`,
SIN lanzar excepción.

### R16 — Sin cabecera de país

CUANDO la cabecera de país no está presente (hoy: no hay CDN adelante),
EL SISTEMA DEBE aplicar `default_currency`,
SIN intentar ninguna consulta externa.

### R17 — Propiedad del carrito

CUANDO el cambio de moneda alcanza al carrito,
EL SISTEMA DEBE asertar la propiedad del carrito dentro de la Action,
y una violación de propiedad NO DEBE tratarse como resultado de dominio ni descartarse en silencio.

---

## Decisiones y Ajustes de Alcance

### D-F14-01 — Restricción Temporal de USD en Vitrina (Pre-Lanzamiento)

Para la salida inicial a producción, el negocio decide desactivar temporalmente `USD` para clientes públicos (vitrina, selector, peticiones HTTP y checkout), operando exclusivamente con `COP` y `EUR`.
* `USD` permanece 100% soportado por la arquitectura y el modelo de datos en [`CurrencyEnum::Usd`](file:///home/moises/programation_projects/test/marketplace/app/Enums/Commerce/CurrencyEnum.php).
* La vitrina y puntos de entrada restringen la oferta exclusivamente a los casos devueltos por `CurrencyEnum::storefrontCases()`.
* La reactivación es inmediata mediante código de dominio.


