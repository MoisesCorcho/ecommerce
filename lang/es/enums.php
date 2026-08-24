<?php

declare(strict_types=1);

return [
    'currency' => [
        'COP' => 'Peso colombiano',
        'EUR' => 'Euro',
        'USD' => 'Dólar estadounidense',
    ],

    'order_status' => [
        'pending' => 'Pendiente',
        'paid' => 'Pagado',
        'processing' => 'En proceso',
        'shipped' => 'Enviado',
        'delivered' => 'Entregado',
        'cancelled' => 'Cancelado',
        'refunded' => 'Reembolsado',
    ],

    'payment_status' => [
        'pending' => 'Pendiente',
        'approved' => 'Aprobado',
        'declined' => 'Rechazado',
        'refunded' => 'Reembolsado',
    ],

    'payment_provider' => [
        'stripe' => 'Stripe',
        'bold' => 'Bold',
    ],

    'coupon_type' => [
        'percentage' => 'Porcentaje',
        'fixed' => 'Monto fijo',
    ],

    'contact_submission_status' => [
        'new' => 'Nuevo',
        'read' => 'Leído',
        'replied' => 'Respondido',
        'archived' => 'Archivado',
    ],
];
