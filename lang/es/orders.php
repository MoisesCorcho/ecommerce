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
        'title' => 'Gracias por tu compra',
        'body' => 'Tu pedido :number fue recibido y está pendiente de pago.',
        'status' => 'Estado: :status',
    ],

    'checkout' => [
        'title' => 'Checkout',
        'contact' => 'Contacto',
        'shipping' => 'Envío',
    ],

    'errors' => [
        'cart_empty' => 'Tu carrito está vacío. Agregá productos antes del checkout.',
        'cart_not_ready' => 'Uno o más ítems del carrito no están disponibles. Volvé al carrito y revisá cantidades o disponibilidad.',
        'insufficient_stock' => 'No hay stock suficiente para ":product". Máximo disponible: :max.',
        'not_eligible' => '":product" no está disponible para compra en la moneda del carrito.',
        'access_denied' => 'No tenés permiso para acceder a este pedido.',
        'cart_access_denied' => 'No tenés permiso para hacer checkout de este carrito.',
        'cannot_cancel' => 'Solo se pueden cancelar pedidos pendientes.',
        'invalid_address' => 'La dirección seleccionada no es válida o no te pertenece.',
    ],

    'notifications' => [
        'cancelled' => 'Pedido cancelado.',
    ],
];
