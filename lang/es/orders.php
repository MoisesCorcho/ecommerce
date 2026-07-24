<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'Pedido',
        'plural' => 'Pedidos',
    ],

    'navigation' => [
        'label' => 'Pedidos',
    ],

    'fields' => [
        'order_number' => 'Número de pedido',
        'email' => 'Correo electrónico',
        'status' => 'Estado',
        'currency' => 'Moneda',
        'subtotal' => 'Subtotal',
        'shipping_cost' => 'Envío',
        'discount' => 'Descuento',
        'tax_amount' => 'Impuestos',
        'total' => 'Total',
        'customer_notes' => 'Notas del pedido',
        'first_name' => 'Nombre',
        'last_name' => 'Apellidos',
        'phone' => 'Teléfono',
        'shipping_full_name' => 'Nombre completo',
        'shipping_phone' => 'Teléfono de envío',
        'shipping_address_line_1' => 'Dirección línea 1',
        'shipping_address_line_2' => 'Dirección línea 2',
        'shipping_city' => 'Ciudad',
        'shipping_state' => 'Departamento / estado',
        'shipping_country' => 'País',
        'shipping_postal_code' => 'Código postal',
        'shipping_address_id' => 'Dirección guardada',
        'created_at' => 'Creado',
        'items' => 'Ítems',
        'product_name' => 'Producto',
        'variant_label' => 'Variante',
        'sku' => 'SKU',
        'unit_price' => 'Precio unitario',
        'quantity' => 'Cantidad',
    ],

    'sections' => [
        'summary' => 'Resumen del pedido',
        'customer' => 'Cliente',
        'shipping' => 'Dirección de envío',
        'items' => 'Líneas',
        'notes' => 'Notas',
    ],

    'actions' => [
        'confirm' => 'Confirmar pedido',
        'cancel' => 'Cancelar pedido',
        'use_saved_address' => 'Usar dirección guardada',
        'use_one_shot_address' => 'Usar otra dirección',
    ],

    'shipping' => [
        'standard' => 'Envío estándar',
    ],

    'thank_you' => [
        'title' => 'Gracias por su compra',
        'body' => 'Su pedido :number fue recibido y está pendiente de pago.',
        'status' => 'Estado: :status',
        'continue_shopping' => 'Seguir comprando',
        'banner' => [
            'pending' => 'Su pedido está pendiente de pago. Complete el pago para confirmar su pedido.',
            'paid' => 'Pago confirmado. Estamos preparando su pedido.',
            'processing' => 'Su pedido está siendo preparado para envío.',
            'shipped' => 'Su pedido está en camino.',
            'delivered' => 'Su pedido ha sido entregado.',
            'cancelled' => 'Este pedido ha sido cancelado.',
            'refunded' => 'Este pedido ha sido reembolsado.',
        ],
    ],

    'checkout' => [
        'title' => 'Checkout',
        'contact' => 'Contacto',
        'shipping' => 'Envío',
        'breadcrumb_home' => 'Inicio',
        'breadcrumb_cart' => 'Carrito',
        'breadcrumb_checkout' => 'Checkout',
        'back_to_cart' => 'Volver al carrito',
        'summary_title' => 'Tu pedido',
        'address_title' => 'Dirección de envío',
        'shipping_method' => 'Método de envío',
        'secure_badge' => 'Compra segura',
        'secure_note' => 'El pago se procesa de forma segura tras confirmar.',
        'stepper' => [
            'contact' => 'Contacto',
            'address' => 'Dirección',
            'shipping' => 'Envío',
            'payment' => 'Pago',
            'payment_hint' => 'Se gestiona al confirmar',
        ],
        'placeholders' => [
            'first_name' => 'María',
            'last_name' => 'Gómez',
            'email' => 'correo@ejemplo.com',
            'phone' => '+57 300 000 0000',
            'address_line_1' => 'Calle y número',
            'city' => 'Ciudad',
            'postal_code' => 'Código postal',
        ],
    ],

    'errors' => [
        'cart_empty' => 'Su carrito está vacío. Agregue productos antes del checkout.',
        'cart_not_ready' => 'Uno o más ítems del carrito no están disponibles. Vuelva al carrito y revise las cantidades o la disponibilidad.',
        'insufficient_stock' => 'No hay stock suficiente para ":product". Máximo disponible: :max.',
        'not_eligible' => '":product" no está disponible para compra en la moneda del carrito.',
        'access_denied' => 'No tiene permiso para acceder a este pedido.',
        'cart_access_denied' => 'No tiene permiso para hacer checkout de este carrito.',
        'cannot_cancel' => 'Solo se pueden cancelar pedidos pendientes.',
        'invalid_address' => 'La dirección seleccionada no es válida o no le pertenece.',
    ],

    'notifications' => [
        'cancelled' => 'Pedido cancelado.',
    ],
];
