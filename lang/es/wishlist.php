<?php

declare(strict_types=1);

return [
    'notification_types' => [
        'price_drop' => 'Rebaja de precio',
        'low_stock' => 'Últimas unidades',
    ],
    'mail' => [
        'price_drop_subject' => '¡Buenas noticias! :product ha bajado de precio',
        'price_drop_heading' => '¡Tu bolso favorito ahora tiene un precio especial!',
        'price_drop_subheading' => 'Un artículo de tu lista de deseos está en oferta exclusiva.',
        'price_drop_body' => 'Guardaste este bolso en tu lista de deseos y queríamos avisarte de inmediato que su precio ha bajado.',
        'price_drop_cta' => 'Ver en la tienda y comprar',
        'old_price_label' => 'Antes',
        'new_price_label' => 'Ahora',
        'low_stock_subject' => '¡Últimas unidades disponibles de :product!',
        'low_stock_heading' => '¡Date prisa, quedan muy pocas unidades!',
        'low_stock_subheading' => 'Un artículo de tu lista de deseos está a punto de agotarse.',
        'low_stock_body' => 'Quedan solo :stock unidades disponibles de tu bolso favorito. ¡No te quedes sin él!',
        'low_stock_badge' => 'Últimas :stock unidades',
        'low_stock_cta' => 'Comprar ahora antes de que se agote',
        'footer_note' => 'Recibes este correo porque guardaste este producto en tu lista de deseos en Leen.',
    ],
];
