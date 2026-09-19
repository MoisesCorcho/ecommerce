# Plan de QA — Smoke Tests (Pruebas de Humo) MVP

Este documento define la matriz completa de pruebas de humo (*Smoke Tests*) para la versión mínima viable (MVP) de Leen Handbags. El objetivo de estas pruebas es garantizar que todas las vistas de la tienda, del perfil de cliente y del panel administrativo no presenten errores de servidor (`500`) ni fallos de renderizado antes de recibir ajustes de diseño del cliente.

---

## 1. Alcance de las Pruebas de Humo (Vistas y Flujos)

| ID | Módulo | Descripción del Escenario | Ruta | Resultado Esperado |
| :--- | :--- | :--- | :--- | :--- |
| **ST-01** | **Home** | Landing page de la marca. | `/` | HTTP `200 OK`, hero section y productos destacados. |
| **ST-02** | **Catálogo** | Grilla de productos con filtros y ordenamiento. | `/products` | HTTP `200 OK`, grilla de bolsos. |
| **ST-03** | **Ficha de Producto (PDP)** | Detalle de bolso, variantes, galería y stock. | `/products/{slug}` | HTTP `200 OK`, fotos, precio y selector. |
| **ST-04** | **Carrito** | Vista del carrito con ítems y subtotales. | `/cart` | HTTP `200 OK`, lista de compras. |
| **ST-05** | **Checkout** | Formulario de datos de contacto y envío. | `/checkout` | HTTP `200 OK` (con carrito poblado). |
| **ST-06** | **Gracias por su compra** | Confirmación de pedido creado `pending`. | `/orders/{order}/thank-you` | HTTP `200 OK`, datos del pedido y botón "Pagar ahora". |
| **ST-07** | **Quienes Somos** | Página estática sobre la marca. | `/about-us` | HTTP `200 OK`. |
| **ST-08** | **Preguntas Frecuentes** | FAQs de la tienda con acordeón Alpine.js. | `/faq` | HTTP `200 OK`. |
| **ST-09** | **Contacto** | Formulario de contacto con la marca. | `/contact` | HTTP `200 OK`. |
| **ST-10** | **Login Cliente** | Formulario de inicio de sesión. | `/login` | HTTP `200 OK`. |
| **ST-11** | **Registro Cliente** | Formulario de creación de cuenta. | `/register` | HTTP `200 OK`. |
| **ST-12** | **Recuperar Contraseña** | Solicitud de reset de contraseña. | `/forgot-password` | HTTP `200 OK`. |
| **ST-13** | **Perfil Cliente** | Gestión de datos personales. | `/profile` | HTTP `200 OK` (autenticado). |
| **ST-14** | **Direcciones Cliente** | Libreta de direcciones de envío. | `/profile/addresses` | HTTP `200 OK` (autenticado). |
| **ST-15** | **Mis Pedidos** | Historial de pedidos del comprador. | `/profile/orders` | HTTP `200 OK` (autenticado). |
| **ST-16** | **Lista de Deseos** | Favoritos guardados. | `/wishlist` | HTTP `200 OK` (autenticado). |
| **ST-17** | **Panel Admin (Login)** | Login del backoffice en Filament. | `/admin/login` | HTTP `200 OK`. |
| **ST-18** | **Panel Admin (Dashboard)**| Gestión central de la tienda. | `/admin` | HTTP `200 OK` (admin autenticado). |

---

## 2. Ejecución Automatizada

- **Smoke Tests HTTP (Backend/Rutas):**
  ```bash
  vendor/bin/sail artisan test --filter=SmokeTest
  ```
- **Smoke Tests E2E de Navegador (Selenium / Dusk):**
  ```bash
  vendor/bin/sail artisan dusk tests/Browser/SmokeTest.php
  ```
