# Checklist de Pruebas Manuales y Edge Cases (QA Humano)

Este documento guía la verificación manual paso a paso de todos los flujos de la tienda **Leen Handbags**, incluyendo casos borde (*edge cases*) y comportamientos esperados.

---

## 1. Credenciales y Setup Recomendado para Pruebas

- **Servidor local:** `http://localhost` (ejecutar `vendor/bin/sail up -d`)
- **Usuario Administrador:** `admin@leen.com` (o crear via `RoleAndAdminBackfillSeeder`)
- **Usuario Comprador:** Crear un usuario de prueba registrándose en `/register`.
- **Modo Sandbox / Pasarelas:** Asegurar llaves de prueba en `.env` (Stripe `sk_test_...` y Bold).

---

## 2. Matriz de Verificación Manual por Módulos

### Módulo 1: Catálogo y Navegación Pública
- [ ] **1.1. Home (`/`)**: Verificar héroe, marcas/categorías principales y productos destacados.
- [ ] **1.2. Catálogo (`/products`)**:
  - [ ] Filtrar por categoría y rango de precio.
  - [ ] Cambiar de moneda (COP vs EUR) en el header/selector y verificar actualización instantánea de precios.
  - [ ] Probar ordenamiento (Precio menor/mayor, novedades).
- [ ] **1.3. Ficha de Producto (PDP) (`/products/{slug}`)**:
  - [ ] Cambiar entre variantes (color/tamaño) y verificar actualización de precio, SKU e imagen.
  - [ ] **Edge Case (Agotado)**: Verificar que productos con stock 0 muestren la insignia "Agotado" y deshabiliten el botón "Agregar al carrito".
  - [ ] **Edge Case (Preventa)**: Verificar que productos en preventa permitan agregar al carrito adecuadamente.
  - [ ] Probar botón de favoritos (agregar/quitar de wishlist).

---

### Módulo 2: Carrito y Promociones (Cupones)
- [ ] **2.1. Carrito (`/cart`)**:
  - [ ] Agregar 1 o más productos desde la PDP.
  - [ ] Modificar cantidades (incrementar/decrementar) y verificar recalculo de subtotales.
  - [ ] Eliminar un producto del carrito.
  - [ ] **Edge Case (Límite de cantidad)**: Intentar agregar más de 99 unidades de un ítem (debe limitar a 99).
  - [ ] **Edge Case (Stock excedido)**: Intentar subir la cantidad por encima del stock disponible en BD.
- [ ] **2.2. Aplicación de Cupones**:
  - [ ] Ingresar un cupón válido (ej. `SUMMER10` o cupones creados en admin) y verificar descuento reflejado en el resumen.
  - [ ] **Edge Case (Cupón vencido/inválido)**: Probar un código inexistente o expirado (debe mostrar mensaje de error amigable).
  - [ ] **Edge Case (Monto mínimo no alcanzado)**: Aplicar un cupón que exige compra mínima superior al carrito actual.

---

### Módulo 3: Checkout y Proceso de Pedido
- [ ] **3.1. Formulario de Checkout (`/checkout`)**:
  - [ ] **Edge Case (Carrito vacío)**: Intentar ingresar directamente a `/checkout` con carrito vacío (debe redirigir a `/cart`).
  - [ ] Completar checkout como **Invitado** (email, datos de envío y contacto).
  - [ ] Completar checkout como **Usuario Autenticado** (usar dirección guardada o ingresar nueva).
- [ ] **3.2. Página de Confirmación (`/orders/{order}/thank-you`)**:
  - [ ] Verificar resumen del pedido, datos de envío, estado `Pendiente` y botón "Pagar ahora".
  - [ ] **Edge Case (IDOR / Acceso no autorizado)**: Intentar acceder a la URL `/orders/{order}/thank-you` de una orden perteneciente a otro usuario (debe retornar `403 Forbidden` a menos que sea URL firmada).

---

### Módulo 3B: Pasarelas de Pago de Prueba (Bold & Stripe)
- [ ] **3.3. Pagos en COP con Bold (Sandbox)**:
  - [ ] Crear un pedido en COP y hacer clic en **"Pagar ahora"**. Verificar redirección al checkout hospedado por Bold.
  - [ ] Completar el pago con datos de prueba en Bold.
  - [ ] Verificar retorno a la tienda y actualización automática del pedido a estado `Paid` (Pagado) tras el webhook.
  - [ ] **Edge Case (Pago Cancelado / Reintentar)**: Simular cancelación en Bold, regresar a la orden y comprobar que permanece en `Pending` permitiendo reintentar.
- [ ] **3.4. Pagos en EUR con Stripe (Test Mode)**:
  - [ ] Cambiar divisa a EUR y realizar checkout.
  - [ ] Hacer clic en **"Pagar ahora"** y verificar redirección a Stripe Checkout.
  - [ ] Probar tarjeta de éxito `4242 4242 4242 4242` (CVC `123`, fecha futura).
  - [ ] Verificar retorno a `/thank-you` y cambio de estado a `Paid`.
  - [ ] **Edge Case (Tarjeta Declinada)**: Probar tarjeta `4000 0000 0000 0002` y verificar que la pasarela muestre el rechazo sin corromper la orden.

---

### Módulo 4: Autenticación y Área de Cliente
- [ ] **4.1. Registro y Login (`/register`, `/login`)**:
  - [ ] Crear cuenta nueva y verificar inicio de sesión automático.
  - [ ] **Edge Case (Credenciales erróneas)**: Ingresar clave incorrecta (debe mostrar error de validación).
  - [ ] Recuperación de contraseña (`/forgot-password`).
- [ ] **4.2. Mi Cuenta (`/profile`)**:
  - [ ] Actualizar datos personales y cambiar contraseña.
- [ ] **4.3. Libreta de Direcciones (`/profile/addresses`)**:
  - [ ] Crear nueva dirección, editarla y marcar como predeterminada.
- [ ] **4.4. Mis Pedidos (`/profile/orders`)**:
  - [ ] Ver lista de pedidos realizados y entrar al detalle de una orden `/profile/orders/{order_id}`.
  - [ ] **Edge Case (IDOR en Pedidos)**: Intentar cambiar la URL con el ID de una orden de otro usuario (debe retornar 403).
- [ ] **4.5. Lista de Deseos (`/wishlist`)**:
  - [ ] Verificar que los productos agregados a favoritos aparezcan y puedan ser removidos o movidos al carrito.

---

### Módulo 5: Administración (Panel de Filament en `/admin`)
- [ ] **5.1. Acceso y Roles**:
  - [ ] **Edge Case (Segregación de Roles)**: Intentar ingresar a `/admin` logueado con una cuenta de cliente `customer` (debe ser rechazado con 403).
  - [ ] Iniciar sesión como Administrador (`admin@leen.com`).
- [ ] **5.2. Gestión de Productos (`/admin/products`)**:
  - [ ] Crear un producto con variantes, imágenes y precio en COP/EUR.
  - [ ] Editar stock y toggle de preventa/activo.
- [ ] **5.3. Gestión de Categorías (`/admin/categories`)**:
  - [ ] Crear/editar categoría.
- [ ] **5.4. Gestión de Cupones (`/admin/coupons`)**:
  - [ ] Crear un nuevo cupón (Fijo o Porcentaje), definir fecha de vencimiento y límites de uso.
- [ ] **5.5. Gestión de Pedidos (`/admin/orders`)**:
  - [ ] Cambiar el estado de un pedido (de `Pending` a `Processing` / `Completed` / `Cancelled`).
  - [ ] Verificar que al cancelar un pedido con cupón se libere el uso del cupón.
- [ ] **5.6. Moderación de Reseñas (`/admin/reviews`)**:
  - [ ] Aprobar o rechazar reseñas enviadas por compradores.
