# F02 — Cuentas y direcciones · Tasks (admin Filament)

> **ID:** F02 · **Slug:** `02-accounts-addresses`  
> **Requirements:** [`requirements.md`](requirements.md) · **Design:** [`design.md`](design.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md) · **Calidad:** [`02-feature-quality.md`](../../_global/02-feature-quality.md)

**Alcance DoD:** admin Filament + dominio Users/Addresses.  
**Fuera de DoD:** Livewire storefront de cuentas.

---

## 1. Dominio — Users

- [ ] 1.1 `UpsertUserDTO` (name, email, phone?, password?). _(cubre R2, R3)_
- [ ] 1.2 `CreateUserAction`. _(cubre R2, R11, R12)_
- [ ] 1.3 `UpdateUserAction` (password solo si no vacío). _(cubre R3, R11, R12)_
- [ ] 1.4 `DeleteUserAction` (soft delete). _(cubre R4)_

## 2. Dominio — Addresses

- [ ] 2.1 `UpsertAddressDTO`. _(cubre R5, R6)_
- [ ] 2.2 `CreateAddressAction` + invariante default en transaction. _(cubre R5, R8, R13, R14)_
- [ ] 2.3 `UpdateAddressAction` + invariante default en transaction. _(cubre R6, R8, R13, R14)_
- [ ] 2.4 `DeleteAddressAction`. _(cubre R7)_

## 3. Admin Filament — Users

- [ ] 3.1 `UserResource` form/table (name, email, phone, password create/edit). _(cubre R1, R2, R3, R11, R12)_
- [ ] 3.2 Pages List/Create/Edit cableadas a User Actions; soft delete. _(cubre R1, R2, R3, R4, R9)_
- [ ] 3.3 Navigation group **Cuentas** (o **Clientes**); labels ES. _(cubre R1, R9)_

## 4. Admin Filament — Addresses

- [ ] 4.1 `AddressesRelationManager` en User (campos de address + is_default). _(cubre R5, R6, R7, R8, R13, R14)_
- [ ] 4.2 Create/Edit/Delete del RelationManager → Address Actions. _(cubre R5, R6, R7, R8)_

## 5. Tests (PHPUnit)

- [ ] 5.1 Acceso: admin puede gestionar users; no-admin/guest denegados. _(cubre R9, R10)_
- [ ] 5.2 Admin crea usuario válido; aparece en listado. _(cubre R1, R2)_
- [ ] 5.3 Validación required user (name/email/password en create). _(cubre R11)_
- [ ] 5.4 Email duplicado rechazado. _(cubre R12)_
- [ ] 5.5 Update user: password vacío no cambia hash; password nuevo sí. _(cubre R3)_
- [ ] 5.6 Soft delete: user ausente del listado default. _(cubre R4)_
- [ ] 5.7 Admin crea/edita/elimina dirección del user. _(cubre R5, R6, R7, R13)_
- [ ] 5.8 Marcar default desmarca la anterior del mismo user. _(cubre R8)_
- [ ] 5.9 País inválido (≠ 2 letras) rechazado. _(cubre R14)_

## 6. Cierre de calidad

- [ ] 6.1 Tests del alcance F02 en verde vía Sail.
- [ ] 6.2 Pint en PHP tocado (`vendor/bin/sail bin pint --dirty --format agent`).
- [ ] 6.3 Estado F02 = **Completa** en requirements + roadmap.

---

## Mapa de trazabilidad (DoD F02)

| Criterio | Tareas | DoD F02 |
|----------|--------|---------|
| R1 | 3.1, 3.2, 3.3, 5.2 | Sí |
| R2 | 1.1, 1.2, 3.1, 3.2, 5.2 | Sí |
| R3 | 1.1, 1.3, 3.1, 3.2, 5.5 | Sí |
| R4 | 1.4, 3.2, 5.6 | Sí |
| R5 | 2.1, 2.2, 4.1, 4.2, 5.7 | Sí |
| R6 | 2.1, 2.3, 4.1, 4.2, 5.7 | Sí |
| R7 | 2.4, 4.1, 4.2, 5.7 | Sí |
| R8 | 2.2, 2.3, 4.1, 4.2, 5.8 | Sí |
| R9 | 3.2, 3.3, 5.1 | Sí |
| R10 | 5.1 | Sí |
| R11 | 1.2, 1.3, 3.1, 5.3 | Sí |
| R12 | 1.2, 1.3, 3.1, 5.4 | Sí |
| R13 | 2.2, 2.3, 4.1, 5.7 | Sí |
| R14 | 2.2, 2.3, 4.1, 5.9 | Sí |

---

## Definition of Done (F02)

- [ ] Criterios **R1–R14** implementados y testeados.
- [ ] Sin componentes Livewire de cuenta/storefront en el alcance entregado.
- [ ] Actions/DTOs en `app/Actions/{Users,Addresses}` y `app/DTOs/{Users,Addresses}`.
- [ ] Filament: `UserResource` + `AddressesRelationManager`.
- [ ] Invariante: ≤1 `is_default` por `user_id`.
- [ ] Soft delete de users; password opcional en edit.
- [ ] PHPUnit del alcance en verde vía Sail; Pint OK.
- [ ] Specs + roadmap con estado **Completa**.
