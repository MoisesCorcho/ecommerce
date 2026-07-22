# Brief UI: Perfil de usuario

> **Vista:** Perfil · **Ruta sugerida:** `/profile`
> **Depende de:** F02 (cuentas y direcciones), F04 (pedidos)
> **Estado:** Pendiente de F02
>
> **Nota:** El menú del perfil incluye un enlace a `/wishlist` (F08), pero la vista de favoritos vive en su propio brief (`09-lista-de-deseados.md`). El perfil no consume datos de wishlist.

---

# Para Stitch (diseño visual)

> **Prompt para Google Stitch.** Pasar este bloque + `00-design-tokens.md`.

## Objetivo de la vista

Panel personal del usuario: administrar datos, direcciones y ver pedidos. Sensación de panel privado, limpio y organizado, no de admin.

> **Nota:** Favoritos NO es una sección del perfil. El menú incluye un enlace "Favoritos" que redirige a la vista dedicada `/wishlist` (ver brief `09-lista-de-deseados.md`). Evita duplicar la vista de favoritos dentro del perfil.

## Estructura y layout

**Desktop (layout principal):**
- Breadcrumb arriba (`Inicio / Mi cuenta`).
- **Menú lateral a la izquierda** (200–250px): navegación entre secciones.
- **Contenido a la derecha** (resto del ancho): sección activa.
- Espacio en blanco generoso.

**Tablet:**
- Menú lateral más estrecho o tabs horizontales arriba.

**Móvil:**
- Tabs horizontales en la parte superior (scrollables si es necesario).
- Contenido debajo.

## Componentes visuales

### Menú lateral / tabs
- Items: Mi perfil, Mis direcciones, Mis pedidos, Favoritos (enlace a `/wishlist`), Cambiar contraseña, Cerrar sesión.
- "Favoritos" **no es una sección del perfil**: al hacer clic redirige a la vista dedicada `/wishlist`. Marcar visualmente como enlace externo (ícono de flecha `→` o similar) para diferenciarlo de las secciones internas.
- Item activo: fondo Soft Sand o borde izquierdo Soft Gold + texto Intense Cocoa SemiBold.
- Items inactivos: Intense Cocoa Regular con opacidad.
- Íconos lineales a la izquierda de cada item (Intense Cocoa).
- "Cerrar sesión" al final, separado, con ícono de salir.
- Montserrat Medium.

### Sección: Mi perfil
- Formulario simple con 3 campos (nombre, email, teléfono).
- Labels sobre inputs, bordes Intense Cocoa sutil, focus Soft Gold.
- Botón "Guardar cambios" al final (Intense Cocoa, Silk Cream, hover Soft Gold).
- Feedback de éxito tras guardar (toast o banner sutil: "Cambios guardados").

### Sección: Mis direcciones
- **Grid de cards** (1–2 columnas).
- Cada card: ícono de ubicación + nombre descriptivo ("Casa", "Oficina") + dirección completa + botones editar/eliminar (íconos, Intense Cocoa, hover Soft Gold).
- Dirección principal: card destacada con borde Soft Gold o badge "Principal".
- Botón "Agregar nueva dirección" como card adicional con borde punteado + ícono `+`.
- Formulario de dirección (crear/editar): modal o sección expandible con campos (nombre, dirección, ciudad, etc.).

### Sección: Mis pedidos (diferido — F04)
- **Estado vacío inicial**: ilustración + "Aún no tienes pedidos." + CTA sutil a `/shop`.
- Cuando F04 esté listo: tabla o lista de cards. Cada pedido: número, fecha, estado (badge de color), total, botón "Ver detalle".
- Badge de estado: colores según `OrderStatusEnum` (ej: pendiente = Soft Gold, completado = verde sutil, cancelado = rojo sutil). Mantener tonos cálidos.

### Sección: Cambiar contraseña
- Formulario simple de 3 campos (contraseña actual, nueva, confirmar).
- Indicador de fortaleza en nueva contraseña (barra rojo → amarillo → verde).
- Botón "Cambiar contraseña" (Intense Cocoa, hover Soft Gold).
- Feedback de éxito o error.

## Paleta de colores (ver `00-design-tokens.md`)

- Fondo: **Silk Cream** `#FFF8CF`
- Contraste (cards, menú activo, dividers): **Soft Sand** `#E9DED3`
- Texto, botones, íconos: **Intense Cocoa** `#372621`
- Hover, activos, detalles: **Soft Gold** `#D2AE36`
- Proporción 70/20/10.

## Tipografía (ver `00-design-tokens.md`)

- Título de sección, saludo al usuario (ej: "Hola, <nombre>"): **Chillax** Semibold.
- Items de menú, botones, etiquetas: **Montserrat** Medium/SemiBold.
- Cuerpo, formularios, captions: **Montserrat** Regular.

## Estilo visual

- Limpio, consistente con el resto del sitio.
- Sensación de panel personal, privado, no de admin.
- Minimalista. Mucho espacio en blanco.
- Cards con sombras suaves, bordes discretos.
- Iconografía lineal, Intense Cocoa.
- El menú lateral organiza sin abrumar.

## Estados

- **Formulario guardado**: toast/banner de éxito.
- **Error de validación**: bordes rojos + mensajes.
- **Contraseña actual incorrecta**: mensaje claro.
- **Dirección eliminada**: card desaparece con animación sutil.
- **Sección vacía (pedidos)**: ilustración + CTA.
- **Loading**: skeleton o spinner en formularios.

## Breakpoints

Diseñar primero para **desktop** (`lg`/`xl`) con menú lateral. En tablet (`md`) y móvil (`sm`), tabs horizontales. Ver `00-design-tokens.md` sección Responsive.

---

# Para implementación (NO pasar a Stitch)

## Contexto del proyecto

- El perfil es una **vista contenedora** que agrupa secciones de múltiples features (F02, F04).
- **Requiere autenticación** — todas las rutas de perfil están protegidas.
- El modelo `User` tiene: `name`, `email`, `phone`. Las direcciones son una relación `hasMany(Address)`.
- La sección de **pedidos** depende de F04 (posterior). En la primera versión del perfil, esa pestaña puede existir pero mostrar estado vacío o "Próximamente".
- **Favoritos NO es una sección del perfil**: el menú incluye un enlace que redirige a `/wishlist` (brief `09-lista-de-deseados.md`, feature F08). No duplicar la vista de favoritos dentro del perfil.

## Acciones del usuario

El usuario podrá:

- Actualizar su información personal (nombre, email, teléfono).
- Agregar, editar y eliminar direcciones.
- Marcar una dirección como principal.
- Consultar sus pedidos (diferido — F04).
- Consultar el detalle de un pedido (diferido — F04).
- Acceder a su lista de Favoritos (enlace que redirige a `/wishlist` — F08).
- Cambiar su contraseña.
- Cerrar sesión.

## Validaciones

- Validar la información personal antes de guardar (email válido, nombre no vacío).
- Verificar la contraseña actual antes de permitir el cambio.
- Validar que la nueva contraseña cumpla los requisitos mínimos de seguridad.
- Validar los campos de dirección al crear/editar.

## Datos requeridos

**Dinámicos (del backend):**

- Información del usuario autenticado (`User`: name, email, phone).
- Direcciones del usuario (`Address`).
- Historial de pedidos (`Order` — diferido F04).

> **Nota:** Los datos de Favoritos (`Wishlist`) se gestionan en la vista dedicada `/wishlist` (brief `09-lista-de-deseados.md`), no en el perfil.

## Consideraciones técnicas

- Todas las rutas de perfil requieren autenticación (`auth` middleware).
- Organizar cada sección como componente Livewire independiente.
- Actualizar la información sin recargar la página cuando sea posible.
- La sección de pedidos se muestra con estado vacío hasta que F04 esté listo.
- "Favoritos" en el menú es un enlace simple a `/wishlist`, no una sección interna del perfil.
- Responsive: desktop-first con experiencia móvil equivalente (ver `00-design-tokens.md`).

## Fuera de alcance (diferido)

- **Múltiples direcciones de envío en una orden** — una dirección por orden.
- **Gestión de métodos de pago guardados** — mejora futura (F05).
- **Preferencias de notificaciones** — mejora futura.
- **Historial de devoluciones** — mejora futura.
- **Descarga de facturas** — mejora futura.
- **Eliminación de la cuenta** — mejora futura.
- **Número de guía / tracking en tiempo real** — requiere integración con transportadora.
