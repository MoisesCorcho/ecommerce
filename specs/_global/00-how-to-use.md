# Cómo usar estas specs (SDD)

## Qué es esto

Esta carpeta define **Spec-Driven Development** para este ecommerce Laravel **sin duplicar** lo que ya es canónico en el repo (guidelines, Boost, esquema BD).

Cada feature de producto se especifica en:

| Archivo | Propósito |
|---------|-----------|
| `requirements.md` | **QUÉ** — user stories + criterios EARS. Sin implementación. |
| `design.md` | **CÓMO** — Actions, UI (Filament/Livewire), contratos, riesgos. |
| `tasks.md` | **ORDEN** — checklist ejecutable + tests + Definition of Done. |

Ubicación: `specs/features/<NN-slug>/`.

## Fuentes de verdad (no copiar aquí)

| Tema | Fuente canónica | Notas |
|------|-----------------|-------|
| Convenciones de código (Actions, DTOs, enums, layout) | `.ai/guidelines/project-conventions.md` → reflejado en `AGENTS.md` / `CLAUDE.md` | Actualizar **solo** el guideline; no reescribir en `specs/` |
| Stack, Sail, Pint, PHPUnit, Filament idioms | Laravel Boost + guidelines del proyecto | Usar `search-docs` / Boost al implementar |
| Modelo de datos de dominio | `app/Models`, `app/Enums/{Area}`, migrations | Si una feature **cambia** el esquema, actualizar models/enums + migrations |
| Layout de código (Actions, DTOs, …) | project-conventions / `AGENTS.md` | Tipo primero, área después: `app/Actions/{Area}`, no módulos verticales |
| Setup del entorno | `README.md` | Fuera de SDD de features |
| Producto, roadmap, dependencias | [`01-product-and-roadmap.md`](01-product-and-roadmap.md) | Unico lugar del “qué construimos y en qué orden” |
| Calidad de specs (EARS, audit, fix) | [`02-feature-quality.md`](02-feature-quality.md) | Barra de robustez de cada feature |

**Regla anti-duplicación:** si el contenido ya vive en la tabla de arriba, en `specs/` solo se **referencia**. Si se contradicen, gana la fuente canónica de la fila.

## Flujo con agente / equipo

1. Leer `01-product-and-roadmap.md`. Confirmar prerequisitos de la feature en estado **Completo** (o aceptar deuda explícita).
2. Aplicar convenciones del proyecto (`AGENTS.md` / project-conventions). No es opcional.
3. Abrir `requirements.md`. Resolver ambigüedades **antes** de design o código.
4. Ajustar `design.md` (alineado a convenciones + esquema BD). Verificar APIs con Boost/docs, no de memoria.
5. Ejecutar `tasks.md` como checklist. Marcar items al completarlos.
6. Antes de cerrar: Definition of Done de `tasks.md` + criterios R1…Rn de requirements.

### Auditoría y corrección de specs

Antes de implementar una feature cuyas specs no fueron revisadas:

1. **Auditar** según `02-feature-quality.md` (solo lectura; una feature por sesión).
2. Tras confirmación del equipo, **corregir** según el mismo documento (sección corrección).
3. Luego implementar.

Los comandos de pipeline del agente (`/sdd-new`, `/sdd-ff`, etc.) son **orquestación** (explore → propose → …). Esta carpeta es el **contrato de producto y calidad** versionado en git. Pueden usarse juntos: el pipeline produce borradores; la barra de `02` los hace auditables.

## Convención de estado

Al inicio de cada `requirements.md` **y** en la tabla del roadmap:

| Estado | Significado |
|--------|-------------|
| `No iniciada` | Sin specs útiles o no empezada |
| `Specs en progreso` | Se están escribiendo/corrigiendo requirements/design/tasks |
| `Lista para implementar` | Audit OK (o corrección aplicada); prerequisitos OK |
| `En implementación` | Código en curso |
| `Completa` | DoD de tasks cumplido; tests verdes del alcance |

```markdown
> **Estado:** Lista para implementar  
> **ID:** F01 · **Slug:** `01-catalog`  
> **Prerequisitos:** fundación de dominio (models/migrations)
```

## Actualizar steering (solo cuando cambia el rol)

| Cambio | Dónde actualizar |
|--------|------------------|
| Nueva feature en el producto | `01-product-and-roadmap.md` + carpeta `specs/features/NN-slug/` |
| Criterio de calidad SDD / protocolo audit | `02-feature-quality.md` |
| Nueva convención de código | `.ai/guidelines/project-conventions.md` (+ `boost:update` si aplica) |
| Cambio de esquema de dominio | migration + model + enum (si aplica) |
| Setup local | `README.md` |

## Layout del repo de specs

```text
specs/
  _global/
    00-how-to-use.md          ← este archivo
    01-product-and-roadmap.md
    02-feature-quality.md
  features/
    01-catalog/
      requirements.md
      design.md
      tasks.md
```
