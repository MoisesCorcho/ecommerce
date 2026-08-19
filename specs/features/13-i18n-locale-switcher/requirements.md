# Selector de idioma (i18n Front) — Requirements

> **Estado:** Completa
> **ID:** F13 · **Slug:** `13-i18n-locale-switcher` (capacidad transversal de storefront, fuera de la secuencia F0N del roadmap)
> **Prerequisitos:** Ninguno — el layout de storefront y los dominios de traducción ya existen
> **Desbloquea:** Selector de moneda en navbar (comparte el patrón de preferencia de usuario)

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Convenciones de i18n (claves, dominios, tono `es`) | `.ai/guidelines/project-conventions.md` § Internationalization |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Layout storefront (navbar desktop + dropdown móvil) | `resources/views/layouts/storefront.blade.php` |
| Patrón de preferencia conmutable | `CartController::updateCurrency` + `ChangeCartCurrencyAction` |
| Config de dominio existente | `config/ecommerce.php` |

> **Nota de partida:** la aplicación arranca en `APP_LOCALE=en` (`config/app.php:81`) y **no existe ningún mecanismo de conmutación**: `bootstrap/app.php` no registra middleware de locale, y no hay ruta, componente ni preferencia persistida. Los dominios de traducción `lang/es/*` y `lang/en/*` sí existen y están completos salvo dos archivos de framework (ver R11 y R12).

## User stories

1. **Como** visitante hispanohablante, **quiero** cambiar la tienda a español, **para** entender el catálogo y el checkout en mi idioma.
2. **Como** visitante angloparlante, **quiero** cambiar la tienda a inglés, **para** comprar sin barrera idiomática.
3. **Como** visitante, **quiero** que mi idioma elegido siga vigente cuando vuelva otro día, **para** no tener que reconfigurarlo en cada visita.
4. **Como** visitante, **quiero** ver claramente cuál es el idioma activo, **para** saber si necesito cambiarlo.
5. **Como** visitante, **quiero** que al cambiar de idioma me deje en la misma página, **para** no perder el contexto de navegación.
6. **Como** visitante en móvil, **quiero** acceder al selector desde el menú desplegable, **para** tener la misma capacidad que en desktop.
7. **Como** visitante, **quiero** que los mensajes de error de los formularios estén en mi idioma, **para** entender qué corregir.
8. **Como** visitante que usa lector de pantalla, **quiero** que el selector anuncie su propósito y estado, **para** poder operarlo.

## Alcance de esta feature

**Incluye:**

- `LocaleEnum` con los idiomas soportados (`es`, `en`) como vocabulario de dominio.
- Middleware `SetLocale` en el grupo `web` que resuelve y aplica el idioma por request.
- Persistencia de la preferencia en **sesión + cookie** (sin tocar la base de datos).
- Endpoint `POST /locale` con Form Request de validación y retorno a la página de origen.
- Componente Blade `x-locale-switcher` montado en navbar desktop y en el menú desplegable móvil.
- Traducciones de framework faltantes: `lang/es/validation.php` y `lang/es/pagination.php`.
- Tests de feature que cubren conmutación, persistencia, rechazo de valores inválidos y render del selector.

**No incluye (diferido):**

- Prefijo de idioma en la URL (`/es/products`) y las etiquetas `hreflang` asociadas. Ver D2.
- Traducción de contenido de base de datos (nombres de producto, descripciones, categorías). Sigue fuera de alcance por convención del repo.
- Detección automática de idioma por cabecera `Accept-Language` o por GeoIP. Ver D7.
- Persistencia de la preferencia en la tabla `users` para el usuario autenticado. Ver D3.
- Idiomas adicionales más allá de `es` y `en`.
- Localización del panel de Filament (ya sigue `APP_LOCALE` por configuración del operador).

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | Persistencia | **Sesión + cookie.** La sesión resuelve la request actual; la cookie (1 año, `httpOnly`) sobrevive a la expiración de sesión (120 min por defecto) para que el visitante recurrente no vuelva a inglés. Sin migración ni columna nueva. |
| D2 | Sin prefijo de URL | Se descarta `/es/products`. Obligaría a reescribir las ~30 rutas, romper cada llamada a `route()` y ajustar buena parte de la suite. El beneficio es SEO multi-idioma, que hoy no es un objetivo declarado del proyecto. Queda como migración posible a futuro, no como punto de partida. |
| D3 | Sin columna en `users` | La cookie ya cubre al visitante recurrente, autenticado o no, y además resuelve el caso invitado —que es la mayoría del tráfico de una tienda. Una columna `users.locale` agregaría una migración sobre una tabla existente sin resolver nada que la cookie no resuelva. |
| D4 | Idiomas soportados | `es` y `en`, declarados en `LocaleEnum`. La fuente de verdad es el enum, no una lista suelta en config, siguiendo la convención de vocabularios fijos del repo. |
| D5 | Etiqueta del selector | Cada idioma se muestra **en su propio idioma** ("Español", "English"), nunca traducido al idioma activo. Es el estándar de la industria: quien no entiende el idioma actual igual reconoce el suyo. |
| D6 | Sin banderas | No se usan banderas de países como íconos de idioma. Un idioma no es un país: el español no es de España, y el inglés no es del Reino Unido. Se usa texto o código de idioma. |
| D7 | Sin autodetección en esta entrega | El primer ingreso usa `APP_LOCALE`. La detección por `Accept-Language` se evalúa junto con la autodetección de moneda por GeoIP (F14), para no construir dos mecanismos de detección distintos y desalineados. |
| D8 | Retorno a origen | Tras conmutar, el visitante vuelve a la URL desde la que disparó el cambio (`back()`), no al home. |

---

## Criterios de aceptación (EARS)

### Happy path

### R1 — Conmutación a español

CUANDO un visitante envía `POST /locale` con `locale=es`,
EL SISTEMA DEBE aplicar el idioma español a la respuesta y a las requests siguientes,
y DEBE redirigir a la URL de origen.

### R2 — Conmutación a inglés

CUANDO un visitante con el idioma español activo envía `POST /locale` con `locale=en`,
EL SISTEMA DEBE aplicar el idioma inglés a la respuesta y a las requests siguientes,
y DEBE redirigir a la URL de origen.

### R3 — Persistencia en sesión

CUANDO el visitante ya conmutó su idioma y navega a cualquier otra página del storefront,
EL SISTEMA DEBE mantener el idioma elegido,
SIN requerir que lo vuelva a seleccionar.

### R4 — Persistencia en cookie

DONDE la sesión del visitante expiró o fue regenerada,
CUANDO el visitante vuelve al sitio con la cookie de idioma presente,
EL SISTEMA DEBE restaurar el idioma de la cookie a la sesión y aplicarlo.

### R5 — Precedencia de fuentes

CUANDO existen a la vez un idioma en sesión y uno distinto en cookie,
EL SISTEMA DEBE aplicar el de **sesión**,
porque representa la elección más reciente del visitante.

### R6 — Idioma por defecto

CUANDO un visitante sin sesión ni cookie de idioma accede a cualquier página,
EL SISTEMA DEBE aplicar el idioma de `APP_LOCALE`,
SIN registrar preferencia alguna.

### R7 — Selector visible en desktop

CUANDO el storefront se renderiza en desktop,
EL SISTEMA DEBE mostrar el selector de idioma en el bloque derecho del navbar,
con el idioma activo indicado visualmente.

### R8 — Selector visible en móvil

DONDE el visitante accede desde móvil (por debajo del breakpoint `lg`),
CUANDO abre el menú desplegable,
EL SISTEMA DEBE mostrar el selector de idioma dentro del menú,
con la misma capacidad de conmutación que en desktop.

### R9 — Etiquetas en idioma nativo

CUANDO el selector se renderiza en cualquier idioma activo,
EL SISTEMA DEBE mostrar cada opción con su nombre nativo ("Español", "English"),
SIN traducir los nombres al idioma activo y SIN usar banderas de países.

### R10 — Mensajes de validación localizados

DONDE el idioma activo es español,
CUANDO un visitante envía un formulario con datos inválidos,
EL SISTEMA DEBE mostrar los mensajes de validación en español.

### R11 — Paginación localizada

DONDE el idioma activo es español,
CUANDO el visitante recorre un listado paginado,
EL SISTEMA DEBE mostrar los enlaces de paginación en español.

---

### Errores y bordes

### R12 — Rechazo de idioma no soportado

CUANDO un visitante envía `POST /locale` con un valor que no corresponde a un idioma soportado,
EL SISTEMA DEBE rechazar la solicitud con error de validación,
SIN modificar la preferencia vigente y SIN cambiar el idioma aplicado.

### R13 — Rechazo de solicitud sin idioma

CUANDO se envía `POST /locale` sin el campo `locale`,
EL SISTEMA DEBE rechazar la solicitud con error de validación,
SIN modificar la preferencia vigente.

### R14 — Cookie corrupta o con idioma retirado

CUANDO la cookie de idioma contiene un valor que ya no corresponde a un idioma soportado,
EL SISTEMA DEBE ignorarla y aplicar `APP_LOCALE`,
SIN lanzar excepción ni interrumpir la request.

### R15 — Protección CSRF

CUANDO se envía `POST /locale` sin token CSRF válido,
EL SISTEMA DEBE rechazar la solicitud,
porque la ruta pertenece al grupo `web` y no está exceptuada.

---

### Accesibilidad

### R16 — Semántica del selector

CUANDO el selector se renderiza,
EL SISTEMA DEBE exponerlo como control accesible con etiqueta descriptiva localizada,
y DEBE indicar programáticamente cuál es el idioma activo.

### R17 — Operable por teclado

CUANDO el visitante navega con teclado,
EL SISTEMA DEBE permitir abrir el selector, recorrer las opciones y confirmar la elección,
SIN requerir puntero.

### R18 — Atributo `lang` del documento

CUANDO cualquier página del storefront se renderiza,
EL SISTEMA DEBE reflejar el idioma activo en el atributo `lang` del elemento `<html>`,
para que lectores de pantalla y traductores automáticos lo interpreten correctamente.
