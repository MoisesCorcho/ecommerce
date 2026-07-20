# Project conventions

These rules define how this Laravel application is structured. Follow them for all new code. Prefer pragmatic SOLID: clear organization without ceremonial layers.

## Directory layout (type first, area second)

Top-level folders under `app/` are **by role/type**, not by vertical domain modules.  
**Do not** create trees like `app/Catalog/Actions` or `app/Domains/Orders/...` unless explicitly requested.

Inside each type folder, group by **area** (plural noun: `Products`, `Categories`, `Cart`, `Orders`, `Payments`, `Coupons`, `Commerce`, …) when there is more than a one-off class—or as soon as a cluster forms.

```text
app/
  Actions/
    Categories/
    Products/
    Cart/            # when it exists
  Services/
    Cart/            # example when shared capability appears
  DTOs/
    Products/
  Enums/
    Commerce/        # cross-cutting commerce vocab (e.g. CurrencyEnum)
    Orders/
    Payments/
    Coupons/
  Exceptions/
    Products/
  Contracts/
    Payments/        # when multiple payment ports appear
  Gateways/
    Payments/
  Models/            # Eloquent stays flat by default (no area subfolders unless approved)
  Http/
  Providers/
  Filament/
```

### Namespace = path

| Path | Namespace example |
|------|-------------------|
| `app/Actions/Products/CreateProductAction.php` | `App\Actions\Products\CreateProductAction` |
| `app/DTOs/Products/UpsertProductDTO.php` | `App\DTOs\Products\UpsertProductDTO` |
| `app/Enums/Commerce/CurrencyEnum.php` | `App\Enums\Commerce\CurrencyEnum` |
| `app/Exceptions/Products/ProductCannotBePublishedException.php` | `App\Exceptions\Products\ProductCannotBePublishedException` |

### Area folder rules

- Prefer **one area name** per cluster and reuse it across types (`Actions/Cart`, `Services/Cart`, `DTOs/Cart`).
- **Shared / cross-cutting** vocab that is not owned by one feature → `Commerce` (or another single agreed umbrella)—do not dump everything in the type root.
- A single class may live at the type root only as a temporary exception; prefer an area folder as soon as a second related class appears.
- **Models** remain flat under `app/Models` unless the team explicitly adopts model area folders.
- Do not add new top-level `app/` folders without approval.

Livewire components follow `config/livewire.php` (project default: multi-file / MFC, no ⚡ emoji prefix).

## Naming suffixes (files and classes)

Every class name and filename must include its role as a suffix to avoid ambiguity:

| Role | Suffix | Example class / file |
|------|--------|----------------------|
| Action | `Action` | `Actions/Orders/CreateOrderAction.php` |
| Service | `Service` | `Services/Cart/CartPricingService.php` |
| DTO | `DTO` | `DTOs/Orders/CreateOrderDTO.php` |
| Enum | `Enum` | `Enums/Commerce/CurrencyEnum.php` |
| Contract / interface | `Interface` | `Contracts/Payments/PaymentGatewayInterface.php` |
| Gateway | `Gateway` | `Gateways/Payments/StripePaymentGateway.php` |

Actions are named as verbs + suffix: `CreateUserAction`, `MarkInvoicePaidAction`.  
Services are named as capabilities + suffix: `InvoiceCalculatorService`.  
DTOs use the `DTO` suffix only (not `Data`).

## Actions vs Services

| Piece | Responsibility |
|-------|----------------|
| **Action** | One use case / domain verb. Preferred entry from Controllers, Livewire, Filament, Jobs. Prefer invokable (`__invoke`). |
| **Service** | Reusable capability within an area of the domain, shared by multiple Actions or callers. |
| **Model** | Persistence, relations, scopes, local invariants. Not full use-case orchestration. |

Rules:

- Prefer **Action** for a single, concrete use case.
- Use **Service** only when logic is shared or is a broader capability—not a 1:1 mirror of an Action.
- Do not create Action + Service pairs that only forward to each other.
- Keep Controllers, Livewire components, and Filament resources thin: validate/authorize at the edge, then call an Action.

Escape hatch: trivial one-off CRUD may stay simple until a second caller or real domain rules appear—then extract.

## DTOs

- Use DTOs to keep data coherent and readable across layer boundaries.
- Prefer DTOs when crossing real boundaries (HTTP → domain, domain → external API, jobs/queues, non-trivial multi-field payloads).
- Skip DTOs for trivial 1–2 argument calls inside the same layer.
- Name: `{Purpose}DTO` (e.g. `CreateOrderDTO`).
- Prefer `readonly` classes with constructor property promotion and strict types.

Validation lives at the edge (Form Request, Livewire rules, Filament). The Action receives an already-validated/normalized DTO, not the raw Request.

## Contracts, Gateways, and DI

- Use **interfaces** (`*Interface` in `app/Contracts`) and **gateways** (`*Gateway` in `app/Gateways`) for external I/O (payments, SMS, third-party APIs, etc.).
- Bind interface → gateway in service providers (`AppServiceProvider` or a dedicated provider).
- Depend on the interface (Dependency Inversion), never on a concrete gateway in domain/Actions.
- Do **not** create interfaces for every internal Service/Model “just in case”.
- Prefer fakes/stubs of gateways in tests.

## Enums and migrations

- Encapsulate fixed domain vocabularies in **backed enums** under `app/Enums/{Area}/*Enum.php`: document types, statuses, channels, etc.
- Prefer `string` backed enums; use `int` only with a strong reason.
- Put domain helpers on the enum when useful (`label()`, `isFinal()`, etc.).
- Do **not** use PHP class constants for sets that should be enums.
- Do **not** use DB native `ENUM` columns in migrations. Use `string` (appropriate length) and cast to the backed enum on the model.
- Adding a new case should not require changing the column type; keep stored values stable.

```php
// migration
$table->string('document_type', 32);

// model
protected function casts(): array
{
    return [
        'document_type' => DocumentTypeEnum::class,
    ];
}
```

Do not use enums for admin-managed catalogs that change at runtime—those belong in tables.

## Typing and PHPDoc

- Every new PHP file: `declare(strict_types=1);`.
- Strict types on parameters, return types, and properties. Prefer composition over deep inheritance.
- Honor interface contracts (Liskov): implementors must not surprise callers with stronger preconditions or weaker guarantees.
- PHPDoc is required when it adds meaning: non-obvious purpose, `@throws`, array shapes, generics, side effects.
- Forbid PHPDoc that only restates native types (e.g. `@param int $id` on `int $id`).

## Cross-cutting rules

- Open multi-model writes inside the Action with `DB::transaction`.
- Authorize at the edge or at the start of the Action (policies/gates); do not scatter auth ad hoc inside low-level Services.
- Prefer domain-specific exceptions over generic `abort()` inside domain code.
- Write feature tests for use cases; unit-test enums, DTO shaping, and gateway fakes when relevant.
- Follow existing sibling files for style; Pint (`vendor/bin/sail bin pint --dirty`) before finishing PHP changes.
- Do not add new top-level `app/` folders without approval.

## SOLID (pragmatic)

- **S** — Actions and focused Services; no god classes.
- **O** — extend via new enum cases, new gateway implementations, new Actions—not by rewriting callers.
- **L** — respect contracts; prefer small interfaces at external ports.
- **I** — interfaces only where multiple implementations or test seams matter (mainly gateways).
- **D** — high-level code depends on `*Interface`, bound to `*Gateway` in providers.

Apply SOLID to keep structure clear—not to maximize the number of classes.

## Spec-Driven Development (product features)

Product features are specified under `specs/`. **Do not restate** this file or Laravel Boost docs inside specs—**link** them.

| Path | Role |
|------|------|
| `specs/_global/00-how-to-use.md` | How to work with SDD in this repo; canonical source map |
| `specs/_global/01-product-and-roadmap.md` | Product vision, feature order, dependencies (F01…) |
| `specs/_global/02-feature-quality.md` | EARS criteria, R-ids, tasks traceability, audit & correction |
| `specs/features/<NN-slug>/` | Per feature: `requirements.md`, `design.md`, `tasks.md` |

When specifying or implementing a product feature:

1. Read `specs/_global/00`, `01`, and `02` first.
2. Follow the feature’s three artifacts; acceptance criteria use EARS with `R1…Rn` and tasks cite `_(cubre Rx)_`.
3. Code still follows the conventions above (Actions, enums as strings, etc.).
4. Domain schema truth remains `app/Models`, `app/Enums/{Area}`, and migrations—update those when a feature changes data shape.
