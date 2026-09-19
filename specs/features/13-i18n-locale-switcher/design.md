# Selector de idioma (i18n Front) — Design

> **Feature:** F13 · **Slug:** `13-i18n-locale-switcher`
> **Requisitos:** [`requirements.md`](requirements.md)
> **Tasks:** [`tasks.md`](tasks.md)

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Convenciones de código (Enums, controllers finos, tipado) | `.ai/guidelines/project-conventions.md` |
| Reglas de i18n (claves cortas, dominios, tono `es`) | `.ai/guidelines/project-conventions.md` § Internationalization |
| Tokens de diseño | [`specs/ui-briefs/00-design-tokens.md`](../../ui-briefs/00-design-tokens.md) |
| Layout storefront | `resources/views/layouts/storefront.blade.php` |
| Controller invocable de referencia | `app/Http/Controllers/Auth/LogoutController.php` |
| Enum de referencia (backed + `label()` vía `__()`) | `app/Enums/Commerce/CurrencyEnum.php` |

---

## Arquitectura

### Flujo completo

```
┌──────────────┐   POST /locale       ┌────────────────────────┐
│  Navbar      │ ───(locale=es)─────► │ UpdateLocaleRequest    │  valida contra LocaleEnum
│  x-locale-   │                      └───────────┬────────────┘
│  switcher    │                                  │ validado
└──────────────┘                                  ▼
                                      ┌────────────────────────┐
                                      │ UpdateLocaleController │  session()->put + Cookie::queue
                                      └───────────┬────────────┘
                                                  │ back()
                                                  ▼
        ┌──────────────────────────────────────────────────────────┐
        │  SetLocale (middleware, grupo web)                        │
        │  1. sesión        → si es válido, se usa                  │
        │  2. cookie        → si es válido, se usa y se re-siembra  │
        │  3. APP_LOCALE    → fallback                              │
        │  App::setLocale($locale)                                  │
        └──────────────────────────────────────────────────────────┘
                                                  │
                                                  ▼
                        <html lang="{{ app()->getLocale() }}">  (ya existente, línea 2)
```

### Capas

| Pieza | Ubicación | Rol |
|-------|-----------|-----|
| `LocaleEnum` | `app/Enums/Localization/LocaleEnum.php` | Vocabulario fijo de idiomas soportados. Fuente de verdad única. |
| `SetLocale` | `app/Http/Middleware/SetLocale.php` | Resuelve el idioma por request y lo aplica. Registrado en el grupo `web`. |
| `UpdateLocaleRequest` | `app/Http/Requests/Localization/UpdateLocaleRequest.php` | Validación en el borde contra el enum. |
| `UpdateLocaleController` | `app/Http/Controllers/Localization/UpdateLocaleController.php` | Invocable. Persiste en sesión + cookie y devuelve `back()`. |
| `x-locale-switcher` | `resources/views/components/locale-switcher.blade.php` | Componente Blade anónimo. Dropdown Alpine.js. |
| `lang/{es,en}/locale.php` | dominio i18n | Copy del selector (etiqueta accesible, título). |

### Por qué no hay Action ni DTO

Las convenciones del repo reservan **Action** para un caso de uso de dominio y **DTO** para cruces de frontera con payloads no triviales. Conmutar idioma no toca dominio ni persistencia: es una preferencia de presentación de un solo campo ya validado. Aplica el *escape hatch* documentado ("trivial one-off ... may stay simple until a second caller or real domain rules appear"). El precedente en el repo es `LogoutController`: invocable, sin Action, sin DTO.

Si más adelante la preferencia pasa a persistirse en la tabla `users` o a coordinarse con la moneda, ahí sí aparece un caso de uso real y se extrae una Action.

---

## `LocaleEnum`

```php
enum LocaleEnum: string implements HasLabel
{
    case Es = 'es';
    case En = 'en';
}
```

| Método | Devuelve | Nota |
|--------|----------|------|
| `label()` | `'Español'` / `'English'` | **Nombre nativo, sin pasar por `__()`.** Ver D5/D9. |
| `getLabel()` | idem `label()` | Contrato `HasLabel`, igual que `CurrencyEnum`. |
| `tryFromValid(?string)` | `?self` | Helper de resolución tolerante para sesión y cookie (R14). |

### D9 — Por qué `label()` NO usa `__()`

`CurrencyEnum::label()` sí resuelve vía `__('enums.currency.COP')`, y la convención del repo dice que las etiquetas de enum nunca se hardcodean. **Este enum es la excepción deliberada**, y la razón es funcional: si el nombre del idioma se tradujera al idioma activo, un visitante que cayó en inglés por defecto vería la opción como "Spanish". El usuario que busca su idioma busca la palabra *en su idioma*. Traducirla rompe justamente al usuario que más necesita el selector.

Es el mismo criterio que aplican Wikipedia, Airbnb y Booking. Se documenta acá porque contradice una convención visible del repo, y lo que contradice una convención se explica o se lee como error.

---

## Middleware `SetLocale`

Registrado en `bootstrap/app.php` mediante `$middleware->appendToGroup('web', SetLocale::class)`, para que corra después de `StartSession` y tenga la sesión disponible.

Orden de resolución (R5, R4, R6, R14):

1. `session('locale')` → si `LocaleEnum::tryFrom` da un caso, se usa.
2. `Cookie` de idioma → si es válido, se usa **y se re-siembra en sesión**, así la resolución posterior es de un solo salto.
3. `config('app.locale')` → fallback. No se persiste nada.

Cualquier valor no reconocido en sesión o cookie se descarta en silencio: `tryFrom` devuelve `null` y se cae al siguiente escalón. Sin excepciones, sin logs de ruido.

---

## Endpoint

| Aspecto | Decisión |
|---------|----------|
| Verbo y ruta | `POST /locale`, nombre `locale.update` |
| Grupo | `web` — hereda CSRF (R15) |
| Validación | `UpdateLocaleRequest`: `required` + `Rule::enum(LocaleEnum::class)` |
| Persistencia | `session()->put('locale', $value)` + `Cookie::queue(...)` |
| Respuesta | `back()` — vuelve al origen (R8/D8) |

### Cookie

| Atributo | Valor | Razón |
|----------|-------|-------|
| Nombre | `locale` (configurable) | Legible y estable |
| Vigencia | 1 año (`525600` minutos) | Preferencia de largo plazo |
| `httpOnly` | `true` | Ningún JS del sitio necesita leerla; la lee el middleware |
| `secure` | según entorno | Laravel lo resuelve por `session.secure` |
| `sameSite` | `lax` | Permite que sobreviva a la navegación entrante normal |

> **Nota de privacidad:** es una cookie de preferencia estrictamente funcional. No identifica al visitante ni se usa para seguimiento, así que no requiere consentimiento previo bajo GDPR/ePrivacy. Si a futuro se agrega un banner de cookies, esta va en la categoría "necesarias".

---

## Componente `x-locale-switcher`

Componente Blade **anónimo** (sin clase), consistente con el resto de `resources/views/components/`.

### Estructura

- Contenedor `x-data` de Alpine con estado `open`.
- Botón disparador: muestra el `label()` del idioma activo + chevron. `aria-haspopup="listbox"`, `:aria-expanded="open"`, `aria-label` localizado desde `lang/{locale}/locale.php` (R16).
- Panel: un `<form method="POST" action="{{ route('locale.update') }}">` por idioma, con `@csrf` e `<input type="hidden" name="locale">`, y un `<button type="submit">` por opción.
- El idioma activo se marca con `aria-current="true"` y estilo diferenciado (R16).
- `x-on:keydown.escape` cierra; `x-on:click.outside` cierra. El recorrido de opciones es nativo por tabulación de botones (R17).

### Por qué formularios y no enlaces

Cambiar idioma **muta estado del servidor**, y mutar estado por `GET` es incorrecto: lo hace cacheable por proxies, prefetcheable por el navegador y disparable desde un `<img src>` ajeno. Con `POST` + CSRF queda protegido (R15). Cuesta un poco más de markup y se banca sin JavaScript.

### Montaje (R7, R8)

| Ubicación | Punto de inserción |
|-----------|--------------------|
| Desktop | Bloque derecho del navbar, `div.flex.flex-1.justify-end`, antes del ícono de wishlist |
| Móvil | Dentro del `nav` desplegable, al final del listado de enlaces |

### Visual

Se apoya en los tokens existentes: texto `text-intense-cocoa`, hover `hover:text-soft-gold`, panel `bg-soft-sand` con `border-intense-cocoa/10`, tipografía `text-label-caps uppercase tracking-widest`. Mismas transiciones `duration-300` que el resto del navbar. Sin banderas (D6).

---

## Traducciones de framework faltantes

`lang/en/` tiene `validation.php` y `pagination.php`; `lang/es/` **no**. Hoy no se nota porque la app corre en inglés. Con el selector activo, un visitante en español vería los errores de validación y la paginación en inglés (R10, R11).

| Archivo | Origen | Tono |
|---------|--------|------|
| `lang/es/validation.php` | Se traduce a partir de `lang/en/validation.php` para que las claves coincidan exactamente | **Tuteo neutro** — "verifica", "ingresa", "tu". Nunca voseo, por convención del repo |
| `lang/es/pagination.php` | idem | idem |

> Estos archivos se traducen a partir de los del propio repo, no de una copia externa, para que ninguna clave quede huérfana si Moisés ya personalizó alguna.

---

## Testing

`tests/Feature/Storefront/LocaleSwitcherTest.php` (PHPUnit, no Pest — convención del repo).

| Test | Cubre |
|------|-------|
| Conmuta a español y redirige al origen | R1, D8 |
| Conmuta a inglés desde español | R2 |
| El idioma persiste entre requests | R3 |
| Restaura desde cookie sin sesión | R4 |
| La sesión gana sobre la cookie | R5 |
| Sin preferencia usa `APP_LOCALE` | R6 |
| Rechaza idioma no soportado | R12 |
| Rechaza request sin campo `locale` | R13 |
| Cookie con valor inválido se ignora | R14 |
| El navbar renderiza el selector con ambas opciones | R7, R9 |
| `<html lang>` refleja el idioma activo | R18 |

CSRF (R15) queda cubierto por pertenecer al grupo `web`; los tests de feature deshabilitan el middleware de CSRF por defecto, así que no se testea ahí — se verifica por inspección de que la ruta no esté en la lista de excepciones de `preventRequestForgery`.

### Dusk — lo que los tests de feature no alcanzan

`tests/Browser/LocaleSwitcherTest.php`. Los tests de feature renderizan el markup pero no ejecutan Alpine: confirman que los formularios existen y que el backend responde, no que el panel abra. Eso se cubre acá.

| Test | Cubre |
|------|-------|
| El panel arranca colapsado, abre al clic y conmuta | R7 |
| La elección sobrevive a la navegación | R3 |
| `escape` cierra el panel | R17 |
| El menú móvil conmuta sin dropdown anidado | R8 |

> **Gotcha:** Dusk reutiliza el browser entre tests de una misma clase, así que la cookie escrita por un test se filtra al siguiente y lo hace arrancar en el idioma equivocado. Cada test empieza con `deleteAllCookies()` sobre el dominio ya cargado.

---

## Dependencias

Ninguna nueva. Sin paquetes de terceros: Laravel ya trae localización, sesión y cookies. La convención del repo lo dice explícitamente ("No third-party i18n package for app strings").

---

## Riesgos

| # | Riesgo | Mitigación |
|---|--------|------------|
| 1 | Cobertura de traducción despareja: `lang/es` y `lang/en` podrían tener claves faltantes que hoy nadie nota porque la app corre en inglés | Verificar paridad de claves entre ambos árboles antes de cerrar la feature; reportar faltantes a Moisés en lugar de inventar copy |
| 2 | Tests existentes que asumen inglés podrían romper si el default cambia | El default **no** cambia: `APP_LOCALE` sigue mandando cuando no hay preferencia (R6) |
| 3 | El `lang` del `<html>` es correcto pero las páginas no declaran alternativas (`hreflang`) | Aceptado: sin prefijo de URL no hay URLs alternativas que declarar (D2) |
| 4 | La copy de `lang/es` podría salir en voseo por contagio del tono de trabajo | Convención explícita en `project-conventions.md`; se revisa en el diff antes de commitear |
