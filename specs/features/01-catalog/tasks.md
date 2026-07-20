# F01 — Catálogo · Tasks (admin Filament)

> **ID:** F01 · **Slug:** `01-catalog`  
> **Requirements:** [`requirements.md`](requirements.md) · **Design:** [`design.md`](design.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md) · **Calidad:** [`02-feature-quality.md`](../../_global/02-feature-quality.md)

**Alcance DoD:** admin Filament + dominio.  
**Diferido:** storefront UI (sección 7 y tests 8.11–8.12) — no bloquean cierre F01.

---

## 1. Config y acceso al panel

- [x] 1.1 Crear `config/ecommerce.php` con `admin_emails` y `default_currency`. Documentar en `.env.example`. _(cubre R14, R18; default_currency prep R11–R13 diferidos)_
- [x] 1.2 `FilamentUser` + `canAccessPanel()` vía `config('ecommerce.admin_emails')`. _(cubre R14, R18)_
- [x] 1.3 Nota operativa: admin en `ADMIN_EMAILS` + user en DB; `storage:link`. _(cubre R8, R14)_

## 2. Dominio — scopes y excepciones

- [x] 2.1 `scopeActive` en `Product` y `ProductVariant`. _(prep dominio; no UI)_
- [x] 2.2 `scopePublishedForStorefront` en `Product`. _(prep; R11–R13 diferidos)_
- [x] 2.3 `ProductCannotBePublishedException`. _(cubre R10, R15)_

## 3. Dominio — categorías

- [x] 3.1 `CreateCategoryAction`. _(cubre R1, R6)_
- [x] 3.2 `UpdateCategoryAction`. _(cubre R2, R7)_
- [x] 3.3 `DeleteCategoryAction`. _(cubre R3)_

## 4. Dominio — productos (DTOs + Actions)

- [x] 4.1 DTOs Upsert producto/variante/precio/imagen. _(cubre R4, R5, R16)_
- [x] 4.2 `CreateProductAction` + transaction + invariantes. _(cubre R4, R6, R8, R9, R10, R15, R16, R20)_
- [x] 4.3 `UpdateProductAction` + transaction + invariantes. _(cubre R5, R7, R8, R9, R10, R15, R16, R20)_

## 5. Admin Filament — categorías

- [x] 5.1 `CategoryResource` form/table. _(cubre R1, R2, R6, R7, R19)_
- [x] 5.2 Create/edit/delete cableados a Actions. _(cubre R1, R2, R3, R19)_

## 6. Admin Filament — productos

- [x] 6.1 `ProductResource` datos producto. _(cubre R4, R5, R6, R7, R19)_
- [x] 6.2 Variantes + precios (Repeater). _(cubre R4, R5, R16, R20)_
- [x] 6.3 Imágenes FileUpload public/products. _(cubre R8, R9)_
- [x] 6.4 Create/Edit → Product Actions; errores de publicación visibles. _(cubre R4, R5, R10, R15)_

## 7. Storefront Livewire — **DIFERIDO** (fuera DoD F01)

> Reactivar con manual de marca / slice “Storefront catálogo” (R11–R13, R17).

- [ ] 7.1 Listado público `publishedForStorefront`. _(cubre R11, R13 — diferido)_
- [ ] 7.2 Detalle por slug + variantes con precio. _(cubre R12, R13 — diferido)_
- [ ] 7.3 Rutas públicas + 404 no publicable. _(cubre R11, R12, R17 — diferido)_

*Nota:* si el código ya existe en el repo, no cuenta como cierre de F01 ni se expande en esta feature.

## 8. Tests (PHPUnit)

### En alcance F01

- [x] 8.1 Acceso panel admin vs no-admin / guest. _(cubre R14, R18)_
- [x] 8.2 Admin crea categoría; name vacío falla. _(cubre R1, R19)_
- [x] 8.3 Admin actualiza categoría; slug duplicado rechazado. _(cubre R2, R7)_
- [x] 8.4 Admin elimina categoría; producto sin categoría. _(cubre R3)_
- [x] 8.5 Admin crea producto con variante+precio; slug auto. _(cubre R4, R6)_
- [x] 8.6 Admin actualiza producto (grafo). _(cubre R5, R8)_
- [x] 8.7 Publish invariant (fail + success). _(cubre R10, R15)_
- [x] 8.8 Una sola imagen primaria. _(cubre R9)_
- [x] 8.9 Precio no entero / moneda inválida. _(cubre R16)_
- [x] 8.10 SKU duplicado. _(cubre R20)_

### Diferido (storefront)

- [ ] 8.11 Listado público filtros de publicación. _(cubre R11, R13 — diferido)_
- [ ] 8.12 Detalle 200/404 storefront. _(cubre R12, R13, R17 — diferido)_

## 9. Cierre de calidad (F01 admin)

- [x] 9.1 Tests del **alcance admin** en verde vía Sail.
- [x] 9.2 Pint en PHP tocado.
- [x] 9.3 Estado F01 = **Completa** (admin only) en requirements + roadmap; storefront marcado diferido.

---

## Mapa de trazabilidad (DoD F01)

| Criterio | Tareas | DoD F01 |
|----------|--------|---------|
| R1 | 3.1, 5.1, 5.2, 8.2 | Sí |
| R2 | 3.2, 5.1, 5.2, 8.3 | Sí |
| R3 | 3.3, 5.2, 8.4 | Sí |
| R4 | 4.1, 4.2, 6.1, 6.2, 6.4, 8.5 | Sí |
| R5 | 4.1, 4.3, 6.1, 6.2, 6.4, 8.6 | Sí |
| R6 | 3.1, 4.2, 5.1, 6.1, 8.5 | Sí |
| R7 | 3.2, 4.3, 5.1, 6.1, 8.3 | Sí |
| R8 | 1.3, 4.2, 4.3, 6.3, 8.6 | Sí |
| R9 | 4.2, 4.3, 6.3, 8.8 | Sí |
| R10 | 2.3, 4.2, 4.3, 6.4, 8.7 | Sí |
| R11 | 7.1, 7.3, 8.11 | **Diferido** |
| R12 | 7.2, 7.3, 8.12 | **Diferido** |
| R13 | 7.1, 7.2, 8.11, 8.12 | **Diferido** |
| R14 | 1.1, 1.2, 1.3, 8.1 | Sí |
| R15 | 2.3, 4.2, 4.3, 6.4, 8.7 | Sí |
| R16 | 4.1, 4.2, 4.3, 6.2, 8.9 | Sí |
| R17 | 7.3, 8.12 | **Diferido** |
| R18 | 1.1, 1.2, 8.1 | Sí |
| R19 | 5.1, 5.2, 6.1, 8.2 | Sí |
| R20 | 4.2, 4.3, 6.2, 8.10 | Sí |

---

## Definition of Done (F01)

- [x] Criterios **en alcance** R1–R10, R14–R16, R18–R20 implementados y testeados.
- [x] R11–R13, R17 **no** bloquean cierre (diferidos a storefront + manual de marca).
- [x] `config/ecommerce.php` + `ADMIN_EMAILS` en `.env.example`.
- [x] `User::canAccessPanel` sin Spatie.
- [x] Actions/DTOs categoría y producto; producto multi-write en transacción.
- [x] Filament: CategoryResource + ProductResource.
- [x] Imágenes public/products; una primaria por producto.
- [x] PHPUnit admin en verde; Pint OK.
- [x] Specs + roadmap alineados a **admin-only**.
