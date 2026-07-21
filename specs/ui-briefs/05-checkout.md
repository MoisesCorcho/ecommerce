# Brief UI: Checkout

> **Vista:** Checkout · **Ruta sugerida:** `/checkout`
> **Depende de:** F03 (carrito), F04 (checkout y órdenes), F02 (cuentas), F05 (pagos)
> **Estado:** Pendiente de F04

---

# Para Stitch (diseño visual)

> **Prompt para Google Stitch.** Pasar este bloque + `00-design-tokens.md`.

## Objetivo de la vista

El usuario completa la compra de forma segura: ingresa información de contacto, dirección de envío, elige método de envío y pago, y confirma el pedido.

## Estructura y layout

**Desktop (layout principal):**
- Breadcrumb arriba (`Inicio / Carrito / Checkout`).
- **Stepper** horizontal indicando los pasos: Contacto → Envío → Pago → Confirmación.
- **Dos columnas:**
  - Izquierda (60%): formularios del paso actual.
  - Derecha (40%, **sticky**): resumen del pedido.
- Botón "Finalizar compra" al final del último paso.

**Tablet:**
- Stepper arriba, formularios y resumen en dos columnas equilibradas o una columna.

**Móvil:**
- Stepper arriba (scrollable horizontal si es necesario), una sola columna: paso actual arriba, resumen colapsable abajo.

## Componentes visuales

### Stepper (indicador de pasos)
- Pasos: Contacto → Dirección → Envío → Pago → Confirmación.
- Paso actual destacado (Intense Cocoa con fondo Soft Gold o borde Soft Gold).
- Pasos completados con ícono check (Intense Cocoa).
- Pasos futuros en Intense Cocoa con opacidad.
- Conectores entre pasos (línea Soft Sand, se rellena con Intense Cocoa al completar).

### Formularios
- Campos limpios, labels **sobre** los inputs (Montserrat Medium, Intense Cocoa).
- Inputs con borde Intense Cocoa sutil, fondo Silk Cream.
- Focus: borde Soft Gold.
- Error: borde rojo sutil + mensaje debajo (Montserrat Regular, rojo).
- Agrupación lógica por sección (Contacto, Dirección, etc.).
- Validación en tiempo real (bordes + mensajes).

### Direcciones guardadas (usuario autenticado)
- Tarjetas seleccionables (radio cards) con dirección completa.
- Opción "Agregar nueva dirección" como tarjeta adicional con borde punteado.
- Dirección seleccionada con borde Intense Cocoa o fondo Soft Sand.

### Selector de método de envío
- Radio buttons con: nombre del servicio (Montserrat Medium), tiempo estimado (Montserrat Regular), costo (Montserrat SemiBold).
- Seleccionado con borde Intense Cocoa o fondo Soft Sand.
- Si solo hay una opción, mostrarla preseleccionada.

### Selector de método de pago
- Radio buttons con ícono del método + nombre.
- Si no hay métodos (F05 pendiente): placeholder informativo "Próximamente" o "Pago contra entrega" según configuración.
- Seleccionado con borde Intense Cocoa.

### Resumen del pedido (sticky)
- Card con fondo Soft Sand o Silk Cream con borde.
- Título "Tu pedido" en Chillax Semibold.
- Lista compacta de ítems: imagen miniatura (40×40px) + nombre + cantidad + precio.
- Subtotal, costo de envío (si aplica), total destacado (Chillax Semibold o Montserrat Bold, Intense Cocoa).
- Si el carrito se modifica, el resumen se actualiza.

### Botón "Finalizar compra"
- Ancho completo de la columna izquierda (o del botón del último paso).
- Fondo Intense Cocoa, texto Silk Cream, Montserrat SemiBold.
- Solo se activa cuando todos los pasos están completos.
- Estado de loading mientras procesa.
- Hover: Soft Gold.

## Paleta de colores (ver `00-design-tokens.md`)

- Fondo: **Silk Cream** `#FFF8CF`
- Contraste (cards, dividers, stepper): **Soft Sand** `#E9DED3`
- Texto, botones, íconos: **Intense Cocoa** `#372621`
- Hover, focus, activos, detalles: **Soft Gold** `#D2AE36`
- Proporción 70/20/10.

## Tipografía (ver `00-design-tokens.md`)

- Títulos de paso, título de resumen: **Chillax** Semibold.
- Labels, botones, etiquetas: **Montserrat** Medium/SemiBold.
- Inputs, cuerpo, captions: **Montserrat** Regular.

## Estilo visual

- **Sensación de seguridad y confianza.** Limpio, sin distracciones.
- Badge "Pago seguro" o ícono de candado cerca del botón finalizar.
- Minimalista, consistente con el resto de la tienda.
- Formularios cómodos, no amontonados.
- El resumen siempre visible (sticky) para que el usuario sepa qué está pagando.
- Sin elementos decorativos que distraigan del objetivo: completar la compra.

## Estados

- **Paso incompleto**: botón "Finalizar compra" deshabilitado, campos con errores destacados.
- **Validación en tiempo real**: bordes rojos/verdes + mensajes.
- **Loading al finalizar**: spinner en el botón, bloquear interacción.
- **Error de disponibilidad de stock**: banner sutil "La disponibilidad de un producto cambió. Vuelve al carrito.".
- **Confirmación exitosa**: redirección a página de confirmación (fuera del alcance de este brief).

## Breakpoints

Diseñar primero para **desktop** (`lg`/`xl`) con dos columnas (formularios + resumen sticky). En tablet (`md`) y móvil (`sm`), una columna con resumen colapsable. Ver `00-design-tokens.md` sección Responsive.

---

# Para implementación (NO pasar a Stitch)

## Contexto del proyecto

- **Guest checkout** (D2): los visitantes pueden comprar sin registrarse. Se solicita información de contacto en el momento.
- Si el usuario está autenticado (F02), se pre-cargan sus datos y direcciones guardadas.
- **Stock visible** (D4): validar disponibilidad antes de generar la orden.
- **Pagos** (F05): los métodos de pago (Stripe / Bold) se integran en una fase posterior. En la primera versión del checkout, el paso de pago puede ser un placeholder o mostrar métodos básicos.
- Las órdenes usan **snapshots** (F04): lo que el usuario "vio" se congela en la orden.

## Flujo del proceso

```text
Información de contacto
  ↓
Dirección de envío
  ↓
Método de envío
  ↓
Método de pago
  ↓
Confirmación del pedido
```

## Información a solicitar

### Información de contacto

**Usuario autenticado:** pre-cargar nombre, email, teléfono desde su perfil (F02).

**Usuario invitado:** solicitar: nombre, apellidos, correo electrónico, número de teléfono.

### Dirección de envío

**Usuario autenticado:** ofrecer direcciones guardadas (F02) + opción de agregar nueva.

**Usuario invitado:** solicitar: país, departamento/estado, ciudad, dirección, información complementaria (opcional).

### Método de envío

> La lógica de envíos no está implementada. En la primera versión, puede ofrecerse una opción genérica ("Envío estándar") con costo configurable o gratuito.

### Método de pago

> F05 (Stripe / Bold) no está implementado. En la primera versión, este paso puede ser un placeholder informativo o mostrar métodos básicos.

## Acciones del usuario

El usuario podrá:

- Completar su información personal (invitado) o confirmar la pre-cargada (autenticado).
- Registrar o seleccionar dirección de envío.
- Seleccionar un método de envío.
- Seleccionar un método de pago.
- Confirmar la compra.
- Volver al carrito si necesita modificar.

## Validaciones

- Validar todos los campos obligatorios.
- Validar el formato del correo electrónico.
- Validar el número telefónico.
- Validar la dirección de envío.
- Validar la disponibilidad del inventario antes de generar la orden.
- No permitir finalizar la compra si existe información incompleta.

## Datos requeridos

**Dinámicos (del backend):**

- Ítems del carrito (producto, variante, cantidad, precio).
- Información del comprador (pre-cargada si autenticado).
- Direcciones guardadas (si autenticado, F02).
- Métodos de envío disponibles (según ubicación — placeholder por ahora).
- Métodos de pago disponibles (F05 — placeholder por ahora).
- Totales del pedido (subtotal, envío, total).

## Consideraciones técnicas

- Mantener actualizado el resumen del pedido durante todo el proceso.
- Guardar temporalmente la información ingresada para evitar pérdidas accidentales.
- Validar la información tanto en el cliente como en el servidor.
- Conexión segura (HTTPS) durante el proceso de pago.
- Responsive: desktop-first con experiencia móvil equivalente (ver `00-design-tokens.md`).
- Generar snapshot de la orden al confirmar (F04).

## Fuera de alcance (diferido)

- **Métodos de pago reales** — requiere F05 (Stripe / Bold). Placeholder en primera versión.
- **Cálculo de costo de envío real** — requiere integración con transportadoras. Opción genérica por ahora.
- **Múltiples direcciones de envío** — una dirección por orden en la primera versión.
- **Facturación electrónica** — fuera del alcance actual.
- **Validación automática de direcciones** — mejora futura.
- **Cálculo automático de impuestos según ubicación** — mejora futura.
- **Cupones de descuento** — requiere F06.
