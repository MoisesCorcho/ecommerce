# F05 — Pagos · Seguridad, hardening y residuales

> **ID:** F05 · **Slug:** `05-payments`  
> **Tipo:** post-implementación / constancia de audit (no reemplaza requirements)  
> **Estado del documento:** Activo  
> **Fecha de audit:** 2026-07-22 (branch `feature/05-payments`)  
> **Skill de referencia:** `.agents/skills/marketplace-security/`  
> **Requirements:** [`requirements.md`](requirements.md) · **Design:** [`design.md`](design.md) · **Tasks F05:** [`tasks.md`](tasks.md)

Este documento deja **constancia** de:

1. Qué del modelo de cobro ya es **robusto** en código.  
2. Qué se **corrigió** tras el audit.  
3. Qué se puede **corregir ya en código** (backlog de hardening).  
4. Qué son **decisiones de diseño / producto** (residuales aceptados a propósito).  
5. Qué es **ops / go-live** (humano, fuera del feature DoD original).

**No duplica** EARS ni D-ids de `requirements.md`: los **referencia**. Si un residual de producto cambia, se actualiza **primero** requirements/design y **después** este archivo.

---

## 1. Fuentes canónicas

| Tema | Fuente |
|------|--------|
| Qué debe hacer F05 | [`requirements.md`](requirements.md) (D1–D43, R1–R22) |
| Cómo está armado | [`design.md`](design.md) |
| Checklist implementación F05 | [`tasks.md`](tasks.md) — **Completa** (alcance original) |
| Hard rules de seguridad del repo | skill `marketplace-security` |
| Baseline Laravel genérico | skill `laravel-security` |
| Convenciones | [`AGENTS.md`](../../../AGENTS.md) |

---

## 2. Veredicto del audit (resumen)

| Capa | Estado |
|------|--------|
| Confianza de cobro (quién marca `paid`, firma webhook, authz pay/view) | **Robusto** y alineado a spec |
| Idempotencia incompleta (retry 5xx tras insert del evento) | **Corregido** (ver §3) |
| Hardening / ops pre-prod | **Pendiente** (ver §4 y §6) |
| Bordes de negocio (multi-intent, D25, D24, guest signed URL) | **Aceptados a propósito** (ver §5) |

**Frase operativa:** el camino crítico de cobro no es un castillo de naipes; lo que queda son residuales de producto conscientes + hardening/ops, no un festival de IDOR o “paid por return URL”.

---

## 3. Ya corregido en código (post-F05)

| ID | Qué | Evidencia | Commit (orientativo) |
|----|-----|-----------|----------------------|
| **FIX-01** | Reintento de webhook con `processed_at = null` ya no se acusa como `duplicate` sin re-aplicar side effects | `ProcessPaymentWebhookAction::claimEventForProcessing`; test `test_webhook_retries_incomplete_event_when_processed_at_still_null` | `fix(payments): re-apply incomplete webhook events on retry` |
| **FIX-02** | Bold: `BOLD_WEBHOOK_SECRET=""` (explícito) es válido en **sandbox**; no cae al `secret_key` por el operador `?:` | `BoldPaymentGateway::webhookSigningSecret`; tests empty/fallback/wrong | `fix(payments): allow empty Bold webhook secret in test mode` |
| **FIX-03** | Bold omite `callback_url` no público (localhost / `.test`) | `BoldPaymentGateway` + tests | `fix(payments): omit non-public Bold callback URLs on checkout` |

---

## 4. Corregible ya en código (backlog de hardening)

Ítems **sin** necesidad de reabrir D-ids de producto. Prioridad sugerida para implementación.

### 4.1 Alta prioridad (pre–plata real)

| ID | Tema | Problema | Solución propuesta | Esfuerzo | Tests |
|----|------|----------|--------------------|----------|-------|
| **SH-01** | Bold secret vacío en **production** | Con signing secret `""` cualquiera puede forjar HMAC de webhook | Fail-fast en `production` si el secret de firma es string vacío (al boot, al `createHostedCheckout`, o en `verifyWebhookSignature`). Documentar en `.env.example`: vacío = solo test | Bajo | Test con `App::detectEnvironment` / config override: prod + `''` → exception |
| **SH-02** | Timeout HTTP Stripe | Bold tiene `Http::timeout(15)`; Stripe no | Añadir el mismo timeout (o config compartida) en `StripePaymentGateway` | Muy bajo | Unit/feature con `Http::fake` + assert (si aplica) o regresión create session |
| **SH-03** | HTTP al gateway dentro de TX/`lockForUpdate` | `StartOrderPaymentAction` puede mantener locks de DB mientras espera al proveedor | Validar + crear `Payment` pending bajo lock → commit → HTTP → update `external_id`/`raw_response` (y compensar/marcar fallo si el gateway falla post-insert) | Medio | Tests start pay happy + fallo de gateway (payment pending o cleanup según diseño elegido) |
| **SH-04** | Rate limit en pay | Abuso de creación de sesiones de checkout | `throttle` en `POST /orders/{order}/pay` (por user/IP) | Bajo | Feature test 429 tras N requests (opcional si el stack lo permite fácil) |

### 4.2 Media prioridad (defensa en profundidad)

| ID | Tema | Problema | Solución propuesta | Esfuerzo | Tests |
|----|------|----------|--------------------|----------|-------|
| **SH-05** | Validar amount/currency del webhook vs `Payment` | Firma mitiga spoof; no hay chequeo de monto | Si el payload parseado expone amount/currency, rechazar approved (o no aplicar) si no matchea el `Payment` local; log estructurado | Medio | Fake payload con amount wrong → payment no approved |
| **SH-06** | Alertas ops en código | D25 / 2º approved / `payment_not_found` solo logs dispersos | Logs con keys estables + severidad clara; opcional canal `security`/`payments` | Bajo | Spy de Log en casos D25 y segundo approved (si se implementa detección) |
| **SH-07** | No loguear signed URLs completas | Link firmado = bearer del pedido | Auditar logs/notificaciones; loguear `order_id` + nombre de ruta, nunca query firmada | Bajo | Revisión estática / assert absence en tests si hay log spy |
| **SH-08** | Throttle webhooks (opcional) | DoS de CPU en verify/parse | Throttle generoso por IP en rutas webhook (cuidado de no bloquear reintentos legítimos del proveedor; preferir edge/WAF en prod) | Bajo–medio | Manual / feature opcional |

### 4.3 Baja prioridad / mejora continua

| ID | Tema | Notas |
|----|------|-------|
| **SH-09** | Tests de firma Stripe con payload sintético `t,v1` | Hoy el dominio confía mucho en Fake; contrato real de firma poco cubierto |
| **SH-10** | Minimizar/retener payloads en `payment_webhook_events` | Job de purga o truncado — política de datos + implementación |
| **SH-11** | Security headers / `URL::forceScheme('https')` en prod | Cross-cutting app, no solo F05 |

### Checklist de implementación (hardening)

Usar como mini-tasks fuera del DoD original de F05:

- [ ] SH-01 Bold prod reject empty signing secret *(ops/env al deploy; no required en dev)*  
- [x] SH-02 Stripe `Http::timeout`  
- [x] SH-03 Start pay: HTTP fuera del lock  
- [x] SH-04 Throttle `orders.pay`  
- [x] SH-05 Amount/currency check on approved webhook (si payload lo trae)  
- [x] SH-06 Logs/alert keys estables (D25, multi-approved, payment_not_found)  
- [x] SH-07 No full signed URLs in logs *(convención en Action; no había logs con URL firmada)*  
- [ ] SH-08 Throttle webhooks (evaluar vs WAF)  
- [ ] SH-09–SH-11 según capacidad  

Al cerrar un SH-\*: marcar aquí y, si aplica, test en `tests/Feature/Payments/`.

---

## 5. Decisiones de diseño / residuales **aceptados** (no “arreglar en silencio”)

Estos **no** son bugs olvidados. Vienen de D-ids / alcance F05. Cambiarlos exige **decisión de producto** y update de `requirements.md` / `design.md`.

| ID residual | Tema | Decisión de producto | Implicación de seguridad/ops | D-id / R |
|-------------|------|----------------------|------------------------------|----------|
| **RES-01** | Multi-intent / reintentos | Se permiten varios `Payment` `pending` y nuevo hosted checkout | Posible **doble cobro** real; el 2º approved no re-descuenta stock si la orden ya está `paid`, pero **no hay auto-refund** | D14, D15, R5 |
| **RES-02** | Approved sin stock | Payment `approved`, orden **no** `paid`, stock intacto | Dinero capturado sin fulfillment automático; ops manual / refund en dashboard del proveedor | D25, R15 |
| **RES-03** | Refund sin reponer stock | Webhook refund → orden `refunded` si estaba paid; **sin** `stock++` | Drift de inventario post-refund | D24 |
| **RES-04** | Sin reserva de stock en `pending` | Stock solo baja al paid | Ventana de over-sell entre pending y paid | F04 handoff + F05 |
| **RES-05** | Return URL no es fuente de verdad | Solo UX (`processing` / cancel) | Correcto frente a fraude de redirect; no “arreglar” marcando paid en return | D / R webhook-first |
| **RES-06** | Guest access por signed URL | Thank-you / pay sin login con firma temporal | El link es **bearer** del pedido (PII + pay) durante el TTL | D12, F04 |
| **RES-07** | Admin = allowlist de emails | `canAccessPanel` + `ADMIN_EMAILS`; sin RBAC fino | Compromiso de una cuenta admin = panel completo | F02 / panel |
| **RES-08** | No auto-refund en F05 | Fuera de alcance iniciar refunds desde la app | D25 y doble cobro dependen de runbook humano o feature futura | D2, alcance |

### Cómo tratar un RES-\*

| Acción | Cuándo |
|--------|--------|
| **Documentar + alertar (SH-06)** | Siempre aceptable sin cambiar D-id |
| **Cambiar comportamiento** | Solo tras update de requirements + design + tasks |
| **Mitigar en UX** sin contradecir D | p.ej. copy “no abras dos checkouts” — ok si no bloquea D15 |

---

## 6. Ops / go-live (humano; no es “falta de Action”)

Checklist fuera del código de feature, **obligatorio** antes de dinero real:

### Stripe
- [ ] `STRIPE_SECRET_KEY` live  
- [ ] Endpoint `https://{prod}/webhooks/stripe`  
- [ ] Eventos mínimos: `checkout.session.completed` (+ los que el design use)  
- [ ] `STRIPE_WEBHOOK_SECRET` (`whsec_…`) no vacío  
- [ ] Smoke cobro mínimo EUR end-to-end  

### Bold
- [ ] `BOLD_API_KEY` / `BOLD_SECRET_KEY` de producción  
- [ ] **`BOLD_WEBHOOK_SECRET` no string vacío** (unset → fallback a secret_key, o valor de prod)  
- [ ] Endpoint `https://{prod}/webhooks/bold`  
- [ ] `APP_URL` HTTPS público (signed URLs + callback rules)  
- [ ] Smoke cobro mínimo COP  

### App
- [ ] `APP_ENV=production`, `APP_DEBUG=false`  
- [ ] Config cache con env correcto  
- [ ] Monitoreo de logs: `payments.webhook.*`, `stock_conflict_d25`, `processing_error`  
- [ ] Runbook: D25, cancel+approved tardío, segundo payment approved, rotación de secrets  

---

## 7. Qué **no** hace falta “arreglar” (controles ya en buen estado)

| Control | Estado |
|---------|--------|
| Paid solo vía webhook verificado | OK |
| Firma sobre raw body | OK |
| CSRF except solo `webhooks/stripe` y `webhooks/bold` | OK |
| Order view/pay: owner policy **o** signed URL | OK |
| Stripe: secret vacío → reject | OK |
| Amount de start pay = `order.total` server-side | OK |
| Provider por moneda (no elegido por el cliente) | OK |
| Redelivery con evento **ya** `processed_at` → sin doble stock | OK (y retry incompleto arreglado) |
| Fake gateway en tests; no keys live en CI | OK |

---

## 8. Orden recomendado de trabajo

```text
1. SH-01 + SH-02 + SH-04     → barato, alto valor pre-prod
2. SH-03                     → robustez bajo carga
3. SH-05 + SH-06 + SH-07     → defensa + operabilidad
4. Ops checklist §6          → go-live
5. RES-* solo con producto   → nueva feature / delta de specs
```

---

## 9. Historial

| Fecha | Cambio |
|-------|--------|
| 2026-07-22 | Documento inicial tras audit F05 + skill `marketplace-security`; registra FIX-01…03 y backlog SH-/RES- |
| 2026-07-22 | Hardening en código (dev-safe): SH-02…SH-07 implementados; SH-01 dejado a ops de deploy |
