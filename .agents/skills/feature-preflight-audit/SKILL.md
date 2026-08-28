---
name: feature-preflight-audit
description: >
  Pre-implementation architectural audit & blast radius analysis for features
  (specs/features/<slug>). Use before writing code to verify completeness,
  invariant integrity, edge cases, existing codebase coverage, and impact radius
  against AGENTS.md, Laravel Boost, and CodeGraph. Triggers on /audit-feature,
  /preflight, "auditar feature", "analizar feature antes de implementar",
  "preflight check", "revisar feature antes de codificar", or when preparing to
  implement any feature spec.
metadata:
  short-description: "Pre-implementation readiness & blast radius audit for features"
  version: "1.0"
  stack: "laravel v13 · filament v5 · livewire v4 · php 8.5 · codegraph · sdd"
---

# Feature Preflight Audit & Readiness Protocol

> **CONCEPTS > CODE**: Code is cheap to write and expensive to delete. Never write a single line of implementation code without a verified understanding of existing codebase baselines, business invariants, failure edge cases, and blast radius.

This skill governs the **pre-implementation gate** in this repository. It guarantees that any feature under `specs/features/<slug>` is architecturally sound, fully specified, and aligned with canonical sources of truth **before** moving to active implementation.

---

## Sources of Truth Hierarchy

When auditing a feature, resolve conflicts using this strict precedence:

| Priority | Source of Truth | Purpose & Scope |
|:---:|---|---|
| **1** | `AGENTS.md` / `.ai/guidelines/project-conventions.md` | Architecture rules: Type first/Area second (`app/Actions/{Area}`), DTOs, Enums as strings, no vertical domain modules, thin Filament/Controllers. |
| **2** | `app/Models`, `app/Enums/{Area}`, `database/migrations` | Canonical domain schema & persistent state. |
| **3** | `specs/_global/` (`00-how-to-use`, `01-roadmap`, `02-feature-quality`) | SDD lifecycle, global dependencies, EARS criteria, and Definition of Done standards. |
| **4** | `specs/features/<slug>/` (`requirements.md`, `design.md`, `tasks.md`) | Feature-specific observable behavior, technical design, and task checklist. |
| **5** | Laravel Boost (`search-docs`) | Official, version-specific framework and package documentation (Filament v5, Laravel v13, Livewire v4). |

---

## When to Activate

Trigger this skill **before writing any code** when:
* The user says: `"analiza la feature"`, `"auditar feature"`, `"revisar feature antes de implementar"`, `"/audit-feature"`, or `"/preflight"`.
* A feature spec is drafted or marked as `"Specs en progreso"` / `"Lista para implementar"`.
* The orchestrator or developer is about to transition tasks to `"En implementación"`.

---

## 4-Phase Audit Protocol

```
┌─────────────────────────────────────────────────────────────┐
│ 1. DESCUBRIMIENTO DE CÓDIGO EXISTENTE Y FUENTES DE VERDAD   │
│    (CodeGraph, modelos, migraciones, DTOs, Actions actuales)│
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│ 2. ANÁLISIS DE RADIO DE IMPACTO (BLAST RADIUS)              │
│    (Esquema BD, consumidores downstream, concurrencia, i18n)│
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│ 3. MATRIZ DE INVARIANTES, EDGE CASES Y REGLAS AGENTS.MD     │
│    (Reglas inquebrantables, modos de fallo, transacciones)  │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│ 4. VEREDICTO DETERMINISTA Y TABLA DE RECOMENDACIONES SENIOR │
│    (Green Light directo O Tabla Situación/Recomendación/Por qué)│
└─────────────────────────────────────────────────────────────┘
```

---

### Fase 1: Descubrimiento de Código Base y Precedentes (Baseline & Sibling Discovery)

Do **NOT** assume a feature starts from a clean slate or exists in a vacuum. First inspect the existing codebase and sibling precedents:

1. **Stack Capabilities & Installed Packages (`composer.json` + Traits)**:
   * Inspect `composer.json` and existing models to identify already adopted ecosystem solutions (e.g. `spatie/laravel-translatable`, `spatie/laravel-permission`).
   * **Hard Rule**: If a package/trait is already adopted in the project, specs must NOT invent alternative/ad-hoc database designs (such as `_es`/`_en` columns).
2. **Sibling Feature & Precedent Audit (`specs/features/` + `app/Models/`)**:
   * Identify the **conceptual family** of the feature (e.g. Marketing Storefront for Pop-up F16 ↔ Announcements F15; Discounts for Cart Threshold F17 ↔ Coupons F06).
   * Verify how sibling features solved:
     * Internationalization (i18n) and locale enums (`LocaleEnum`).
     * Prioritization and sorting (`sort_order`, `scopeOrdered`).
     * Scheduling and validity (`starts_at`, `ends_at`, `scopeActive`).
     * Filament v5 form/table organization (`Schemas/` directory decomposition).
3. **CodeGraph Exploration**: Use `codegraph_explore` or read-only CLI (`codegraph query`, `codegraph callers`, `codegraph callees`) to map out related symbols and call chains.
4. **Inventory Classification**: Categorize existing artifacts:
   * **Ya implementado y funcional**: Clases, migraciones, modelos, enums o traits existentes que NO deben duplicarse ni reescribirse.
   * **Parcialmente implementado**: Componentes que requieren extensión o refactorización controlada.
   * **Faltante (In Scope)**: Lo que verdaderamente debe construirse desde cero.
5. **Anti-Reinvention Rule**: Never create duplicate utilities, enums (e.g. `CurrencyEnum`), helpers, or base Actions if equivalent mechanisms already exist in `app/`.

---

### Fase 2: Análisis de Radio de Impacto (Blast Radius)

Evaluate what existing systems could break or degrade:

1. **Database Schema & Migrations**:
   * Are existing columns modified? (Must remain non-destructive).
   * Are enums stored as `string` per project conventions, or is someone trying to use DB-native `ENUM`?
   * Are indexes, foreign keys, and unique constraints properly defined?
2. **Downstream Callers & Consumers**:
   * Which Filament Resources, Livewire components, Controllers, Jobs, or Listeners depend on the modified models/actions?
3. **Concurrency & Race Conditions**:
   * Stock reservations, coupon redemptions, wallet balances, or payment states: does the design use `DB::transaction` and pessimistic locking (`lockForUpdate()`) or atomic increments where needed?
4. **Security & Authorization Boundaries**:
   * Check against `marketplace-security` skill: are policy gates, signed URLs, admin panel barriers, or tenant boundaries preserved?
5. **Localization (i18n)**:
   * Are UI copy and validation messages planned in `lang/{en,es}/{domain}.php` using short keys, without hardcoded strings in code?

---

### Fase 3: Matriz Crítica de Invariantes y Edge Cases

Review the feature spec against real-world production risks:

1. **Domain Invariants**: What business rules must **never** be violated under any circumstance? (e.g., *Total cannot be negative*, *Stock cannot drop below zero*, *A completed order cannot be re-paid*).
2. **Failure Modes & Edge Cases**:
   * Network drops or webhook timeouts (idempotency tokens).
   * Guest vs authenticated session transitions (cart ownership).
   * Deleted/inactive products inside active carts.
   * Missing permissions or unauthorized role access.
   * Empty states, zero results, validation errors in Filament/Livewire.
3. **Project Architecture Compliance (`AGENTS.md`)**:
   * Class layout: `app/Actions/{Area}/...Action.php` (invokable, single verb).
   * Services used **only** for shared capabilities, not 1:1 Action wrappers.
   * Payloads use `readonly` DTOs (`app/DTOs/{Area}/...DTO.php`) with strict types and constructor promotion.
   * Models remain flat in `app/Models/`.
   * No vertical domain directories (`app/Catalog/...` is strictly prohibited).
4. **Pattern Drift Detection (Deriva de Patrones vs Features Hermanas)**:
   * Does the spec introduce an ad-hoc mechanism where a sibling feature already established a project standard? (e.g. ad-hoc `_es`/`_en` columns instead of `HasTranslations`, missing `sort_order` or `ordered()` scope, monolithic forms instead of Filament v5 `Schemas/`).
   * Any detected pattern drift MUST be highlighted as a top-priority item in the Architectural Decisions Table.

---

### Fase 4: Formato Determinista de Salida

After completing the 3 analysis phases, output **EXACTLY ONE** of the following two outcomes:

#### Caso A: VEREDICTO: LISTA PARA IMPLEMENTAR (Green Light)
Use this **only** if the spec has zero ambiguities, all edge cases are handled in EARS criteria, existing code is identified, and architecture conforms 100% to `AGENTS.md`.

Format:
```markdown
### 🟢 Veredicto: LISTA PARA IMPLEMENTAR

La feature **[NN-slug]** está completamente especificada y validada contra la arquitectura del proyecto.

* **Fuentes de verdad verificadas**: [Modelos, Enums, Migraciones citadas]
* **Código base identificado**: [Componentes existentes reutilizados]
* **Invariantes cubiertas**: [Resumen de invariantes garantizadas]
* **Radio de impacto**: Controlado y acotado a [módulos/archivos].
```

---

#### Caso B: VEREDICTO: ACCIÓN REQUERIDA — Decisiones y Gaps Pendientes (Yellow/Red Light)
If there are missing decisions, unhandled edge cases, architecture violations, or blast radius risks, output the structured **Tabla de Decisiones y Gaps Arquitectónicos**:

Format:
```markdown
### 🟡 Veredicto: ACCIÓN REQUERIDA — Decisiones y Gaps Pendientes

Antes de escribir código para **[NN-slug]**, se detectaron los siguientes puntos que deben quedar definidos en la spec (`requirements.md` / `design.md`):

#### 1. Tabla de Decisiones y Gaps Arquitectónicos

| # | Situación / Vacío / Edge Case | Riesgo / Radio de Impacto | Recomendación Senior (Arquitectura & Código) | Justificación Técnica & Trade-offs |
|---|-------------------------------|---------------------------|----------------------------------------------|------------------------------------|
| 1 | [Descripción concreta]        | [Qué pasa si se ignora]   | [Solución exacta siguiendo AGENTS.md]       | [Por qué es la mejor alternativa]  |
| 2 | ...                           | ...                       | ...                                          | ...                                |

#### 2. Inventario de Código Base (Existente vs Faltante)
* **Ya implementado (Reutilizar)**: `Path/To/File.php`
* **Por modificar/extender**: `Path/To/File.php`
* **Por crear desde cero**: `app/Actions/Area/ActionNameAction.php`, `app/DTOs/...`

#### 3. Radio de Impacto Identificado
* **Esquema BD**: [Migraciones necesarias, índices, no-breaking]
* **Componentes Afectados**: [Filament Resources, Livewire, Controllers]
* **i18n Requerido**: `lang/{en,es}/{domain}.php`
```

Follow up with a single, clear question asking the user to approve the recommendations or provide specific preferences before updating the specs.
