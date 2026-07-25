<?php

declare(strict_types=1);

return [
    'actions' => [
        'pay' => 'Pagar ahora',
        'retry' => 'Reintentar pago',
    ],

    'fields' => [
        'provider' => 'Proveedor',
        'status' => 'Estado del pago',
        'amount' => 'Monto',
        'currency' => 'Moneda',
        'external_id' => 'ID externo',
        'payment_method' => 'Método de pago',
        'paid_at' => 'Pagado el',
        'refunded_at' => 'Reembolsado el',
        'created_at' => 'Creado',
    ],

    'sections' => [
        'payments' => 'Intentos de pago',
    ],

    'return' => [
        'processing' => 'Estamos confirmando tu pago. Esta página reflejará el estado final cuando el proveedor lo confirme.',
        'cancelled' => 'El pago no se completó. Puedes reintentar mientras el pedido siga pendiente.',
        'paid' => 'Pago confirmado. ¡Gracias!',
    ],

    'errors' => [
        'not_payable' => 'Este pedido no se puede pagar en su estado actual.',
        'already_paid' => 'Este pedido ya está pagado.',
        'stock_unavailable' => 'Uno o más ítems ya no tienen stock suficiente para completar el pago.',
        'access_denied' => 'No tienes permiso para pagar este pedido.',
        'gateway' => 'El proveedor de pagos no pudo iniciar la sesión de checkout. Intenta de nuevo.',
        'invalid_webhook_signature' => 'Firma de webhook de pago inválida.',
        'payment_not_found' => 'Ningún intento de pago coincide con este evento de webhook.',
        'stock_conflict' => 'El pago fue aprobado pero el pedido no pudo marcarse como pagado por stock insuficiente. Operaciones fue notificada.',
    ],
];
