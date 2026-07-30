# Calidad de features SDD (EARS, audit, corrección)

**Rol:** contrato de robustez de specs de producto.  
**Alcance:** `specs/features/<slug>/` en este repositorio.

Este documento fusiona, adaptado a Laravel, lo que en otros proyectos eran protocolos 07 (audit) y 08 (corrección). No reemplaza project-conventions ni Boost.

---

## Tres artefactos (obligatorios)

| Archivo | Propósito | Prohibido |
|---------|-----------|-----------|
| `requirements.md` | **QUÉ** observable | Timestamps de migration, nombres de paquetes, clases PHP, “usar Action X” |
| `design.md` | **CÓMO** técnico | User stories sin decisión de diseño; contradecir convenciones del proyecto |
| `tasks.md` | **ORDEN** ejecutable | Tareas sin traza a criterios; “tests unitarios” genérico sin escenarios |

Mezclar QUÉ/CÓMO (p. ej. “Eloquent SoftDeletes” dentro de un AC de requirements) es hallazgo de audit.

### Plantilla mínima de feature

```text
specs/features/NN-slug/
  requirements.md
  design.md
  tasks.md
```

Cabecera recomendada en `requirements.md`: estado, ID, prerequisitos (ver `00-how-to-use.md`).

Referencias de steering que **sí** deben citarse en requirements/design cuando apliquen:

- Producto y dependencias → `specs/_global/01-product-and-roadmap.md`
- Convenciones de código → project-conventions / `AGENTS.md`
- Datos → `app/Models`, `app/Enums`, migrations
- Docs de framework/paquetes → Laravel Boost (`search-docs`), no memoria del modelo

---

## Criterios EARS e IDs

### Formato

Cada criterio de aceptación:

1. ID estable: encabezado `### R{N} — Título`
2. Plantilla EARS válida: **CUANDO** / **MIENTRAS** / **DONDE** / **SI…ENTONCES**
3. Debe mapear a un test futuro concreto
4. Sin lenguaje vago sin métrica (“rápido”, “fácil”, “adecuado”, “fluido”)

Secciones recomendadas en requirements:

- **Happy path**
- **Validación y error**

Toda user story debe tener al menos un criterio. Todo comportamiento en tasks debe tener AC.

### Ejemplos (Laravel / ecommerce)

```markdown
### R3 — Precio por moneda en variante publicada

CUANDO una variante de producto está marcada como disponible para la venta
Y existe un precio activo en la moneda del contexto de tienda,
EL SISTEMA DEBE exponer ese precio en la moneda solicitada
SIN usar floats para el monto almacenado.
```

```markdown
### R9 — Formulario de producto en admin

DONDE un administrador autenticado con permiso de catálogo está en el panel,
CUANDO envía el formulario de alta de producto con datos válidos,
EL SISTEMA DEBE persistir el producto y mostrarlo en el listado de catálogo.
```

```markdown
### R12 — Variante sin stock suficiente al agregar al carrito

CUANDO el comprador intenta agregar una cantidad mayor al stock disponible de la variante,
EL SISTEMA DEBE rechazar la operación e informar el límite disponible
SIN crear o actualizar la línea del carrito con la cantidad inválida.
```

**DONDE** es obligatorio en features UI-heavy (Filament, Livewire, storefront) o con comportamiento por rol/pantalla.

---

## Tasks: granularidad y trazabilidad

- Máximo **2 niveles** de jerarquía en `tasks.md`.
- **Crítico:** cada tarea referencia criterio(s): `_(cubre R3, R5)_`.
- Si faltan IDs R1…Rn en requirements → hallazgo prioritario (corregir requirements primero).
- Orden lógico típico en este stack:

  1. Esquema/enums/factories (solo si la feature los extiende)
  2. Domain (Actions, Services, policies)
  3. UI admin (Filament) y/o storefront (Livewire)
  4. Integraciones (gateways, jobs, webhooks)
  5. Tests explícitos (feature/unit según capa)

### Tests en tasks (no genéricos)

Desglosar, por ejemplo:

- Feature test happy path _(cubre Rn)_
- Feature/unit error o edge _(cubre Rm)_
- Test de policy/authz si aplica
- Test de gateway fake / webhook idempotente si aplica

### Mapa de trazabilidad (obligatorio al cerrar specs)

Tabla al final de `tasks.md`:

| Criterio | Tareas |
|----------|--------|
| R1 | 1.2, 5.1 |
| R2 | 1.3, 5.2 |

Definition of Done: listar el rango completo de criterios (ej. R1–R14) + Pint en PHP tocado + tests del alcance en verde (vía Sail).

### Ejemplo de tarea

```markdown
- [ ] Implementar Action de alta de producto con variantes mínimas. _(cubre R1, R3)_
- [ ] Feature test: admin crea producto válido y aparece en listado. _(cubre R9)_
- [ ] Feature test: precio inválido (monto no entero / moneda desconocida) es rechazado. _(cubre R4, R12)_
```

---

## Ambiguities (detección temprana)

Actuar como implementador inmediato: listar **preguntas concretas** (no inventar respuestas) sobre:

- AuthZ (quién puede; guest vs user; Spatie roles)
- Errores de red / proveedor de pago caído / webhook duplicado o reordenado
- Concurrencia (stock, doble submit de checkout, carrito concurrente)
- Multi-moneda (qué pasa si falta precio en la moneda activa)
- Casos borde no cubiertos por AC
- Huecos entre user stories, design y tasks

Resolver en tabla **Decisiones de producto** dentro de `requirements.md` (o bloquear implementación hasta respuesta del equipo).

---

## Validación primero (Regla happy + error)

- Los AC deben cubrir happy path **y** inputs inválidos / estados de fallo.
- Al menos **un** caso de error testeable por feature.
- `tasks.md` debe incluir esos tests con nombre de escenario.

---

## Anti-alucinación (stack de este proyecto)

Antes de afirmar APIs en `design.md`:

| Tema | Verificar con |
|------|----------------|
| Laravel (Eloquent, validation, queues, HTTP) | Boost `search-docs` / docs de la versión del repo |
| Filament v5 | Boost / docs Filament alineadas al proyecto |
| Livewire v4 | Boost / docs Livewire |
| Stripe / Bold | docs oficiales del proveedor + diseño de gateway del proyecto |
| Spatie Permission | docs del paquete instalado |

**No** asumir firmas de métodos ni nombres de componentes Filament de memoria.  
**No** copiar patrones Flutter/Drift/Riverpod de otros repos.

Si el design propone un paquete nuevo: requiere aprobación (las guidelines del proyecto prohíben cambiar dependencias sin OK).

---

# Parte A — Auditoría (solo lectura)

## Cuándo

El usuario pide auditar / revisar / verificar una feature contra SDD, o evaluar calidad de specs antes de implementar (`/sdd-audit`, “audita F01”, etc.).

**Regla crítica:** NO modificar archivos en esta fase. Entregar reporte y esperar confirmación antes de corregir.

## Alcance por sesión

Por defecto **una feature** (`specs/features/<slug>/`). Si piden muchas, confirmar alcance.

## Archivos a leer

Por feature:

1. `requirements.md`
2. `design.md`
3. `tasks.md`

Steering (anti-alucinación y alineación):

- `specs/_global/00-how-to-use.md`
- `specs/_global/01-product-and-roadmap.md`
- este archivo (`02`)
- project-conventions / `AGENTS.md` (equivalente a “03-conventions”)
- `app/Models` + migrations + enums (equivalente a “05-data-model”)

## Las 6 reglas de evaluación

| # | Regla | Qué verificar |
|---|--------|----------------|
| 1 | Estructura de 3 artefactos | Existen y respetan QUÉ / CÓMO / ORDEN; sin mezcla |
| 2 | Sintaxis EARS | Plantillas válidas; sin vaguedad; testability; DONDE en UI |
| 3 | Granularidad y trazabilidad | ≤2 niveles; `_(cubre Rx)_`; orden lógico; IDs R presentes |
| 4 | Ambiguities | Preguntas concretas sin respuestas inventadas |
| 5 | Integración con steering | Refs a 01 + conventions + esquema; design no contradice convenciones ni modelo |
| 6 | Validación primero | Happy + error; tests explícitos en tasks |

## Formato del reporte

### Tabla

| Feature (ID) | R1 | R2 | R3 | R4 | R5 | R6 | Hallazgos clave |
|--------------|----|----|----|----|----|----|-----------------|
| F01 catalog | ✅/⚠️/❌ | … | | | | | |

### Cierre obligatorio

1. **Top problemas** (priorizados; transversales si auditas más de una).
2. **Propuesta de cambio estructural** si aplica (ej. “falta mapa de trazabilidad en todas”).
3. **Lista priorizada** de correcciones (respetar dependencias de `01-product-and-roadmap.md`: F01 antes que F03…).

Luego preguntar si se procede a corrección.

---

# Parte B — Corrección (edición de specs)

## Cuándo

Tras reporte de auditoría, o si el usuario confirma explícitamente corregir sin audit previo (`/sdd-fix`, “corrige F01”).

## Alcance

- Una feature por sesión salvo indicación contraria.
- Solo `specs/features/<slug>/`.
- Si la feature introduce entidades nuevas: migration + model (+ enum/factory) en la fase de implementación; reflejar en `design.md` de la feature.

## Paquete de corrección (orden)

### 1. `requirements.md`

- IDs **R1…Rn** con `### R{N} — Título`
- Secciones Happy path / Validación y error
- EARS completas; **DONDE** en UI
- Sacar implementación → `design.md`
- Tabla **Decisiones de producto** para ambigüedades resueltas
- Referencias explícitas a `01-product-and-roadmap`, convenciones, esquema
- Alinear stories ↔ criterios

### 2. `design.md`

- Referenciar steering (01, convenciones, esquema)
- Alinear con layout tipo+área `app/Actions/{Area}|Services/{Area}|…`, sufijos, Filament/Livewire del proyecto
- Contratos observables (eventos, jobs, webhooks, DTOs de borde) referenciados desde requirements
- Verificar APIs con Boost/docs
- Diagrama o flujo **específico** de la feature (no plantilla vacía)
- Gateways solo para I/O externo; no interfaces ceremoniales internas

### 3. `tasks.md`

- `_(cubre R{N})_` en cada tarea
- Máx. 2 niveles; orden datos → dominio → UI → integración → tests
- Tests por escenario
- Mapa de trazabilidad + DoD con rango R1–Rn

## Checklist pre-cierre de corrección

- [ ] Toda user story tiene criterio(s)
- [ ] Toda tarea tiene `_(cubre Rx)_` o justificación en el mapa
- [ ] Ningún criterio vago sin métrica
- [ ] `design.md` no contradice project-conventions ni el esquema
- [ ] Tests nombran escenarios
- [ ] Huecos de audit resueltos o documentados en Decisiones de producto

## Commit sugerido (si piden commit)

```text
docs(F0N): refine <slug> specs after SDD audit
```

Tipo `docs`, scope = ID de feature.

## Priorización entre features

Corregir/implementar según `01-product-and-roadmap.md`:

1. F01 (catálogo) antes que carrito/checkout
2. Luego features cuyos prerequisitos ya estén en **Completa** o al menos **Lista para implementar** con riesgo aceptado explícito
