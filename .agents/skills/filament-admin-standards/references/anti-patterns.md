# Filament Anti-Patterns (Marketplace)

Failures common in “working but semi-mature” admin panels. Agents must avoid introducing them and should fix them when touching related code.

Domain rules live in Actions/DTOs per `AGENTS.md`. Here the focus is **panel UI/UX and Filament hygiene**.

## 1. Open panel access

```php
// BAD
public function canAccessPanel(Panel $panel): bool
{
    return true;
}
```

**Do:** Gate with `config('ecommerce.admin_emails')` / project auth. Deny by default.

---

## 2. Scaffold leftovers

- Empty Resource page folders without a real Resource
- `FilamentInfoWidget` on the production dashboard
- Importing `SoftDeletingScope` without trash UX
- Unreachable code after early `return true` in visibility methods

**Do:** Finish or delete; production chrome = domain only.

---

## 3. Flat “CSV form”

Fifteen fields in one column, no sections/tabs, raw column names as labels.

**Do:** Section/Tab hierarchy; Spanish labels; progressive disclosure; extract `Schemas/` when large.

---

## 4. God Resource with buried domain

200+ lines of Repeaters + `live()` + pricing/publish branching inside the Resource.

**Do:**

- UI layout stays rich but readable (schemas)
- Invariants in **Actions** + domain exceptions
- Resource only maps DTO ↔ form state and surfaces errors

---

## 5. Tables as bare CRUD

```php
->filters([])
->bulkActions([])
// no emptyState*
```

**Why it hurts:** Operators cannot find records; empty screens look broken.

**Do:** Filters that match operator questions; empty states; bulk only when intentional.

---

## 6. Silent domain failures

Create/Edit calls Action, exception becomes 500 or nothing happens.

**Do:** Catch typed domain exceptions → danger `Notification` + `halt()` with Spanish body.

---

## 7. Validation only in the form

Jobs, seeders, or future APIs write the same models without the same rules.

**Do:** Action/DTO layer shared by all entry points; Filament rules for immediate UX only; DB unique indexes for races.

---

## 8. Locale salad

Section titles in English, helpers in Spanish, validation in English.

**Do:** One operator language (Spanish) across labels, helpers, notifications, empty states.

---

## 9. Money UX that lies

Float inputs, missing currency, or raw integer with no unit (COP pesos vs EUR cents).

**Do:** Integer fields + `CurrencyEnum` + helper text; format table columns with context.

---

## 10. Unbounded uploads / public by accident

FileUpload without mime/size limits or wrong visibility.

**Do:** Explicit disk, directory, visibility, accepted types, max size (`visibility('public')` only when product needs public URLs).

---

## 11. SoftDeletes half-implemented

Model soft-deletes; table has no TrashedFilter/restore; or trash actions without model support.

**Do:** Full end-to-end **or** remove the half UI.

---

## 12. Business logic in `afterStateUpdated`

Publication rules, price integrity, or stock only inside Livewire state hooks.

**Do:** Call Action/Service; UI hooks only refresh options/visibility.

---

## 13. N+1 and chatty forms

```php
->afterStateUpdated(fn ($state) => Model::find($state)?->name)
// per field, per repeater row
```

**Do:** `relationship()` selects, eager loads, cached option maps.

---

## 14. Delete without operator-facing guard

Relying on SQL FK errors → 500 or cryptic message.

**Do:** Pre-delete check in Action/UI with human Spanish message when the product requires it.

---

## 15. Introducing Shield/Spatie “because the skill said so”

**Do:** Follow project auth (admin emails) until product explicitly approves roles package.

---

## 16. Copy-pasted field blocks

Address/contact/price rows duplicated across Resources and RelationManagers.

**Do:** Shared schema under `app/Filament/Support` or Resource `Schemas/` when reuse appears (not before).

---

## 17. Features without operator feedback

Actions that fail quietly or succeed without confirmation when destructive.

**Do:** Success/danger notifications; confirm modals on destructive actions.

---

## 18. English-only Filament defaults left as-is

`modelLabel` still “product”, navigation in English, mixed with Spanish sections.

**Do:** Spanish labels for model, plural, navigation group, columns, filters.
