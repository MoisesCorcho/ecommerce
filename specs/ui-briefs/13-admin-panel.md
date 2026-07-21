# Brief UI: Panel Administrativo

> **Vista:** Admin Panel · **Ruta:** `/admin` (Filament v5)
> **Depende de:** F01 (catálogo — ✅ completa), F04 (pedidos), F05 (pagos), F06 (cupones), F07 (reviews)
> **Estado:** F01 completa. Módulos futuros pendientes.

---

# Para Stitch (diseño visual)

> **Importante:** el panel admin usa **Filament v5**, no Blade/Tailwind personalizado. El diseño visual lo proporciona Filament. Este brief describe **contenido y estructura**, no diseño visual personalizado desde cero.
> Pasar este bloque + `00-design-tokens.md` solo para personalización de branding (colores, logo).

## Objetivo de la vista

Panel de administración del e-commerce. Gestionar catálogo, pedidos, clientes y contenido desde una plataforma centralizada.

## Nota sobre Filament

El panel usa Filament v5 nativo: menú lateral, tablas, formularios, widgets. **No se diseña desde cero.** Lo que se personaliza:

- **Branding**: logo (variante Brown sobre fondos claros), nombre "Leen Handbags Admin", colores primarios alineados a la paleta de marca (Intense Cocoa como primario, Soft Gold como acento).
- **Navegación**: agrupar recursos en grupos lógicos.
- **Tablas y formularios**: configurar columnas, filtros, campos relevantes (Filament nativo).

## Estructura (nativa de Filament)

- **Menú lateral izquierdo** (Filament nativo): navegación entre recursos.
- **Header superior**: perfil del admin, notificaciones.
- **Área principal**: contenido del recurso/página activa.

## Módulos existentes (✅ F01 completa)

### Grupo: Catálogo

#### Categorías (`CategoryResource`)
- **Tabla**: nombre, slug, orden, productos asociados (contador).
- **Formulario crear/editar**: nombre, slug (auto si vacío), categoría padre (select), orden.
- **Acciones**: crear, editar, eliminar (con confirmación).
- **Nota:** las categorías **no** tienen flag de activo/inactivo (F01 D3). No mostrar toggle de activación.

#### Productos (`ProductResource`)
- **Tabla**: imagen, nombre, slug, estado (`is_active`), rango de precios, variantes (contador).
- **Formulario crear/editar**:
  - Datos producto: nombre, descripción, `is_active` (toggle).
  - Variantes (Repeater): SKU, atributos (color, material, tamaño), stock, `is_active`.
  - Precios por variante (Repeater anidado): moneda (COP/EUR), monto (entero).
  - Imágenes (Repeater con FileUpload): path, `is_primary` (exclusive toggle).
- **Invariante de publicación**: si `is_active = true` sin variante activa con precio, mostrar error claro, no guardar.
- **Acciones**: crear, editar, eliminar (con confirmación).

## Módulos futuros (pendientes de features)

### Dashboard
- Widgets nativos de Filament (stats, charts).
- Métricas: ventas recientes (F04), pedidos pendientes (F04), productos con bajo inventario, productos más vendidos (F04), clientes recientes (F02).
- **Estado inicial**: dashboard básico o placeholder hasta que F04 esté listo.

### Pedidos (futuro — F04)
- Tabla de pedidos: número, fecha, cliente, estado (`OrderStatusEnum`), total.
- Detalle: productos (snapshot), cantidades, precios, dirección, método de pago, estado, número de guía.
- Acciones: actualizar estado, registrar número de guía.

### Clientes (futuro — F02)
- Tabla de clientes: nombre, email, teléfono, pedidos (contador), fecha de registro.
- Detalle: información del cliente + historial de pedidos.
- **Nota:** sin activar/desactivar cuentas (no hay campo `is_active` en `User`).

### Promociones (futuro — F06)
- Productos destacados (flag `is_featured`).
- Cupones de descuento.
- **Nota:** banners promocionales requieren CMS (no planeado, D1).

## Personalización de branding (alineado a `00-design-tokens.md`)

- **Color primario del panel**: Intense Cocoa `#372621` (para botones, enlaces activos, elementos destacados).
- **Color de acento**: Soft Gold `#D2AE36` (para hover, estados activos secundarios, notificaciones).
- **Fondo del panel**: Silk Cream `#FFF8CF` o blanco (Filament default es blanco/gris — considerar mantener o ajustar sutilmente).
- **Logo**: variante Brown del logo de Leen Handbags.
- **Nombre del panel**: "Leen Handbags" o "Leen Admin".
- **Tipografía**: Montserrat (consistent con la marca). Filament usa su propia tipografía por defecto, pero puede personalizarse.

## Estados

- **Confirmación de eliminación**: modal nativo de Filament ("¿Estás seguro?").
- **Notificaciones**: nativas de Filament (toast, color según tipo: éxito, error, advertencia).
- **Error de publicación**: notificación Filament + mensaje en formulario ("No se puede publicar: agrega al menos una variante activa con precio.").
- **Formulario con errores**: resaltado nativo de Filament.

## Consideraciones para Stitch

- **No generar HTML/CSS personalizado para el panel.** Filament lo provee.
- Stitch puede ayudar a:
  1. Definir la **estructura de tablas** (qué columnas mostrar).
  2. Definir la **estructura de formularios** (qué campos, en qué orden, agrupación).
  3. Sugerir **widgets de dashboard** relevantes.
  4. Ajustar **branding** (colores, logo, nombre) en `AdminPanelProvider`.
- Usar la skill `filament-admin-standards` del proyecto para estándares de UI/UX en Filament v5.

---

# Para implementación (NO pasar a Stitch)

## Contexto del proyecto

- **F01 (Catálogo admin) ya está completo:** `CategoryResource` y `ProductResource` en Filament v5 están implementados y testeados.
- **Acceso al panel:** controlado por `config('ecommerce.admin_emails')`. Un usuario accede al panel si su email está en la lista. **Sin roles ni permisos granulares** (no se usa Spatie Permission — F01 D2).
- **Sin CMS** (D1): el contenido institucional (Home, FAQ, About Us) **no** se administra desde el panel. Es estático en Blade.
- Los módulos de Pedidos, Clientes, Promociones, etc. son **futuros** y dependen de sus respectivas features en el roadmap.

## Acciones del usuario (admin)

El administrador podrá:

- **Categorías**: crear, editar, eliminar, reordenar.
- **Productos**: crear, editar, eliminar, gestionar variantes/precios/imágenes, publicar/despublicar.
- **Pedidos** (futuro): consultar, actualizar estado, registrar guía.
- **Clientes** (futuro): consultar, ver historial.
- Cerrar sesión.

## Validaciones

- Restringir el acceso únicamente a usuarios autenticados con email en `ADMIN_EMAILS`.
- Confirmar acciones críticas como eliminaciones.
- Mostrar mensajes claros de error o éxito (notificaciones Filament).
- Invariante de publicación de productos (F01 R10, R15).

## Datos requeridos

- Información del administrador autenticado.
- Categorías (`Category`).
- Productos + variantes + precios + imágenes.
- Pedidos (futuro — F04).
- Clientes (futuro — F02).

## Consideraciones técnicas

- El panel usa Filament v5 nativo — no diseñar desde cero.
- Acceso controlado por `User::canAccessPanel()` (email en `ADMIN_EMAILS`).
- Sin roles ni permisos granulares (Spatie no instalado — F01 D2).
- Las categorías no tienen toggle de activo/inactivo.
- Los productos tienen `is_active` con invariante de publicación.
- Los módulos futuros se agregan como nuevos `Resource` de Filament cuando sus features estén listas.
- Responsive: el panel admin es principalmente desktop (Filament lo soporta, pero la experiencia óptima es desktop).

## Fuera de alcance (diferido)

- **Dashboard con métricas** — requiere F04 (órdenes) para datos de ventas.
- **Gestión de pedidos** — requiere F04.
- **Gestión de clientes** — requiere F02 + feature de panel.
- **Promociones / cupones** — requiere F06.
- **Gestión de reseñas** — requiere F07.
- **Roles y permisos granulares** — requiere Spatie Permission (no instalado, F01 D2).
- **Gestión de usuarios del panel** — actualmente por `ADMIN_EMAILS` en `.env`. La gestión desde UI requiere feature dedicada.
- **CMS de contenido institucional** — no planeado (D1). Home, FAQ, About Us son estáticos.
- **Reportes avanzados** — mejora futura.
- **Blog / Newsletter** — fuera del roadmap actual.
- **Facturación electrónica** — fuera del alcance actual.
- **CRM** — fuera del alcance actual.
