# Filament Panel — Pre-Merge Checklist (Marketplace)

Use before marking a Resource, Page, Widget, or Panel change as done.
Domain/Actions conventions: see `AGENTS.md`. This list emphasizes **premium UI/UX**.

## Panel & access

- [ ] Single admin panel discovery limited to `App\Filament\*`
- [ ] `User::canAccessPanel()` uses admin emails config (never unconditional `true`)
- [ ] No `FilamentInfoWidget` / debug chrome in production
- [ ] Branding (name/colors) coherent; plugins justified

## Resource / Page structure

- [ ] Navigation group by **domain**, Heroicon, Spanish model labels
- [ ] Area folder under `app/Filament/Resources/{Area}/`
- [ ] Form/table extracted to `Schemas/` if long or multi-concern
- [ ] Pages map correct (List/Create/Edit/View as needed)
- [ ] Create/Edit call Actions + DTOs; domain errors → Notification + halt
- [ ] `getEloquentQuery()` eager-loads and scopes correctly
- [ ] No empty stub directories or unused imports

## Form UX (premium)

- [ ] Sections (and Tabs if multi-concern / long form)
- [ ] Grid for short fields; full width for description/repeaters/uploads
- [ ] Spanish labels; helpers only for non-obvious rules/formats
- [ ] Primary identity fields first; advanced fields later
- [ ] Create vs Edit differences handled (slug unique ignore self, etc.)
- [ ] `live()` / visibility only when it clarifies UX
- [ ] Money fields: integers + currency context (helper or adjacent select)
- [ ] File uploads constrained (types, size, disk, visibility)
- [ ] Nested repeaters labeled, collapsible, not unbounded chaos

## Table UX (premium)

- [ ] Columns operators actually scan; searchable/sortable/toggleable intentional
- [ ] Status/enums as badges with colors
- [ ] Filters for real operator questions (or conscious omit)
- [ ] Row actions grouped when many
- [ ] Bulk actions intentional + confirmed if destructive
- [ ] Empty state: heading + description + primary action
- [ ] Striped + pagination options (`10, 25, 50`)
- [ ] Relations eager-loaded (no N+1)

## Validation & feedback

- [ ] Field rules for format/presence/length
- [ ] `unique(ignoreRecord: true)` on edit where needed
- [ ] Domain invariants enforced in Actions; UI surfaces exceptions in Spanish
- [ ] Delete blocked with clear message when product requires it
- [ ] No brittle `alpha()` on names that need accents/spaces
- [ ] Success and failure notifications present
- [ ] Destructive actions use confirm modal

## Soft deletes

- [ ] Full UI (TrashedFilter + restore/forceDelete) **or** no half-wired trash UX
- [ ] No unused SoftDeletingScope imports

## Domain alignment (light check)

- [ ] Multi-model writes go through Action (+ transaction inside Action)
- [ ] Enums under `app/Enums/{Area}` for statuses/types/currencies
- [ ] No business rules only in `afterStateUpdated`

## i18n & polish

- [ ] Single UI language: Spanish for operators (no EN/ES mix)
- [ ] Navigation badges only for actionable counts

## Performance

- [ ] No N+1 on list/widgets
- [ ] Heavy selects searchable/limited
- [ ] Dashboard queries acceptable under real volume

## Tests & style

- [ ] Feature tests for critical admin paths (happy + validation/domain fail)
- [ ] `vendor/bin/sail bin pint --dirty` clean on touched PHP
