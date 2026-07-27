<?php

declare(strict_types=1);

return [

    'breadcrumb' => [
        'home' => 'Inicio',
        'faq' => 'FAQ',
    ],

    'title' => 'Preguntas Frecuentes',
    'subtitle' => 'Encuentra respuestas rápidas a preguntas comunes sobre nuestros productos, pedidos y servicios.',

    'categories' => [

        'compras' => [
            'label' => 'Compras',
            'questions' => [
                [
                    'q' => '¿Cómo realizo un pedido?',
                    'a' => 'Explora nuestro catálogo, selecciona el bolso y color que deseas, agrégalo al carrito y procede al pago. Puedes completar tu compra como invitado o con una cuenta registrada.',
                ],
                [
                    'q' => '¿Puedo modificar o cancelar mi pedido después de hacerlo?',
                    'a' => 'Si tu pedido aún no ha sido procesado, contáctanos lo antes posible y haremos lo mejor para ayudarte. Una vez que el pedido entra en proceso de envío, ya no puede ser modificado.',
                ],
                [
                    'q' => '¿Los productos mostrados son exactamente lo que recibiré?',
                    'a' => 'Hacemos todo lo posible para mostrar colores y detalles con la mayor precisión. Pueden existir ligeras variaciones debido a la configuración de la pantalla, pero la calidad y artesanía que ves es lo que recibirás.',
                ],
                [
                    'q' => '¿Ofrecen envoltorio de regalo?',
                    'a' => 'En este momento no ofrecemos envoltorio de regalo. Cada pieza es cuidadosamente empacada para garantizar que llegue en perfectas condiciones.',
                ],
            ],
        ],

        'envios' => [
            'label' => 'Envíos',
            'questions' => [
                [
                    'q' => '¿Cuáles son las opciones de envío y tiempos de entrega?',
                    'a' => 'Ofrecemos envío estándar. Los tiempos de entrega varían según el destino: típicamente 3–5 días hábiles dentro de Colombia y 7–14 días hábiles hacia Europa. Recibirás un correo de confirmación con los detalles de seguimiento una vez que tu pedido sea enviado.',
                ],
                [
                    'q' => '¿Ofrecen envío gratis?',
                    'a' => 'Los costos de envío se calculan en el checkout según tu ubicación y el método de envío seleccionado. Ocasionalmente realizamos promociones con envío gratis — suscríbete a nuestro newsletter para mantenerte informado.',
                ],
                [
                    'q' => '¿Puedo rastrear mi pedido?',
                    'a' => 'Sí. Una vez que tu pedido haya sido enviado, recibirás un correo con un número de seguimiento y un enlace para seguir tu paquete en tiempo real.',
                ],
                [
                    'q' => '¿Hacen envíos internacionales?',
                    'a' => 'Sí, enviamos a Colombia y países seleccionados de Europa. Si tu destino no está disponible en el checkout, contáctanos y exploraremos opciones de envío para ti.',
                ],
            ],
        ],

        'pagos' => [
            'label' => 'Pagos',
            'questions' => [
                [
                    'q' => '¿Qué métodos de pago aceptan?',
                    'a' => 'Aceptamos pagos a través de Bold (para transacciones en pesos colombianos) y Stripe (para transacciones en euros). Ambas plataformas aceptan las principales tarjetas de crédito y débito.',
                ],
                [
                    'q' => '¿Es seguro pagar en su sitio web?',
                    'a' => 'Absolutamente. Todos los pagos se procesan a través de pasarelas de pago seguras y certificadas. Nunca almacenamos los datos de tu tarjeta en nuestros servidores.',
                ],
                [
                    'q' => '¿Puedo pagar en una moneda diferente a la de mi país?',
                    'a' => 'Nuestra tienda soporta COP (peso colombiano) y EUR (euro). La moneda disponible se determina por tu contexto de navegación. Los precios siempre se muestran en la moneda que se cobrará.',
                ],
                [
                    'q' => 'Mi pago fue rechazado. ¿Qué debo hacer?',
                    'a' => 'Verifica que los datos de tu tarjeta sean correctos y que tu banco autorice compras en línea. Si el problema persiste, intenta con otro método de pago o contacta a tu banco directamente.',
                ],
            ],
        ],

        'cambios' => [
            'label' => 'Cambios y devoluciones',
            'questions' => [
                [
                    'q' => '¿Cuál es su política de devoluciones?',
                    'a' => 'Puedes solicitar una devolución dentro de los 14 días siguientes a la recepción de tu pedido, siempre que el artículo esté en su estado original, sin usar y con todas las etiquetas adjuntas. Contáctanos para iniciar el proceso.',
                ],
                [
                    'q' => '¿Cómo solicito un cambio?',
                    'a' => 'Contáctanos con tu número de pedido y los detalles del artículo que deseas cambiar. Te guiaremos en el proceso y confirmaremos la disponibilidad del reemplazo.',
                ],
                [
                    'q' => '¿Quién cubre el costo del envío de devolución?',
                    'a' => 'Los costos de envío de devolución son responsabilidad del cliente, a menos que el artículo haya llegado dañado o defectuoso. En esos casos, nosotros cubrimos el costo del envío de devolución.',
                ],
                [
                    'q' => '¿Cuánto tiempo tarda en llegar mi reembolso?',
                    'a' => 'Una vez que recibamos e inspeccionemos el artículo devuelto, los reembolsos se procesan en 5–10 días hábiles. El reembolso se emitirá al método de pago original.',
                ],
            ],
        ],

        'cuenta' => [
            'label' => 'Mi cuenta',
            'questions' => [
                [
                    'q' => '¿Necesito una cuenta para hacer un pedido?',
                    'a' => 'No, puedes pagar como invitado. Sin embargo, crear una cuenta te permite rastrear tus pedidos, gestionar tus direcciones, guardar artículos en tu lista de deseos y disfrutar de un checkout más rápido.',
                ],
                [
                    'q' => '¿Cómo creo una cuenta?',
                    'a' => 'Haz clic en el ícono de usuario en la barra de navegación superior y selecciona "Regístrate". Ingresa tu nombre, correo electrónico y contraseña. Recibirás un correo de confirmación para verificar tu cuenta.',
                ],
                [
                    'q' => 'Olvidé mi contraseña. ¿Cómo la restablezco?',
                    'a' => 'Haz clic en "Iniciar sesión" y luego selecciona "¿Olvidaste tu contraseña?". Ingresa tu dirección de correo y te enviaremos un enlace para crear una nueva contraseña.',
                ],
                [
                    'q' => '¿Cómo actualizo mi información personal?',
                    'a' => 'Inicia sesión en tu cuenta y navega a tu perfil. Allí puedes actualizar tu nombre, correo, número de teléfono y direcciones guardadas.',
                ],
            ],
        ],

    ],

    'empty' => 'No hay preguntas en esta categoría.',

    'cta' => [
        'heading' => '¿No encontraste lo que buscabas?',
        'body' => 'Nuestro equipo está listo para ayudarte con cualquier consulta.',
        'button' => 'Contáctanos',
    ],

];
