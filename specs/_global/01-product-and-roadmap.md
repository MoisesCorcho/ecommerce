# Producto y roadmap

## Visión

Ecommerce **single-vendor** de bolsos/accesorios (catálogo del orden de decenas a cientos de SKUs), con:

- Variantes (p. ej. color), inventario y precios **multi-moneda** (COP / EUR)
- Carrito, checkout, órdenes con **snapshots** de lo comprado
- Pagos duales: **Bold** (Colombia) y **Stripe** (Europa)
- Panel de administración (Filament) y experiencia de tienda (Livewire / frontend del proyecto)

No es un marketplace multi-vendedor. El path del repo puede decir “marketplace”; el producto es **un solo vendedor**.

## Principios de producto

1. **Dominio primero, UI después.** El grafo Eloquent (ya en `app/Models`) es la base; las features de producto se montan sobre él, no al revés.
2. **Snapshots en historial comercial.** Lo que la orden “vio” (ítems, precios, dirección) se congela; no reconstruir facturas solo con FKs mutables.
3. **Dinero en enteros.** Sin floats. COP en pesos enteros; EUR en centavos (minor units). Documentar unidad en campos/DTOs.
4. **Vocabulario de dominio en enums PHP.** String en DB + cast a `*Enum`; nunca `ENUM` nativo MySQL.
5. **AuthZ en el borde.** Policies/gates/Spatie en Controllers, Livewire, Filament o al inicio del Action; no esparcido en Services de bajo nivel.
6. **Integraciones detrás de puertos.** Pagos y I/O externo vía `*Interface` + `*Gateway`; fakes en tests.
7. **Una Action = un caso de uso.** Orquestación de writes multi-model con `DB::transaction` en el Action.

Detalle de código: **solo** en project-conventions / `AGENTS.md`. Aquí no se repite.

## No-objetivos (explícitos)

- Multi-vendor / comisiones por tienda / storefront por vendedor
- Catálogos administrados 100 % “runtime” donde un enum fijo alcanza (colores de producto de negocio fijo → enum o tabla según caso; no inventar capas)
- Reimplementar convenciones Laravel Boost dentro de `specs/`

## Fundación de dominio (ya hecha)

Fuera del roadmap de features de producto, pero **prerequisito global**:

| Entrega | Estado | Ubicación |
|---------|--------|-----------|
| Enums de dominio | Completa | `app/Enums/*Enum.php` |
| Migrations + models + factories | Completa | `database/migrations`, `app/Models`, `database/factories` |
| Tests de grafo de dominio | Completa | `tests/Feature/Domain/` |
| Esquema en código (models/enums/migrations) | Completa | `app/Models`, `app/Enums`, `database/migrations` |

Sin esta fundación no se implementan features de catálogo/comercio.

## Roadmap de features

Estados: ver convención en [`00-how-to-use.md`](00-how-to-use.md).  
Rutas de specs se crean al iniciar cada feature (`specs/features/<slug>/`).

| ID | Feature | Fase | Estado | Prerequisitos |
|----|---------|------|--------|---------------|
| F01 | Catálogo (categorías, productos, variantes, precios, imágenes) | 0 · Fundación comercio | No iniciada | Fundación de dominio |
| F02 | Cuentas y direcciones | 0 · Fundación comercio | No iniciada | Fundación de dominio |
| F03 | Carrito | 1 · Compra | No iniciada | F01 |
| F04 | Checkout y órdenes | 1 · Compra | No iniciada | F01, F02, F03 |
| F05 | Pagos (Stripe / Bold + webhooks) | 2 · Cobro | No iniciada | F04 |
| F06 | Cupones y redenciones | 2 · Cobro | No iniciada | F03 o F04 (definir en specs: carrito vs orden) |
| F07 | Reviews | 3 · Post-compra | No iniciada | F01; idealmente F04/F05 si se exige compra |
| F08 | Wishlist | 3 · Post-compra | No iniciada | F01, F02 |

Sincronizar la columna **Estado** con el bloque `> Estado:` de cada `requirements.md` cuando exista.

### Notas de alcance (evitar solapamiento)

#### F01 Catálogo

- **Incluye:** CRUD admin (Filament) y/o lectura pública del catálogo; categorías; productos; variantes; precios por moneda; imágenes.
- **No incluye:** carrito, stock “reservado” en checkout, ni pricing de cupones (F03/F04/F06).
- Primera feature natural: sin variantes con precio no hay carrito ni orden.

#### F02 Cuentas y direcciones

- **Incluye:** datos de usuario necesarios para compra (p. ej. teléfono ya en schema), CRUD de `addresses`.
- **No incluye:** checkout completo ni métodos de pago guardados de terceros (eso es F05 si aplica).

#### F03 Carrito

- Depende de variantes publicables y precios (F01).
- Guest cart vs user cart: decidir en requirements de F03 (tabla Decisiones de producto), no en silencio.

#### F04 Checkout y órdenes

- Crea `orders` / `order_items` con snapshots; consume carrito y dirección.
- Transición de estados de orden: alinear con `OrderStatusEnum`; no inventar estados fuera del enum sin actualizar enum + esquema doc.

#### F05 Pagos

- `PaymentGatewayInterface` + gateways; `payments` + `payment_webhook_events`.
- Idempotencia de webhooks y mapeo a `PaymentStatusEnum` / efectos en orden: obligatorios en AC de error.

#### F06 Cupones

- Puede validarse en carrito y/o al cerrar orden; una sola fuente de verdad del descuento aplicado en el snapshot de orden.

#### F07 / F08

- Reviews y wishlist no bloquean el path de compra; priorizar tras F04/F05 salvo necesidad de demo.

## Orden de corrección / implementación

1. F01 → F02 (pueden solaparse en branches si no compiten por los mismos archivos de dominio críticos).
2. F03 solo con F01 estable.
3. F04 con F01+F02+F03.
4. F05 sobre F04.
5. F06 cuando el punto de aplicación (carrito vs orden) esté decidido.
6. F07/F08 cuando el catálogo (y, si aplica, la compra) estén listos.

## Cuando una feature toca el esquema

1. Migration + model + factory + casts (+ enum si aplica).
2. Reflejar en `design.md` / tasks de la feature.
3. No documentar el modelo completo otra vez en `_global` (la verdad está en el código).
