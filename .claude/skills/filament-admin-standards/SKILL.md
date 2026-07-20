---
name: filament-admin-standards
description: >
  Premium UI/UX standards for Filament v5 admin panels in this marketplace project.
  Use when creating or refactoring Filament Resources, Pages, Widgets, RelationManagers,
  forms, tables, filters, actions, PanelProvider branding, or admin UX; when improving
  panel aesthetics, validation feedback, empty states, or navigation; or when the user
  runs /filament-admin-standards. Domain logic stays in Actions/DTOs/Services per AGENTS.md —
  this skill owns panel presentation quality, not business rules.
metadata:
  short-description: "Filament v5 premium admin UI/UX for this marketplace"
  version: "2.0"
  stack: "filament/filament v5 · laravel v13 · livewire v4 · php 8.5"
---

# Filament Admin Standards (Marketplace)

Actionable rules for **premium admin panels** in this repo. Domain architecture is already
defined in [`AGENTS.md`](../../../AGENTS.md) / project-conventions — **do not reinvent it here**.

| Concern | Source of truth |
|---------|-----------------|
| Actions, DTOs, Services, Enums, Gateways | `AGENTS.md` / `.ai/guidelines/project-conventions.md` |
| Feature acceptance / EARS | `specs/features/**` |
| Filament API syntax (version-specific) | Laravel Boost `search-docs` **before** non-trivial UI code |
| **Panel UI/UX quality** | **This skill** |

## When to Use

Load **before writing Filament code** when:

- Creating or editing Resources / Pages / Widgets / Relation Managers
- Designing forms, tables, filters, bulk actions, empty states
- Polishing admin navigation, labels, feedback, or branding
- Reviewing “working but ugly/half-baked” admin CRUD
- User mentions: panel Filament, resource, admin UX, `/filament-admin-standards`

## Core Principles (non-negotiable)

1. **Domain outside Filament** — writes and invariants go through **Actions** (and Services only when shared). Resources orchestrate UI and call Actions with DTOs. See AGENTS.md.
2. **Thin Resources, rich UI** — Resources may be UI-heavy (layout, copy, affordances); they must not own business rules.
3. **Premium default** — every List/Form ships as product-grade operator UX, not scaffold leftovers.
4. **One language** — Spanish for operator-facing strings (labels, helpers, empty states, notifications, validation). Prefer `__()` when the string is reused; otherwise consistent Spanish literals matching existing Resources.
5. **Auth matches project** — panel access via `User::canAccessPanel()` + `config('ecommerce.admin_emails')` (or future approved auth). **Do not** introduce Spatie/Shield without explicit approval.
6. **No dead chrome** — no `FilamentInfoWidget` in production, no empty stubs, no unused soft-delete UI.
7. **Verify API** — Filament v5 namespaces differ from v3/v4. Use `search-docs` + sibling Resources under `app/Filament/`.

---

## 1. Project layout (Filament)

Follow **type-first** conventions. Current panel discovery:

```text
app/
  Filament/
    Resources/
      Categories/          # area folder
        CategoryResource.php
        Pages/
        Schemas/           # extract when form/table grows (premium: extract early)
      Products/
        ProductResource.php
        Pages/
        Schemas/
    Pages/                 # custom panel pages when needed
    Widgets/               # domain KPIs only
  Providers/Filament/
    AdminPanelProvider.php
  Actions/{Area}/          # domain entry — Filament calls these
  DTOs/{Area}/
  Enums/{Area}/
  Exceptions/{Area}/
  Models/                  # flat
```

| Rule | Detail |
|------|--------|
| Single admin panel | `id('admin')`, `path('admin')` unless product asks for more panels |
| Discovery | Only `App\Filament\Resources` (and Pages/Widgets) for this panel |
| Area folders | Group Resources by domain area (`Products`, `Categories`, `Orders`, …) |
| Extraction | Form/Table schemas → `Schemas/*` when form is multi-section or > ~80 lines |
| Shared UI bits | Prefer `app/Filament/Support/` (not a new top-level `app/Helpers`) for reusable columns/inputs — **only when reuse appears** |
| No new top-level `app/` folders | Without approval (AGENTS.md) |

### Panel provider (chrome & branding)

| Rule | Detail |
|------|--------|
| Branding | App name, primary color coherent with product; logo when assets exist |
| Widgets | Domain widgets only — **remove** `FilamentInfoWidget` from production |
| Login | Keep Filament login; access still gated by `canAccessPanel` |
| Plugins | Document why each plugin exists before adding |

### Panel access (project pattern)

```php
// User::canAccessPanel — NEVER return true unconditionally
// Gate: email in config('ecommerce.admin_emails') / ADMIN_EMAILS
// Multi-panel role matrix only if/when product adds more panels
```

---

## 2. Resource responsibilities

### Allowed in Resource / Pages

- Navigation: group, icon (`Heroicon` enum), sort, badge (actionable counts only)
- Form/table schema composition (layout, copy, field UX)
- Field-level validation for immediate feedback (`required`, `maxLength`, `unique(ignoreRecord: true)`, …)
- Mapping create/edit to **Actions + DTOs**
- Catch domain exceptions → danger `Notification` + `halt()` (no silent fail)
- Lightweight `getEloquentQuery()` for table scopes / soft deletes / eager loads

### Forbidden in Resource

- Multi-model business workflows without an Action
- Pricing, publication, stock, or integrity rules only inside `afterStateUpdated`
- Copy-pasted field blocks across Resources (extract schema)
- God-forms without sections/tabs/extraction

### Create / Edit page pattern

```php
// Create*: validate form → build DTO → Create*Action → notify success / map domain errors
// Edit*: same with Update*Action; never leave domain errors as uncaught 500s
```

---

## 3. Premium UI/UX — Forms (emphasis)

Aim for **calm density**: clear hierarchy, short labels, helpers only when needed, progressive disclosure.

### Layout hierarchy

| Level | Use |
|-------|-----|
| `Tabs` | ≥ 2 conceptual areas on a long form (e.g. Product: Datos · Variantes · Imágenes) |
| `Section` | Group of related fields; always titled; optional `description` for operator context |
| `Grid` | 2–3 columns for short fields; full width for description, repeaters, uploads |
| `Fieldset` | Rare — only for tight sub-groups inside a section |

**Rules:**

- Never dump 15 fields in a single flat column without sections.
- Put primary identity fields first (name, status, category).
- Put advanced / rare fields later or behind collapse (`collapsed()` / `compact()` when Filament API allows and siblings use it).
- Repeaters get their **own** section with clear heading + item labels from meaningful state (e.g. SKU or variant name).

### Labels & copy (Spanish operators)

| Element | Standard |
|---------|----------|
| Label | Human Spanish; never raw `snake_case` |
| Helper | Only for non-obvious format, side-effects, or domain constraints (e.g. “Requiere variante activa con precio”) |
| Placeholder | Optional for format hints (`COP`, `sku-001`) — do not repeat the label |
| Section description | One short sentence of operator guidance, not documentation essays |

### Field UX

| Practice | Standard |
|----------|----------|
| Progressive disclosure | `visible()` / `live()` only when it reduces noise |
| Defaults | Safe `default()` for booleans/currency; never surprise-publish |
| Create vs Edit | Password/immutable keys only where relevant; slug editable with unique ignore self |
| Selects | `searchable()` + `preload()` when options are small; search without full dump when large |
| Money | Integers only; helper states unit (pesos enteros vs centavos EUR); use `CurrencyEnum` options |
| Uploads | Disk, directory (`products/`), `visibility('public')` when public, mime + size limits — never unbounded |
| Toggles | Label states the **business** meaning (“Publicado”), not the column name |
| Nested repeaters | Cap depth; collapse items; bound counts if domain needs |

### Validation UX (edge of domain)

| Layer | Role in UI |
|-------|------------|
| Filament field rules | Instant feedback: presence, length, email, unique, numeric, enum options |
| Action + domain exception | Invariants (e.g. cannot publish without price) → danger notification, clear Spanish message |
| DB constraints | Safety net; do not rely on 500s for UX |

**Field rules of thumb:**

- `unique(ignoreRecord: true)` on edit for slugs/SKUs.
- No brittle `alpha()` on real-world names (accents, spaces, hyphens).
- Messages in Spanish, consistent with UI language.
- Do not re-implement Action invariants only in the form — call the Action and surface errors.

### Feedback

- Success: `Notification` with short title (“Producto creado”).
- Domain failure: catch typed exception → `->danger()` body = exception message → `halt()`.
- Destructive actions: confirm modal with consequence text.
- Never silent no-ops.

---

## 4. Premium UI/UX — Tables (emphasis)

Every List page is an **operator workspace**, not a raw data dump.

### Required table surface

```php
->columns([/* intentional searchable / sortable / toggleable */])
->filters([/* real operator questions — or omit consciously */])
->recordActions([/* Edit + domain actions; group if many */])
->toolbarActions([/* bulk only if policy/product allows */])
->emptyStateHeading('…')
->emptyStateDescription('…')
->emptyStateActions([/* Create if allowed */])
->striped()
->paginated([10, 25, 50])
```

(Use the exact action API names from installed Filament v5 / sibling Resources — verify with `search-docs`.)

| Practice | Standard |
|----------|----------|
| Columns | Show what operators scan first: name, status, category, key money/SKU signals |
| Search / sort | Only useful columns; avoid sorting heavy JSON/text without need |
| Toggleable | Secondary columns (`created_at`, ids) toggleable, some `isToggledHiddenByDefault` |
| Badges | Status/enums as badges; colors from Enum helpers when available |
| Filters | Active/published, category, trashed (if soft deletes are real product), dates when relevant |
| Empty state | Always: heading + description + primary Create when allowed |
| Row actions | Primary: Edit; secondary in `ActionGroup` |
| Bulk | Delete/restore only when intentional; confirm destructive bulk |
| Performance | Eager-load relations used in columns (`getEloquentQuery` / modify query) |
| Money columns | Format integers with currency context; never show raw ambiguous numbers without unit |

### Navigation & chrome

- **Groups by domain**: Catálogo, Pedidos, Pagos, … — not by technical type
- **Icons**: `Heroicon` enum; one metaphor per concept
- **Badges**: pending work queues only
- **Labels**: `modelLabel` / `pluralModelLabel` in Spanish, consistent with group

---

## 5. Soft deletes (all or nothing)

If model uses SoftDeletes **and** product needs trash UX:

- Table: `TrashedFilter`
- Actions: restore / force delete, permission-gated
- Bulk variants aligned

If not product-required: do not leave half-wired trash columns/actions/imports.

---

## 6. Authorization (project-aligned)

| Layer | Requirement |
|-------|-------------|
| `canAccessPanel` | Admin emails config — never `return true` |
| Policies | Prefer when operations diverge beyond “any admin”; not mandatory scaffolding for every model on day one |
| Actions visibility | Hide/disable actions operators cannot use |
| Row-level | When ownership matters, policy receives the model instance |

Do **not** add Shield/Spatie unless approved.

---

## 7. Domain wiring (reminder only)

| UI event | Domain |
|----------|--------|
| Create form submit | `Create*Action` + `*DTO` inside `DB::transaction` when multi-model |
| Edit form submit | `Update*Action` + DTO |
| Delete | `Delete*Action` or explicit guard with Spanish message if relations block |
| Publish toggle | Action enforces invariants (`ProductCannotBePublishedException`, etc.) |

Enums: `app/Enums/{Area}/*Enum.php` string-backed; forms/tables consume Enum options + badge colors.

Full rules: **AGENTS.md** — this skill does not override them.

---

## 8. Relation Managers & Widgets

**Relation managers** — when editing children in parent context improves operator flow; share schemas with child Resource when both exist.

**Widgets** — operator questions (queues, KPIs), authorized, no N+1, no marketing chrome.

---

## 9. Performance

- Eager-load table relations
- Searchable selects for large datasets (limit + search)
- Avoid `Model::find` inside hot `afterStateUpdated` loops
- Dashboard queries acceptable under real data volume

---

## 10. Implementation workflow (agents)

1. **Discover** sibling Resources, Actions, DTOs, Enums — match style.
2. **Confirm domain** already exists or implement Action/DTO first (AGENTS.md).
3. **`search-docs`** for Filament v5 form/table/action APIs involved.
4. **Compose premium form** — tabs/sections/grid, Spanish copy, helpers, validation UX.
5. **Compose premium table** — columns, filters, empty state, pagination, eager loads.
6. **Wire pages** to Actions; map domain exceptions to notifications.
7. **Auth & panel chrome** — access gate; no FilamentInfoWidget.
8. **Tests** — PHPUnit feature tests for admin happy/fail paths (existing `tests/Feature/Catalog` style).
9. **Pint** dirty PHP; self-check §11 + [references/checklist.md](references/checklist.md).

---

## 11. Definition of Done (premium bar)

### UI/UX
- [ ] Navigation group + Heroicon + Spanish labels
- [ ] Form: sections (tabs if multi-concern); no flat field dump
- [ ] Helpers only where non-obvious; money/currency clarified
- [ ] Table: useful columns, at least intentional filters or conscious omit
- [ ] Empty state complete (heading, description, action)
- [ ] Striped + sensible pagination
- [ ] Notifications success/failure; destructive confirms
- [ ] No marketing/debug widgets on panel

### Validation & feedback
- [ ] Field rules for format/presence/length/unique(ignoreRecord)
- [ ] Domain invariants via Action exceptions, surfaced in UI
- [ ] Spanish messages; no brittle `alpha()` on names

### Architecture (project)
- [ ] Writes through Actions/DTOs, not only Resource closures
- [ ] Enums for statuses/types; no magic strings in filters
- [ ] No new unauthorized top-level `app/` folders

### Quality
- [ ] No N+1 on list
- [ ] Soft deletes end-to-end **or** not half-present
- [ ] Feature tests for critical admin paths
- [ ] Pint clean

---

## 12. Minimal UI patterns (Filament v5 style)

Prefer patterns already in `app/Filament/Resources/**`. Illustrative shapes:

### Sectioned form

```php
Section::make('Datos del producto')
    ->description('Información principal visible en el catálogo.')
    ->schema([
        Grid::make(2)->schema([
            TextInput::make('name')->label('Nombre')->required()->maxLength(255),
            TextInput::make('slug')->label('Slug')->helperText('Vacío = se genera del nombre.'),
        ]),
    ])
    ->columnSpanFull(),
```

### Table empty state

```php
->emptyStateHeading('No hay productos todavía')
->emptyStateDescription('Crea el primer producto del catálogo.')
->emptyStateActions([
    // CreateAction / page action — match sibling Resources
])
```

### Domain error on save

```php
try {
    ($this->action)($dto);
} catch (ProductCannotBePublishedException $e) {
    Notification::make()->title('No se pudo publicar')->body($e->getMessage())->danger()->send();
    $this->halt();
}
```

---

## References

- Operator checklist: [references/checklist.md](references/checklist.md)
- Anti-patterns: [references/anti-patterns.md](references/anti-patterns.md)
- Code conventions: `AGENTS.md`
- Official docs: https://filamentphp.com/docs (always verify via Boost `search-docs` for **v5**)
