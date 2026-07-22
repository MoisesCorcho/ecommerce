<?php

declare(strict_types=1);

return [
    'errors' => [
        'not_eligible' => 'Esta variante no está disponible para la compra en la moneda del carrito.',
        'insufficient_stock' => 'No hay stock suficiente para esta variante. Máximo disponible: :max.',
        'quantity_max' => 'La cantidad no puede superar :max unidades por línea.',
        'quantity_invalid' => 'La cantidad debe ser cero (para quitar) o un entero positivo.',
        'currency_blocked' => 'No se puede cambiar la moneda: una o más líneas no tienen precio en :currency.',
        'access_denied' => 'No tenés permiso para modificar este carrito.',
        'item_not_found' => 'No se encontró la línea del carrito.',
        'variant_not_found' => 'No se encontró la variante de producto.',
    ],

    'fields' => [
        'quantity' => 'Cantidad',
        'currency' => 'Moneda',
        'product_variant_id' => 'Variante de producto',
    ],
];
