# F05 — Pagos · Tasks

> **ID:** F05 · **Slug:** `05-payments`  
> **Requirements:** [`requirements.md`](requirements.md) · **Design:** [`design.md`](design.md)  
> **Convenciones:** [`AGENTS.md`](../../../AGENTS.md) · **Calidad:** [`02-feature-quality.md`](../../_global/02-feature-quality.md)

**Alcance DoD:** dominio Payments + gateways + webhooks + entrypoint pay + botón mínimo + payments en Order admin + tests.  
**Fuera de DoD:** auth storefront; iniciar refunds; reponer stock; emails; UI de marca; PaymentResource global; fulfillment.

**Estado de implementación:** no iniciada (solo specs).

---

## 1. Fundación config, i18n, contratos y errores

- [ ] 1.1 Config de pagos + keys en `.env.example` (`STRIPE_*`, `BOLD_*`). _(cubre D32, R1)_
- [ ] 1.2 `lang/en/payments.php` + `lang/es/payments.php` (+ navigation/labels admin si aplica). _(cubre R8–R11, R15)_
- [ ] 1.3 Verificar/completar labels enums payment en `lang/*/enums.php`. _(cubre R6)_
- [ ] 1.4 Excepciones de dominio en `app/Exceptions/Payments/`. _(cubre R8, R9, R12, R15)_
- [ ] 1.5 `PaymentGatewayInterface` + DTOs en `app/DTOs/Payments/`. _(cubre D30, R1, R17)_
- [ ] 1.6 `FakePaymentGateway` (o equivalente) para tests + bind en testing. _(cubre D31)_

## 2. Gateways

- [ ] 2.1 `StripePaymentGateway` (hosted checkout + verify webhook signature). _(cubre D7, D11, D28, R1, R18)_
- [ ] 2.2 `BoldPaymentGateway` (hosted checkout + verify webhook signature). _(cubre D7, D11, D28, R1, R18)_
- [ ] 2.3 Resolver gateway por `PaymentProviderEnum` / moneda. _(cubre D7, D8, R18)_

## 3. Dominio — iniciar pago

- [ ] 3.1 `StartOrderPaymentAction`: lock order, solo `pending`, revalidate stock, crear `Payment` pending con `amount=order.total`, llamar gateway, guardar `external_id`. _(cubre R1, R2, R8, R9, R17, R18, R20, R21, R22, D10, D14, D26)_
- [ ] 3.2 Policy/ability `pay` (o reutilizar view + reglas) + signed access guest. _(cubre R2, R10, R11, D12, D13)_
- [ ] 3.3 Ruta `POST /orders/{order}/pay` + respuesta redirect a hosted URL. _(cubre R1, R2, D36)_
- [ ] 3.4 Link/botón mínimo “Pagar” en thank-you/order show si `pending`. _(cubre D1, R1, R5)_

## 4. Dominio — webhooks y efectos

- [ ] 4.1 Rutas `POST /webhooks/stripe` y `POST /webhooks/bold` (CSRF exempt, raw body). _(cubre D36, R3)_
- [ ] 4.2 Persistencia `PaymentWebhookEvent` + unique idempotencia `(provider, event_id)`. _(cubre R3, R13, D27, D29)_
- [ ] 4.3 Verificación de firma; rechazo firma inválida. _(cubre R12, D28)_
- [ ] 4.4 Outcome **approved** + stock OK → payment approved, order `paid`+`paid_at`, decrement stock atómico. _(cubre R3, D17, D23)_
- [ ] 4.5 Outcome **approved** + stock FAIL (D25) → payment approved, order no paid, stock intacto, señal ops. _(cubre R15, D25)_
- [ ] 4.6 Outcome **approved** + order `cancelled` → no paid, no stock. _(cubre R14, D22)_
- [ ] 4.7 Outcome **declined** → payment declined, order pending. _(cubre R16, D18)_
- [ ] 4.8 Outcome **refunded** → payment refunded, order `refunded` si estaba paid; sin reponer stock. _(cubre R7, D19, D24)_
- [ ] 4.9 Order ya `paid`: no re-descuento ni re-start pago. _(cubre R21)_

## 5. Return UX mínima

- [ ] 5.1 Success return: thank-you/order con estado “confirmando” o estado real; **no** marcar paid en el GET. _(cubre R4, D16, D33)_
- [ ] 5.2 Cancel return: mensaje no completado + CTA reintentar si pending. _(cubre R19, D34)_

## 6. Filament admin

- [ ] 6.1 Mostrar payments en vista de Order (relation/infolist). _(cubre R6, D3)_
- [ ] 6.2 Confirmar que cancel pending (F04) sigue disponible y es coherente con R14. _(cubre R14, D22)_

## 7. Tests (PHPUnit)

- [ ] 7.1 Start pay user dueño: crea payment pending, provider EUR→Stripe / COP→Bold, amount=total. _(cubre R1, R17, R18)_
- [ ] 7.2 Start pay guest signed OK. _(cubre R2)_
- [ ] 7.3 Start pay: foreign user / guest sin signed → deny. _(cubre R10, R11)_
- [ ] 7.4 Start pay: order no pending → fail. _(cubre R8, R21)_
- [ ] 7.5 Start pay: stock insuficiente → fail sin llamar gateway (o fake no invocado). _(cubre R9)_
- [ ] 7.6 Webhook approved stock OK → paid + stock−. _(cubre R3)_
- [ ] 7.7 Webhook redelivery idempotent. _(cubre R13)_
- [ ] 7.8 Webhook approved D25 stock fail. _(cubre R15)_
- [ ] 7.9 Webhook approved order cancelled. _(cubre R14)_
- [ ] 7.10 Webhook declined. _(cubre R16)_
- [ ] 7.11 Webhook refunded sin restore stock. _(cubre R7)_
- [ ] 7.12 Firma inválida. _(cubre R12)_
- [ ] 7.13 HTTP POST pay redirect (feature). _(cubre R1, D36)_
- [ ] 7.14 Reintento crea segundo payment si sigue pending. _(cubre R5, D14, D15)_
- [ ] 7.15 Filament order view muestra payments (si aplica convención del repo). _(cubre R6)_

## 8. Cierre de calidad

- [ ] 8.1 Tests del alcance F05 en verde vía Sail.
- [ ] 8.2 Pint en PHP tocado (`vendor/bin/sail bin pint --dirty --format agent`).
- [ ] 8.3 Estado F05 = **Completa** en requirements + roadmap al cerrar implementación.

---

## Mapa de trazabilidad (DoD F05)

| Criterio | Tareas | DoD F05 |
|----------|--------|---------|
| R1 | 1.1, 1.5–1.6, 2.x, 3.1–3.4, 7.1, 7.13 | Sí |
| R2 | 3.1–3.3, 7.2 | Sí |
| R3 | 4.1–4.4, 7.6–7.7 | Sí |
| R4 | 5.1 | Sí |
| R5 | 3.4, 4.7, 7.14 | Sí |
| R6 | 1.3, 6.1, 7.15 | Sí |
| R7 | 4.8, 7.11 | Sí |
| R8 | 3.1, 7.4 | Sí |
| R9 | 3.1, 7.5 | Sí |
| R10 | 3.2, 7.3 | Sí |
| R11 | 3.2, 7.3 | Sí |
| R12 | 4.3, 7.12 | Sí |
| R13 | 4.2, 7.7 | Sí |
| R14 | 4.6, 6.2, 7.9 | Sí |
| R15 | 4.5, 7.8 | Sí |
| R16 | 4.7, 7.10 | Sí |
| R17 | 3.1, 7.1 | Sí |
| R18 | 2.3, 3.1, 7.1 | Sí |
| R19 | 5.2 | Sí |
| R20 | 3.1 (no auto desde create order) | Sí |
| R21 | 3.1, 4.9, 7.4 | Sí |
| R22 | 3.1, factories/casts existentes | Sí |

---

## Definition of Done (implementación)

1. Todas las tareas 1–7 marcadas y R1–R22 con test o cobertura explícita en mapa.
2. `vendor/bin/sail artisan test --compact` del filtro Payments/Orders pay en verde.
3. Pint limpio en PHP dirty.
4. Sin secrets en git; `.env.example` actualizado.
5. Actualizar `requirements.md` y roadmap a **Completa**.
