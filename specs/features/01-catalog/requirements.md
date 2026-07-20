# F01 — Catálogo (admin Filament)

> **Estado:** Completa  
> **ID:** F01 · **Slug:** `01-catalog`  
> **Prerequisitos:** Fundación de dominio (models, enums, migrations, factories, tests de grafo) — ver [`01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md)

## Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Producto y alcance F01 | [`specs/_global/01-product-and-roadmap.md`](../../_global/01-product-and-roadmap.md) |
| Calidad EARS / audit | [`specs/_global/02-feature-quality.md`](../../_global/02-feature-quality.md) |
| Convenciones de código | [`AGENTS.md`](../../../AGENTS.md) / project-conventions |
| Esquema de dominio | `app/Models` (`Category`, `Product`, `ProductVariant`, `ProductVariantPrice`, `ProductImage`), `app/Enums/Commerce/CurrencyEnum.php`, migrations |

## User stories

1. **Como** administrador de la tienda, **quiero** gestionar categorías (crear, editar, listar, eliminar o desasociar), **para** organizar el catálogo en una estructura jerárquica opcional.
2. **Como** administrador, **quiero** gestionar productos con variantes, precios por moneda e imágenes, **para** cargar el inventario vendible del negocio.
3. **Como** administrador, **quiero** que el sistema impida publicar un producto incompleto, **para** no dejar ofertas activas sin opción con precio.
4. **Como** operador de seguridad, **quiero** que solo cuentas de administración autorizadas accedan al panel, **para** proteger el CRUD de catálogo.

## Alcance de esta feature

**Incluye:** CRUD admin (Filament) del grafo de catálogo: categorías, productos, variantes, precios multi-moneda, imágenes; gate de panel; invariante de publicación; Actions/DTOs/scopes de dominio necesarios para admin.

**No incluye (F01):**

- Storefront Livewire (listado/detalle públicos, rutas `/products`, UI de marca).
- Carrito, checkout, cupones, reserva de stock, reviews, wishlist.
- Spatie Permission / roles granulares.
- Media Library de terceros; multi-idioma de catálogo.

### Diferido — storefront UI

La **lectura pública del catálogo** (UI con manual de marca) queda **fuera del DoD de F01**.  
Criterios R11, R12, R13 y R17 se conservan abajo marcados **Diferido** para reactivarlos en una slice futura (p. ej. “Storefront catálogo”) sin reescribir el contrato.

Scopes de dominio (`publishedForStorefront`, etc.) pueden existir en código como preparación; **no** son entregable de UI de F01.

---

## Decisiones de producto

| # | Tema | Decisión |
|---|------|----------|
| D1 | Alcance F01 | **Solo admin Filament** del grafo de catálogo. Storefront UI **diferido** hasta manual de marca / slice propia. |
| D2 | AuthZ admin | Spatie Permission **no** instalado y **no** se agrega sin aprobación. Gate mínimo: email en `ADMIN_EMAILS` → `config('ecommerce.admin_emails')`. Reemplazable por Spatie más adelante. |
| D3 | Publicación (datos) | Visibilidad de negocio controlada por `Product.is_active` y `ProductVariant.is_active`. Categorías sin flag de activo (siempre estructurales). |
| D4 | Moneda (datos) | Moneda por defecto en config (`COP`). Reglas de “qué es publicable en moneda X” viven en dominio/scopes para F03+ y storefront futuro; F01 no entrega UI de tienda. |
| D5 | Dinero | Solo enteros. COP = pesos enteros; EUR = centavos. Nunca floats. |
| D6 | Invariante de publicación (admin) | Para `is_active = true` se exige ≥1 variante activa con ≥1 precio (cualquier moneda). Error claro si no. |
| D7 | Slugs | Únicos; auto desde nombre si vacío en create; editables; unique ignore self en update. |
| D8 | Imágenes | Disco `public`, directorio `products/`, path relativo, visibilidad pública. Sin Media Library. |
| D9 | Imagen primaria | Como máximo una `is_primary = true` por producto. |
| D10 | Idioma de specs | Español. EARS según [`02-feature-quality.md`](../../_global/02-feature-quality.md). |
| D11 | Naming | Feature slug `01-catalog`, ID **F01**. |
| D12 | Código storefront preexistente | Si hay Livewire/rutas de catálogo en el repo, **no** forman parte del DoD de F01; se tratan como adelanto o se reharán con el manual de marca. |

---

## Criterios de aceptación (EARS)

### Happy path — en alcance F01

### R1 — Alta de categoría en admin

DONDE un administrador autenticado con acceso al panel está en la gestión de categorías,  
CUANDO envía un alta con nombre válido y, opcionalmente, slug, categoría padre y orden,  
EL SISTEMA DEBE persistir la categoría y mostrarla en el listado de categorías del panel.

### R2 — Edición y listado de categorías

DONDE un administrador autenticado con acceso al panel,  
CUANDO edita los datos de una categoría existente con valores válidos,  
EL SISTEMA DEBE actualizar la categoría y reflejar los cambios en el listado y en el formulario de edición.

### R3 — Eliminación de categoría

DONDE un administrador autenticado con acceso al panel,  
CUANDO elimina una categoría,  
EL SISTEMA DEBE remover esa categoría del listado de categorías  
Y, si tenía productos asociados, esos productos deben permanecer en el sistema sin esa categoría (asociación nula o equivalente observable en el grafo de dominio).

### R4 — Alta de producto con grafo mínimo vendible

DONDE un administrador autenticado con acceso al panel está en la gestión de productos,  
CUANDO envía un alta válida que incluye nombre, al menos una variante activa y al menos un precio entero en una moneda soportada (COP o EUR) para esa variante,  
EL SISTEMA DEBE persistir el producto, sus variantes y precios asociados  
Y el producto debe aparecer en el listado de productos del panel.

### R5 — Edición de producto con variantes, precios e imágenes

DONDE un administrador autenticado con acceso al panel,  
CUANDO actualiza un producto existente incluyendo cambios en variantes, precios por moneda e imágenes con datos válidos,  
EL SISTEMA DEBE persistir el estado resultante del grafo (producto + variantes + precios + imágenes) de forma coherente  
Y reflejarlo al reabrir el producto en el panel.

### R6 — Generación y unicidad de slug en creación

CUANDO se crea una categoría o un producto con nombre válido y slug vacío,  
EL SISTEMA DEBE asignar un slug no vacío derivado del nombre  
Y ese slug DEBE ser único entre registros del mismo tipo (categorías entre sí; productos entre sí).

### R7 — Slug editable y único en actualización

CUANDO se actualiza el slug de una categoría o producto a un valor que no usa otro registro del mismo tipo,  
EL SISTEMA DEBE aceptar el cambio.  
CUANDO el slug propuesto ya pertenece a otro registro del mismo tipo,  
EL SISTEMA DEBE rechazar la operación e informar el conflicto de unicidad  
SIN sobrescribir el registro ajeno.

### R8 — Imágenes de producto en almacenamiento público

DONDE un administrador autenticado con acceso al panel,  
CUANDO asocia una o más imágenes válidas a un producto,  
EL SISTEMA DEBE almacenarlas de forma accesible públicamente bajo el prefijo de productos del disco público  
Y persistir la ruta relativa de cada imagen en el catálogo.

### R9 — Una sola imagen primaria por producto

CUANDO se marca una imagen de un producto como primaria,  
EL SISTEMA DEBE dejar como máximo una imagen con marca de primaria en ese producto  
(si había otra primaria, deja de serlo).

### R10 — Publicación de producto con invariante de variantes y precios

CUANDO un administrador intenta guardar un producto con `is_active = true`,  
Y el producto tiene al menos una variante activa con al menos un precio en cualquier moneda soportada,  
EL SISTEMA DEBE permitir la publicación (`is_active = true` persistido).

### R14 — Acceso de administrador autorizado al panel

DONDE un usuario autenticado cuyo email está en la lista de administradores configurada,  
CUANDO accede al panel de administración,  
EL SISTEMA DEBE permitir el acceso al panel y a la gestión de catálogo.

---

### Validación y error — en alcance F01

### R15 — Publicación rechazada sin variante activa con precio

CUANDO un administrador intenta guardar un producto con `is_active = true`  
Y no existe al menos una variante activa con al menos un precio (cualquier moneda),  
EL SISTEMA DEBE rechazar el guardado con un mensaje de error claro orientado a completar variantes/precios  
SIN dejar el producto en estado activo en base de datos por esa operación.

### R16 — Rechazo de montos no enteros o moneda no soportada

CUANDO se intenta persistir un precio de variante con monto no entero (o no convertible de forma segura a entero no negativo) o con una moneda fuera del conjunto soportado (COP, EUR),  
EL SISTEMA DEBE rechazar la operación e informar el error de validación  
SIN persistir ese precio inválido.

### R18 — Acceso denegado al panel para no administradores

DONDE un usuario autenticado cuyo email **no** está en la lista de administradores configurada, o un visitante no autenticado,  
CUANDO intenta acceder al panel de administración o a la gestión de catálogo del panel,  
EL SISTEMA DEBE denegar el acceso (redirigir a login y/o impedir la entrada al panel según el caso)  
SIN permitir crear, editar o eliminar categorías o productos.

### R19 — Campos obligatorios de categoría y producto en admin

DONDE un administrador envía un formulario de categoría sin nombre, o de producto sin nombre,  
EL SISTEMA DEBE rechazar el guardado e informar los errores de validación  
SIN crear o actualizar el registro con datos incompletos en esos campos obligatorios.

### R20 — SKU de variante único

CUANDO se intenta crear o actualizar una variante con un SKU que ya pertenece a otra variante,  
EL SISTEMA DEBE rechazar la operación e informar el conflicto de unicidad  
SIN sobrescribir la otra variante.

---

### Diferido — storefront UI (fuera del DoD F01)

> **Estado de estos criterios:** Diferido · Reactivar en slice “Storefront catálogo” (manual de marca).

### R11 — Listado público solo de productos publicables en moneda de contexto

DONDE un visitante accede al listado público de catálogo (sin autenticación),  
EL SISTEMA DEBE mostrar únicamente productos con `is_active = true` que tengan al menos una variante con `is_active = true` y precio en la moneda por defecto de la tienda  
Y NO DEBE listar productos inactivos ni productos activos sin ninguna variante activa con precio en esa moneda.

### R12 — Detalle público por slug

DONDE un visitante solicita el detalle de un producto mediante un slug que corresponde a un producto publicable según R11,  
EL SISTEMA DEBE mostrar el detalle del producto (datos de catálogo visibles: nombre, descripción si existe, imágenes, variantes con precio en la moneda de contexto)  
SIN exigir autenticación.

### R13 — Precios enteros en moneda de contexto en storefront

DONDE un visitante ve un producto o variante con precio en la moneda por defecto de la tienda,  
EL SISTEMA DEBE exponer el monto como entero en la unidad de esa moneda (COP en pesos enteros; EUR en centavos)  
SIN usar representación flotante del monto almacenado  
Y DEBE omitir de las opciones con precio las variantes activas que no tengan fila de precio en esa moneda.

### R17 — Detalle público: producto inexistente o no publicable

DONDE un visitante solicita el detalle por un slug que no existe, o que existe pero el producto no cumple las reglas de publicación de R11 (inactivo o sin variante activa con precio en la moneda de contexto),  
EL SISTEMA DEBE responder con un resultado de no encontrado (HTTP 404 o equivalente observable de “no existe para el visitante”)  
SIN exponer el contenido del producto no publicable.

---

## Trazabilidad de stories → criterios

| User story | Criterios (F01) | Diferido |
|------------|-----------------|----------|
| 1 Categorías admin | R1, R2, R3, R6, R7, R19 | — |
| 2 Productos / variantes / precios / imágenes | R4, R5, R6, R7, R8, R9, R16, R19, R20 | — |
| 3 Invariante de publicación | R10, R15 | — |
| 4 AuthZ panel | R14, R18 | — |
| (futuro) Catálogo público UI | — | R11, R12, R13, R17 |

## Definition of Done (F01)

- Criterios **en alcance** R1–R10, R14–R16, R18–R20 implementados y testeados.
- R11–R13, R17 **no** bloquean el cierre de F01.
- Filament Category + Product usables por admin autorizado.
- Sin dependencia de storefront Livewire ni manual de marca.
