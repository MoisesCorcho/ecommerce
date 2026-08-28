---
name: feature-qa-checklist
description: >
  Post-implementation QA verification & comprehensive test scenario generator
  for features (specs/features/<slug>). Use after completing a feature to produce
  an exhaustive QA testing checklist covering happy paths, boundary values,
  error injection, concurrency/race conditions, permissions/roles, UI edge cases,
  and regression vectors mapped to EARS requirements. Triggers on /qa-checklist,
  /qa, "checklist de qa", "casos de prueba", "matriz de qa", "qa checklist",
  "checklist para qa", or when preparing a feature for release/QA handoff.
metadata:
  short-description: "Post-implementation QA checklist & test scenario generator"
  version: "1.0"
  stack: "laravel v13 · filament v5 · livewire v4 · php 8.5 · pest/phpunit · qa"
---

# Feature QA Verification Checklist Protocol

> **CONCEPTS > CODE**: Writing code is only half the battle. Proving that the feature works under extreme, concurrent, and adversarial conditions is what separates amateur work from robust production engineering.

This skill governs the **post-implementation QA handoff** in this repository. It generates an exhaustive, structured test checklist so QA testers and developers can verify that a feature under `specs/features/<slug>` is 100% compliant with its EARS requirements, business invariants, and system boundaries.

---

## Sources of Truth

When generating a QA checklist, the agent MUST read:

| Artifact | Purpose for QA Checklist |
|---|---|
| `specs/features/<slug>/requirements.md` | Maps every acceptance criterion (`R1...Rn`) to explicit test cases. |
| `specs/features/<slug>/design.md` | Identifies technical failure modes, exceptions, gateways, and database state transitions. |
| `specs/features/<slug>/tasks.md` | Verifies implemented automated tests and Definition of Done scope. |
| `AGENTS.md` / `marketplace-security` | Threat model, race conditions, role boundaries, and localized messages. |

---

## When to Activate

Trigger this skill **immediately after completing the implementation of a feature** when:
* The user says: `"generá la checklist de QA"`, `"matriz de pruebas"`, `"casos de prueba de la feature"`, `"/qa-checklist"`, or `"/qa"`.
* All tasks in `tasks.md` are completed and the feature is transitioning from `"En implementación"` to `"Completa"` / QA handoff.

---

## 7-Pillar QA Taxonomy

Every QA Checklist produced MUST cover all 7 pillars of verification:

```
┌─────────────────────────────────────────────────────────────┐
│ 1. HAPPY PATH & FLUJOS DORADOS (Golden Journeys)            │
│    (Caminos ideales para invitados, clientes y admin)       │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│ 2. VALORES LÍMITE Y VALIDACIÓN (Boundary Testing)           │
│    (Ceros, negativos, máximos, caracteres especiales, vacíos)│
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│ 3. INYECCIÓN DE ERRORES Y FALLOS (Negative Testing)         │
│    (Pasarelas caídas, tarjetas rechazadas, sesiones vencidas)│
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│ 4. CONCURRENCIA Y RACE CONDITIONS (Concurrency Testing)     │
│    (Doble clic, pestañas simultáneas, cupones/stock agotados)│
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│ 5. PERMISOS, AUTORIZACIÓN Y SEGURIDAD (Authz & Security)    │
│    (Guest vs User vs Admin, IDOR, manipulación de precios)  │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│ 6. UI/UX, ACCESIBILIDAD E I18N (UI & Localization)          │
│    (Feedback visual, estados vacíos, llaves ES/EN)          │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│ 7. REGRESIÓN Y RADIO DE IMPACTO (Regression Testing)        │
│    (Flujos existentes que no deben haberse roto)            │
└─────────────────────────────────────────────────────────────┘
```

---

## Output Standard Format

The generated checklist MUST be output in clear, actionable Markdown with checkboxes `[ ]`, organized by pillar, containing:

```markdown
# QA Verification Checklist: [NN-slug] — [Feature Name]

> **Feature Specs:** `specs/features/[NN-slug]/`  
> **Fecha:** [YYYY-MM-DD]  
> **Alcance:** Verificación completa de aceptación, bordes y regresión.

---

### 1. 🌟 Happy Path & Flujos Principales
*Casos de uso normales de inicio a fin.*

- [ ] **TC-01 — [Título del Escenario]**
  * **Precondición:** [Estado inicial requerido, ej: Usuario logueado con carrito con 2 ítems]
  * **Pasos:** 
    1. [Paso 1]
    2. [Paso 2]
  * **Resultado Esperado:** [Comportamiento exacto esperado en UI y Base de Datos]
  * **Criterio EARS Cubierto:** `R1`

---

### 2. 🎚️ Valores Límite y Validaciones de Entrada
*Comprobación de límites numéricos, cadenas y campos obligatorios.*

- [ ] **TC-02 — [Validación de campo / Valor Límite]**
  * **Precondición:** [...]
  * **Pasos / Input:** [Ingresar valor 0 / valor máximo / texto > 255 chars / caracteres especiales]
  * **Resultado Esperado:** [Mensaje de validación localizado, sin error 500, persistencia bloqueada]
  * **Criterio EARS Cubierto:** `R2`

---

### 3. 💥 Inyección de Errores y Modos de Fallo
*Comportamiento cuando servicios externos o datos fallan.*

- [ ] **TC-03 — [Fallo de Pasarela / Excepción de Dominio / Red Desconectada]**
  * **Pasos:** [Forzar error en pasarela Stripe/Bold, simular timeout o token inválido]
  * **Resultado Esperado:** [Transacción revertida (`DB::rollBack`), feedback claro al usuario, logs limpios sin exponer datos sensibles]
  * **Criterio EARS Cubierto:** `R3`

---

### 4. ⚡ Concurrencia y Condiciones de Carrera (Race Conditions)
*Acciones simultáneas sobre recursos compartidos.*

- [ ] **TC-04 — [Doble Clic / Canje Simultáneo / Agotamiento de Stock]**
  * **Pasos:** [Enviar 2 peticiones simultáneas con la misma sesión o cupón de 1 solo uso]
  * **Resultado Esperado:** [Exactamente 1 petición es exitosa; la segunda es rechazada atómicamente; sin saldos/stock inconsistentes]
  * **Invariante Protegida:** [Invariante de concurrencia]

---

### 5. 🔒 Permisos, Autorización y Seguridad
*Límites de acceso y prevención de manipulación.*

- [ ] **TC-05 — [Acceso no autorizado / IDOR / Tampering]**
  * **Pasos:** [Intentar acceder a la orden de otro usuario o a endpoints de Filament sin rol de admin]
  * **Resultado Esperado:** [HTTP 403 Forbidden o redirección a login, sin fuga de datos]
  * **Seguridad:** [Policy Gate / canAccessPanel]

---

### 6. 🎨 UI/UX, Estados Vacíos y Localización (i18n)
*Experiencia visual, respuesta y traducciones.*

- [ ] **TC-06 — [Verificación de Idioma y Estados Vacíos]**
  * **Pasos:** [Navegar la interfaz con `APP_LOCALE=es` y luego `en`; ver tabla o lista sin registros]
  * **Resultado Esperado:** [Todas las cadenas usan `__('domain.key')`, sin texto crudo en código; empty state informativo visible]

---

### 7. 🛡️ Pruebas de Regresión (Impacto Colateral)
*Verificar que las features existentes siguen funcionando.*

- [ ] **TC-07 — [Verificación de flujo previo no afectado]**
  * **Pasos:** [Ejecutar flujo estándar del catálogo/checkout normal]
  * **Resultado Esperado:** [Todo opera con normalidad; suite automatizada `vendor/bin/sail artisan test --compact` verde]
```

---

## Instructions for the Agent

1. **Be Specific**: Include real URLs, field names, amounts, and expected status codes / DB column values (e.g. `order.status = 'paid'`, `coupon.used_count = 1`).
2. **Never Generalize**: Avoid generic steps like *"test that it works"*. State exact inputs, buttons to click, and assertions.
3. **Traceability**: Every test case must reference its corresponding requirement `R{N}` from `requirements.md` or a documented security invariant.
