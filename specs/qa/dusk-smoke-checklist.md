# Checklist de Pruebas Visuales E2E (Dusk & Selenium)

Este documento define la secuencia de navegación E2E y generación de capturas de pantalla automatizadas para la revisión de todas las vistas del MVP de Leen Handbags.

---

## 1. Almacenamiento de Capturas de Pantalla

Las capturas de pantalla se almacenan en:
- **Directorio de Screenshots:** `tests/Browser/screenshots/`

---

## 2. Matriz de Capturas de Pantalla Visuales Generadas

| ID Paso | Pantalla / Vista | Ruta | Captura generada |
| :--- | :--- | :--- | :--- |
| **VIS-01** | **Página de Inicio (Home)** | `/` | `01-home-page.png` |
| **VIS-02** | **Catálogo de Productos** | `/products` | `02-catalog-page.png` |
| **VIS-03** | **Detalle de Producto (PDP)** | `/products/{slug}` | `03-product-detail.png` |
| **VIS-04** | **Página del Carrito** | `/cart` | `04-cart-page.png` |
| **VIS-05** | **Formulario de Checkout** | `/checkout` | `05-checkout-page.png` |
| **VIS-06** | **Gracias por su compra** | `/orders/{order}/thank-you` | `06-thank-you-page.png` |
| **VIS-07** | **Quienes Somos (About Us)** | `/about-us` | `07-about-us-page.png` |
| **VIS-08** | **Preguntas Frecuentes (FAQ)**| `/faq` | `08-faq-page.png` |
| **VIS-09** | **Contacto** | `/contact` | `09-contact-page.png` |
| **VIS-10** | **Login Cliente** | `/login` | `10-login-page.png` |
| **VIS-11** | **Registro Cliente** | `/register` | `11-register-page.png` |
| **VIS-12** | **Perfil Cliente** | `/profile` | `12-customer-profile-page.png` |
| **VIS-13** | **Mis Pedidos Cliente** | `/profile/orders` | `13-customer-orders-page.png` |
| **VIS-14** | **Lista de Deseos (Wishlist)** | `/wishlist` | `14-customer-wishlist-page.png` |
| **VIS-15** | **Login Panel de Admin** | `/admin/login` | `15-admin-login-page.png` |

---

## 3. Instrucciones de Ejecución

```bash
vendor/bin/sail artisan dusk tests/Browser/SmokeTest.php
```
