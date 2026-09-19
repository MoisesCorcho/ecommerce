# Checklist de Pruebas Visuales E2E (Dusk & Selenium)

Este documento define la secuencia de navegación E2E y generación de capturas de pantalla automatizadas para la revisión de todas las vistas del MVP de Leen Handbags.

---

## 1. Almacenamiento de Capturas de Pantalla

Las capturas de pantalla se almacenan en:
- **Directorio de Screenshots:** `tests/Browser/screenshots/`

---

## 2. Matriz de Capturas de Pantalla Visuales Generadas (Orden Lógico por Bloques)

### Bloque 1: Experiencia Pública y Marca
| ID Paso | Pantalla / Vista | Ruta | Captura generada |
| :--- | :--- | :--- | :--- |
| **VIS-01** | **Página de Inicio (Home)** | `/` | `01-home-page.png` |
| **VIS-02** | **Quienes Somos (About Us)** | `/about-us` | `02-about-us-page.png` |
| **VIS-03** | **Preguntas Frecuentes (FAQ)**| `/faq` | `03-faq-page.png` |
| **VIS-04** | **Contacto** | `/contact` | `04-contact-page.png` |

### Bloque 2: Catálogo y Flujo de Compra
| ID Paso | Pantalla / Vista | Ruta | Captura generada |
| :--- | :--- | :--- | :--- |
| **VIS-05** | **Catálogo de Productos** | `/products` | `05-catalog-page.png` |
| **VIS-06** | **Detalle de Producto (PDP)** | `/products/{slug}` | `06-product-detail.png` |
| **VIS-07** | **Página del Carrito** | `/cart` | `07-cart-page.png` |
| **VIS-08** | **Formulario de Checkout** | `/checkout` | `08-checkout-page.png` |
| **VIS-09** | **Gracias por su compra** | `/orders/{order}/thank-you` | `09-thank-you-page.png` |

### Bloque 3: Acceso / Autenticación de Cliente
| ID Paso | Pantalla / Vista | Ruta | Captura generada |
| :--- | :--- | :--- | :--- |
| **VIS-10** | **Login Cliente** | `/login` | `10-login-page.png` |
| **VIS-11** | **Registro Cliente** | `/register` | `11-register-page.png` |

### Bloque 4: Área / Perfil de Cuenta del Comprador (Seguidilla)
| ID Paso | Pantalla / Vista | Ruta | Captura generada |
| :--- | :--- | :--- | :--- |
| **VIS-12** | **Perfil Cliente (Datos)** | `/profile` | `12-profile-dashboard-page.png` |
| **VIS-13** | **Mis Direcciones** | `/profile/addresses` | `13-profile-addresses-page.png` |
| **VIS-14** | **Mis Pedidos** | `/profile/orders` | `14-profile-orders-page.png` |
| **VIS-15** | **Mis Reseñas** | `/profile/reviews` | `15-profile-reviews-page.png` |
| **VIS-16** | **Lista de Deseos (Wishlist)** | `/wishlist` | `16-profile-wishlist-page.png` |

### Bloque 5: Administración (Panel de Filament)
| ID Paso | Pantalla / Vista | Ruta | Captura generada |
| :--- | :--- | :--- | :--- |
| **VIS-17** | **Login Panel de Admin** | `/admin/login` | `17-admin-login-page.png` |
| **VIS-18** | **Dashboard de Admin** | `/admin` | `18-admin-dashboard-page.png` |
| **VIS-19** | **Gestión de Productos** | `/admin/products` | `19-admin-products-page.png` |
| **VIS-20** | **Gestión de Categorías** | `/admin/categories` | `20-admin-categories-page.png` |
| **VIS-21** | **Gestión de Pedidos** | `/admin/orders` | `21-admin-orders-page.png` |
| **VIS-22** | **Gestión de Cupones** | `/admin/coupons` | `22-admin-coupons-page.png` |
| **VIS-23** | **Gestión de Usuarios** | `/admin/users` | `23-admin-users-page.png` |
| **VIS-24** | **Gestión de Reseñas** | `/admin/reviews` | `24-admin-reviews-page.png` |

---

## 3. Instrucciones de Ejecución

```bash
vendor/bin/sail artisan dusk tests/Browser/SmokeTest.php
```
