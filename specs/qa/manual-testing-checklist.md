# Checklist de Pruebas Manuales y Edge Cases (QA Humano)

Este documento guía la verificación manual paso a paso de todos los flujos de la tienda **Leen**, estructurado en pasos **atómicos, secuenciales y basados en el flujo real de uso**.

---

## 1. Credenciales y Setup Recomendado para Pruebas

- **Servidor local:** `http://localhost` (ejecutar `vendor/bin/sail up -d`)
- **Usuario Administrador:** `admin@leen.com` (o crear via `RoleAndAdminBackfillSeeder`)
- **Usuario Comprador:** Crear un usuario de prueba registrándose en `/register`.
- **Modo Sandbox / Pasarelas:** Asegurar llaves de prueba en `.env` (Stripe `sk_test_...` y Bold).

---

## 2. Matriz de Verificación Manual por Módulos

### Módulo 1: Catálogo, Páginas Informativas y Navegación Pública

- [x] **1.1. Home (`/`)**:
  - [x] **1. Hero Section**: Verificar banner principal, título, botón *"Comprar la colección"* y redirección a la tienda (`/products`).
  - [x] **2. Carrusel de Categorías**: Verificar carrusel horizontal con flechas navegables y que al hacer clic en una categoría redirija a `/products?category={slug}` aplicando el filtro.
  - [ ] **3. Productos Destacados**: Verificar carrusel/grilla de productos destacados *(Diferido: pendiente implementación de toggle de destacado en admin)*.
  - [x] **4. Nuestra Historia**: Verificar bloque de historia (texto e imagen) y botón *"Lee nuestra historia"* con redirección a `/about-us`.
  - [x] **5. ¿Por qué Leen?**: Verificar los 4 pilares de beneficios con sus íconos Heroicons (`needle`, `sparkles`, `clock`, `map-pin`).
  - [x] **6. Instagram y Footer**: Verificar sección "Sigue el viaje" con enlace a Instagram oficial y eslogan en footer (*"Sweeter than honey"*).

- [x] **1.2. Quiénes Somos / About Us (`/about-us`)**:
  - [x] **1. Hero Section**: Verificar título *"Nuestra esencia"*, imagen de fondo y subtítulo estilizado *"Sweeter than honey"* con legibilidad mejorada.
  - [x] **2. Manifiesto**: Verificar párrafos e imágenes de la sección "Nuestra Historia".
  - [x] **3. Pilares de la Marca**: Verificar la sección "Nuestro honeycomb world" y sus 4 pilares (*Libertad de Expresión*, *Arquitectura de Panales*, *Atemporalidad Consciente*, *Espíritu de Colmena*).
  - [x] **4. Nuestra Promesa**: Verificar la sección de cierre y la tipografía estilizada *honeys* (*La Belle Aurore*).

- [x] **1.3. Contacto (`/contact`)**:
  - [x] **1. Información Pública**: Verificar email oficial (`leenhandbags@gmail.com`) y enlace a TikTok (`https://www.tiktok.com/@leenhandbags`).
  - [x] **2. Envío de Formulario**: Completar campos (Nombre, Email, Mensaje), enviar y verificar notificación de éxito.
  - [x] **3. Edge Case (Throttling / Límite de tasa)**: Intentar enviar más de 3 mensajes seguidos para confirmar bloqueo temporal de seguridad.

- [ ] **1.4. Catálogo (`/products`)**:
  - [ ] **1. Filtro por Categoría**: Seleccionar una categoría en el panel lateral y verificar filtrado de productos.
  - [ ] **2. Filtro por Precio**: Ajustar rango mínimo y máximo de precio y comprobar actualización instantánea.
  - [ ] **3. Cambio de Moneda**: Cambiar selector de moneda en el header (COP ↔ EUR) y verificar actualización instantánea de precios.
  - [ ] **4. Ordenamiento**: Probar ordenamiento por *Precio: menor a mayor*, *Precio: mayor a menor* y *Más recientes*.

- [ ] **1.5. Ficha de Producto - PDP (`/products/{slug}`)**:
  - [ ] **1. Selección de Variantes**: Cambiar entre opciones de color y talla, comprobando actualización de precio, SKU e imagen.
  - [ ] **2. Botón de Favoritos**: Marcar y desmarcar el producto como favorito (Wishlist).
  - [ ] **3. Edge Case (Agotado)**: Verificar que productos con stock 0 muestren la insignia "Agotado" y deshabiliten el botón de agregar al carrito.
  - [ ] **4. Edge Case (Preventa)**: Verificar que productos en condición de preventa muestren su insignia y permitan agregar al carrito adecuadamente.

- [ ] **1.6. Fallback de Rutas Inexistentes**:
  - [ ] **1. Redirección 404**: Navegar a una URL no registrada (ej. `/home` o `/pagina-rara`) y comprobar redirección automática a la portada `/`.

---

### Módulo 2: Carrito y Promociones (Cupones)

- [ ] **2.1. Carrito (`/cart`)**:
  - [ ] **1. Agregar al Carrito**: Añadir un producto desde la PDP y navegar a `/cart` para verificar su presencia.
  - [ ] **2. Modificar Cantidad**: Incrementar y decrementar unidades comprobando la actualización del subtotal.
  - [ ] **3. Eliminar Producto**: Hacer clic en el botón de eliminar ítem y comprobar que la lista y totales se recalculen.
  - [ ] **4. Edge Case (Límite máximo)**: Intentar subir la cantidad por encima de 99 unidades (debe limitar a 99).
  - [ ] **5. Edge Case (Límite de Stock)**: Intentar solicitar más unidades del stock disponible en la base de datos (debe mostrar mensaje de advertencia).

- [ ] **2.2. Aplicación de Cupones**:
  - [ ] **1. Cupón Válido**: Ingresar un código promocional activo y comprobar que el descuento se reste correctamente en el resumen.
  - [ ] **2. Edge Case (Cupón Inválido / Expirado)**: Probar un código inexistente o fuera de vigencia y verificar el mensaje de error.
  - [ ] **3. Edge Case (Monto Mínimo No Alcanzado)**: Aplicar un cupón con condición de monto mínimo sobre un carrito de menor valor.

---

### Módulo 3: Checkout y Proceso de Pedido

- [ ] **3.1. Formulario de Checkout (`/checkout`)**:
  - [ ] **1. Edge Case (Carrito Vacío)**: Intentar ingresar directamente a `/checkout` sin productos (debe redirigir a `/cart`).
  - [ ] **2. Checkout como Invitado**: Completar datos de envío, contacto y dirección como usuario sin sesión activa.
  - [ ] **3. Checkout como Autenticado**: Completar checkout con sesión iniciada utilizando una dirección guardada.

- [ ] **3.2. Página de Confirmación (`/orders/{order}/thank-you`)**:
  - [ ] **1. Verificación de Resumen**: Comprobar datos de entrega, resumen de productos, total y estado inicial `Pendiente`.
  - [ ] **2. Edge Case (Seguridad IDOR)**: Intentar abrir la URL de confirmación de un pedido perteneciente a otro usuario (debe retornar 403 Forbidden a menos que tenga firma válida).

---

### Módulo 3B: Pasarelas de Pago de Prueba (Bold & Stripe)

- [ ] **3.3. Pasarela Bold (COP - Sandbox)**:
  - [ ] **1. Iniciar Pago**: Hacer clic en "Pagar ahora" en una orden en COP y verificar redirección a la pasarela Bold.
  - [ ] **2. Transacción Exitosa**: Completar el pago con datos sandbox de prueba y comprobar el retorno a la tienda.
  - [ ] **3. Verificación Webhook**: Confirmar que el estado del pedido cambie automáticamente a `Paid` (Pagado).
  - [ ] **4. Edge Case (Cancelación en Pasarela)**: Simular cancelación en la ventana de Bold, volver a la tienda y verificar que la orden siga en `Pending` permitiendo reintentar.

- [ ] **3.4. Pasarela Stripe (EUR - Test Mode)**:
  - [ ] **1. Iniciar Pago en EUR**: Cambiar divisa a EUR, realizar checkout e iniciar pago hacia Stripe Checkout.
  - [ ] **2. Tarjeta Exitosa**: Usar tarjeta de prueba `4242 4242 4242 4242` y comprobar retorno a `/thank-you` con estado `Paid`.
  - [ ] **3. Edge Case (Tarjeta Declinada)**: Probar tarjeta `4000 0000 0000 0002` y verificar que la pasarela reporte el rechazo sin corromper la orden.

---

### Módulo 4: Autenticación y Área de Cliente

- [ ] **4.1. Autenticación (`/login`, `/register`, `/forgot-password`)**:
  - [ ] **1. Registro**: Crear una cuenta nueva en `/register` y verificar inicio de sesión automático.
  - [ ] **2. Login Incorrecto**: Intentar ingresar con contraseña errónea (debe mostrar mensaje de validación).
  - [ ] **3. Recuperación de Clave**: Solicitar enlace de restablecimiento en `/forgot-password`.

- [ ] **4.2. Mi Perfil (`/profile`)**:
  - [ ] **1. Datos Personales**: Actualizar nombre, email y contraseña desde el panel de usuario.

- [ ] **4.3. Libreta de Direcciones (`/profile/addresses`)**:
  - [ ] **1. Gestión de Direcciones**: Crear una nueva dirección de envío, editar sus datos y marcarla como predeterminada.

- [ ] **4.4. Historial de Pedidos (`/profile/orders`)**:
  - [ ] **1. Listado y Detalle**: Ver el historial de pedidos y consultar el detalle completo de un pedido `/profile/orders/{order_id}`.
  - [ ] **2. Edge Case (Seguridad IDOR en Pedidos)**: Intentar consultar la URL del pedido de otro usuario (debe bloquearse con 403).

- [ ] **4.5. Lista de Deseos (`/wishlist`)**:
  - [ ] **1. Gestión de Favoritos**: Consultar la lista de deseos, eliminar un producto o moverlo directamente al carrito.

---

### Módulo 5: Administración (Panel de Filament en `/admin`)

- [ ] **5.1. Seguridad y Acceso**:
  - [ ] **1. Edge Case (Segregación de Roles)**: Intentar ingresar a `/admin` logueado como usuario cliente `customer` (debe ser rechazado con 403).
  - [ ] **2. Login Administrador**: Iniciar sesión con la cuenta de administrador `admin@leen.com`.

- [ ] **5.2. Gestión de Productos (`/admin/products`)**:
  - [ ] **1. Crear Producto**: Crear un producto con variantes, imágenes y precio en COP y EUR.
  - [ ] **2. Modificar Producto**: Actualizar stock y activar/desactivar condición de preventa.

- [ ] **5.3. Gestión de Categorías (`/admin/categories`)**:
  - [ ] **1. Crear Categoría**: Crear una nueva categoría con nombre, slug e imagen opcional.
  - [ ] **2. Editar Categoría**: Modificar los datos de la categoría existente.
  - [ ] **3. Eliminar Categoría**: Borrar una categoría y comprobar la eliminación limpia de su imagen asociada y la redirección transparente a la lista.

- [ ] **5.4. Gestión de Cupones (`/admin/coupons`)**:
  - [ ] **1. Crear Cupón**: Crear un cupón (descuento fijo o porcentaje), asignar vencimiento y límite de usos.

- [ ] **5.5. Gestión de Pedidos (`/admin/orders`)**:
  - [ ] **1. Cambio de Estado**: Cambiar el estado de una orden (de `Pending` a `Processing`, `Completed` o `Cancelled`).
  - [ ] **2. Restitución de Cupón**: Verificar que al cancelar un pedido que usó un cupón se reestablezca la disponibilidad de uso del cupón.

- [ ] **5.6. Moderación de Reseñas (`/admin/reviews`)**:
  - [ ] **1. Moderación**: Aprobar o rechazar reseñas de productos enviadas por compradores.
