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
| Enums de dominio | Completa | `app/Enums/{Area}/*Enum.php` (tipo + área) |
| Migrations + models + factories | Completa | `database/migrations`, `app/Models`, `database/factories` |
| Tests de grafo de dominio | Completa | `tests/Feature/Domain/` |
| Esquema en código (models/enums/migrations) | Completa | `app/Models`, `app/Enums`, `database/migrations` |

Sin esta fundación no se implementan features de catálogo/comercio.

## Roadmap de features

Estados: ver convención en [`00-how-to-use.md`](00-how-to-use.md).  
Rutas de specs se crean al iniciar cada feature (`specs/features/<slug>/`).

| ID | Feature | Fase | Estado | Prerequisitos |
|----|---------|------|--------|---------------|
| F01 | Catálogo **admin** (Filament: categorías, productos, variantes, precios, imágenes) | 0 · Fundación comercio | Completa | Fundación de dominio |
| F01-S | Storefront catálogo (UI pública; manual de marca) | 0 · Fundación comercio | No iniciada (diferido) | F01; manual de marca |
| F02 | Cuentas y direcciones (**admin** Filament: users + addresses; sin Livewire storefront) | 0 · Fundación comercio | Completa | Fundación de dominio |
| F03 | Carrito | 1 · Compra | Completa | F01 |
| F04 | Checkout y órdenes | 1 · Compra | Completa | F01, F02, F03 |
| F05 | Pagos (Stripe / Bold + webhooks) | 2 · Cobro | Completa | F04 |
| F06 | Cupones y redenciones | 2 · Cobro | Completa | F03, F04 (punto de aplicación: checkout/confirm; specs en `06-coupons`) |
| F07 | Reviews | 3 · Post-compra | Lista para implementar | F01; F04/F05 (compra paid+ para elegibilidad) |
| F08 | Wishlist | 3 · Post-compra | No iniciada | F01, F02 |

Sincronizar la columna **Estado** con el bloque `> Estado:` de cada `requirements.md` cuando exista.

### Notas de alcance (evitar solapamiento)

#### F01 Catálogo (admin)

- **Incluye:** CRUD admin (Filament); categorías; productos; variantes; precios por moneda; imágenes; gate de panel; invariante de publicación.
- **No incluye:** storefront Livewire / UI de tienda (eso es **F01-S**); carrito; stock reservado; cupones (F03/F04/F06).
- Primera feature natural de **datos**: sin variantes con precio no hay carrito ni orden.
- Specs: `specs/features/01-catalog/` (R11–R13, R17 marcados **Diferido**).

#### F01-S Storefront catálogo

- **Incluye:** listado y detalle públicos (Livewire/UI) cuando exista manual de marca; criterios diferidos R11–R13, R17 de F01.
- **No incluye:** rehacer el admin ni cambiar el grafo de dominio salvo gaps reales.
- Puede reutilizar scopes `publishedForStorefront` y/o código Livewire ya presente en el repo (adelanto no-DoD de F01).

#### F02 Cuentas y direcciones

- **Incluye:** CRUD **admin Filament** de usuarios (name, email, phone, password) y de `addresses` (RelationManager / ownership por user); invariante de una dirección `is_default` por usuario; Actions/DTOs; reutiliza gate `admin_emails`.
- **No incluye:** Livewire storefront (login/registro/perfil/libreta del comprador); checkout completo; métodos de pago guardados de terceros (F05 si aplica).
- Specs: `specs/features/02-accounts-addresses/`.

#### F03 Carrito

- Depende de variantes publicables y precios (F01).
- Specs: `specs/features/03-cart/`.
- Decisiones de producto cerradas en requirements (D1–D14): guest + user + merge sumando; Actions + entrypoint mínimo (sin Filament ni storefront de marca); moneda en cart con reprecio live y bloqueo si falta precio; stock al mutar sin reserva; upsert sumando, qty 0 = remove, techo `min(stock, 99)`.

#### F04 Checkout y órdenes

- Specs: `specs/features/04-checkout-orders/`.
- Crea `orders` / `order_items` con snapshots; consume carrito y dirección.
- Decisiones cerradas en requirements (D1–D28): guest + user; revalidar al entrar y confirmar; **sin** descontar stock (F05 al pagar); carrito se vacía al crear; orden solo `pending`; admin `pending→cancelled`; sin paso de pago; envío estándar configurable; signed URL guest; Filament de pedidos; thank-you simple; perfil “mis pedidos” diferido.
- Transición de estados de orden: alinear con `OrderStatusEnum`; no inventar estados fuera del enum sin actualizar enum + esquema doc.

#### F05 Pagos

- Specs: `specs/features/05-payments/`.
- Decisiones cerradas en requirements (D1–D43): hosted checkout; COP→Bold / EUR→Stripe; webhook como fuente de verdad de `paid`; multi-intento `payments`; stock al `approved` (D25 si falta stock: payment approved sin order paid); guest signed pay; auth storefront out; refunds solo por webhook sin reponer stock.
- `PaymentGatewayInterface` + gateways; `payments` + `payment_webhook_events`.
- Idempotencia de webhooks y mapeo a `PaymentStatusEnum` / efectos en orden: obligatorios en AC de error.

#### F06 Cupones

- Specs: `specs/features/06-coupons/`.
- Decisiones cerradas: aplicar en **checkout/confirm** (no en carrito); preview sin consumir; un cupón por orden; `percentage`/`fixed`; descuento sobre subtotal; consume al crear `pending`, libera al cancel pending; no libera en refund; snapshot `coupon_redemptions.code`; Filament sí; UI marca out.
- Fuente de verdad del descuento: `orders.discount` + `orders.coupon_id` + redención (F05 cobra total ya fijado).

#### F07 Reviews

- Specs: `specs/features/07-reviews/`.
- Decisiones cerradas: solo compradores autenticados; elegibilidad orden `paid|processing|shipped|delivered`; moderación manual (`is_approved` default false); edit del autor re-modera; una review por user+product; Filament moderación (sin admin create); enganche PDP con estilo de marca del storefront existente; sin migración de schema; sin guests/fotos/auto-approve/denormalize.

#### F07 / F08

- Reviews y wishlist no bloquean el path de compra; priorizar tras F04/F05 salvo necesidad de demo.

## Orden de corrección / implementación

1. F01 (admin) → F02 (pueden solaparse en branches si no compiten por archivos críticos).
2. F01-S (storefront UI) cuando haya manual de marca; **no** bloquea F03 si el admin ya carga catálogo.
3. F03 solo con F01 admin estable (datos publicables en dominio).
4. F04 con F01+F02+F03.
5. F05 sobre F04.
6. F06 cuando el punto de aplicación (carrito vs orden) esté decidido.
7. F07/F08 cuando el catálogo (y, si aplica, la compra) estén listos.

## Cuando una feature toca el esquema

1. Migration + model + factory + casts (+ enum si aplica).
2. Reflejar en `design.md` / tasks de la feature.
3. No documentar el modelo completo otra vez en `_global` (la verdad está en el código).
