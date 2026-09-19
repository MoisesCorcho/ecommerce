<?php

declare(strict_types=1);

return [
    'errors' => [
        'not_eligible' => 'Esta variante no está disponible para la compra en la moneda del carrito.',
        'insufficient_stock' => 'No hay stock suficiente para esta variante. Máximo disponible: :max.',
        'quantity_max' => 'La cantidad no puede superar :max unidades por línea.',
        'quantity_invalid' => 'La cantidad debe ser cero (para quitar) o un entero positivo.',
        'currency_blocked' => 'No se puede cambiar la moneda: una o más líneas no tienen precio en :currency.',
        'access_denied' => 'No tiene permiso para modificar este carrito.',
        'item_not_found' => 'No se encontró la línea del carrito.',
        'variant_not_found' => 'No se encontró la variante de producto.',
    ],

    'fields' => [
        'quantity' => 'Cantidad',
        'currency' => 'Moneda',
        'product_variant_id' => 'Variante de producto',
    ],

    'page' => [
        'title' => 'Carrito',
        'breadcrumb_home' => 'Inicio',
        'breadcrumb_cart' => 'Carrito',
        'currency_label' => 'Moneda',
        'clear_cart' => 'Vaciar carrito',
        'clear_cart_confirm' => 'Todos los productos agregados se eliminarán del carrito, ¿estás seguro?',
        'clear_cart_cancel' => 'Cancelar',
    ],

    'line' => [
        'sku_label' => 'REF',
        'decrease_quantity' => 'Disminuir cantidad',
        'increase_quantity' => 'Aumentar cantidad',
        'remove' => 'Eliminar producto',
        'out_of_stock' => 'Agotado',
        'unit_price_suffix' => 'c/u',
    ],

    'summary' => [
        'title' => 'Resumen del pedido',
        'items_count' => ':count producto|:count productos',
        'subtotal' => 'Subtotal',
        'threshold_discount' => 'Descuento por volumen (10%)',
        'total' => 'Total',
        'checkout' => 'Finalizar compra',
        'continue_shopping' => 'Continuar comprando',
    ],

    'threshold' => [
        'progress' => 'Añade :amount más para obtener un 10% de descuento',
        'unlocked' => '¡Tienes 10% de descuento aplicado!',
        'discount_label' => 'Descuento por volumen (10%)',
    ],

    'empty' => [
        'title' => 'Tu carrito está vacío',
        'message' => 'Explora nuestra colección y encuentra tu próxima pieza favorita.',
        'cta' => 'Explorar productos',
    ],

    'status' => [
        'quantity_updated' => 'Cantidad actualizada.',
        'line_removed' => 'Línea eliminada.',
        'cart_cleared' => 'Carrito vaciado.',
        'currency_updated' => 'Moneda actualizada.',
    ],

    'stock_banner' => [
        'message' => 'La disponibilidad de uno o más productos en tu carrito ha cambiado.',
    ],
];
