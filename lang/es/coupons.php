<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'cupón',
        'plural' => 'cupones',
    ],

    'navigation' => [
        'label' => 'Cupones',
    ],

    'pages' => [
        'create_title' => 'Nuevo cupón',
        'edit_title' => 'Editar cupón',
    ],

    'sections' => [
        'identity' => 'Identidad del cupón',
        'identity_description' => 'Código único que el cliente ingresa en el checkout. Se guarda en mayúsculas.',
        'discount' => 'Reglas de descuento',
        'discount_description' => 'Porcentaje (1–100, cualquier moneda) o monto fijo en una moneda. El descuento aplica solo al subtotal de líneas.',
        'limits' => 'Límites de uso y vigencia',
        'limits_description' => 'Topes y ventana opcionales. Vacío = ilimitado o sin cierre.',
        'status' => 'Estado',
        'redemptions' => 'Redenciones',
    ],

    'fields' => [
        'code' => 'Código',
        'type' => 'Tipo',
        'value' => 'Valor',
        'currency' => 'Moneda',
        'min_order_amount' => 'Monto mínimo de compra',
        'min_order_currency' => 'Moneda del mínimo',
        'usage_limit' => 'Límite global de usos',
        'usage_limit_per_user' => 'Límite de usos por usuario',
        'used_count' => 'Usos',
        'starts_at' => 'Inicio de vigencia',
        'expires_at' => 'Fin de vigencia',
        'is_active' => 'Activo',
        'status' => 'Estado',
        'created_at' => 'Creado',
        'updated_at' => 'Actualizado',
        'discount_amount' => 'Monto de descuento',
        'order_number' => 'Pedido',
        'user' => 'Usuario',
        'redeemed_at' => 'Redimido el',
    ],

    'placeholders' => [
        'code' => 'VERANO25',
        'value_percentage' => '10',
        'value_fixed' => '50000',
        'unlimited' => 'Ilimitado',
        'filter_all' => 'Todos',
        'no_user' => 'Invitado',
    ],

    'helpers' => [
        'code' => 'Letras, números y guiones. Se guarda en mayúsculas (máx. 32).',
        'value_percentage' => 'Entero de 1 a 100. Se aplica floor al calcular.',
        'value_fixed' => 'Entero en unidades menores (COP pesos / EUR céntimos).',
        'currency_fixed' => 'Obligatoria en cupones fijos. Debe coincidir con la moneda del carrito.',
        'currency_percentage' => 'Dejar vacío en porcentaje (aplica a COP y EUR).',
        'min_order' => 'Opcional. Se compara con el subtotal de líneas en la moneda del carrito.',
        'usage_limit' => 'Nulo = redenciones ilimitadas.',
        'usage_limit_per_user' => 'Solo usuarios autenticados. Los guests usan solo el límite global.',
        'is_active' => 'Un cupón inactivo no se aplica aunque las fechas sean válidas.',
        'immutable_after_redemption' => 'Tipo, valor y moneda no se pueden cambiar si ya hay redenciones.',
    ],

    'filters' => [
        'type' => 'Tipo',
        'active' => 'Activo',
        'active_only' => 'Solo activos',
        'inactive_only' => 'Solo inactivos',
        'currency' => 'Moneda',
    ],

    'status' => [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
    ],

    'actions' => [
        'create' => 'Nuevo cupón',
        'edit' => 'Editar',
        'confirm_delete' => 'Eliminar',
    ],

    'empty' => [
        'heading' => 'Todavía no hay cupones',
        'description' => 'Creá un cupón percentage o fixed para promociones en checkout.',
        'redemptions_heading' => 'Sin redenciones todavía',
        'redemptions_description' => 'Las redenciones aparecen cuando se crean pedidos con este código.',
    ],

    'notifications' => [
        'created' => 'Cupón creado.',
        'updated' => 'Cupón actualizado.',
    ],

    'validation' => [
        'code_required' => 'El código del cupón es obligatorio.',
        'code_format' => 'El código solo puede tener letras, números y guiones (máx. 32).',
        'code_unique' => 'Este código de cupón ya está en uso.',
        'type_required' => 'El tipo de cupón es obligatorio.',
        'value_required' => 'El valor del cupón es obligatorio.',
        'value_percentage_range' => 'El porcentaje debe ser un entero entre 1 y 100.',
        'value_fixed_positive' => 'El valor fijo debe ser un entero positivo.',
        'currency_required_for_fixed' => 'La moneda es obligatoria en cupones fijos.',
        'currency_must_be_null_for_percentage' => 'Los cupones de porcentaje no deben tener moneda.',
        'min_order_currency_required' => 'La moneda del mínimo es obligatoria si hay monto mínimo.',
        'immutable_fields' => 'Tipo, valor y moneda no se pueden cambiar después de redimir el cupón.',
    ],

    'errors' => [
        // Mensaje genérico seguro para storefront (D45 / R14).
        'invalid' => 'Este código de cupón no es válido.',
        // Reasons específicos admin / dominio (también útiles en logs).
        'not_found' => 'Código de cupón no encontrado.',
        'inactive' => 'Este cupón está inactivo.',
        'not_started' => 'Este cupón aún no es válido.',
        'expired' => 'Este cupón expiró.',
        'currency_mismatch' => 'Este cupón no aplica a la moneda del carrito.',
        'min_not_met' => 'El subtotal del pedido no alcanza el mínimo del cupón.',
        'usage_exhausted' => 'Este cupón alcanzó su límite de usos.',
        'per_user_exhausted' => 'Ya usaste este cupón el máximo de veces permitido.',
        'immutable_fields' => 'Tipo, valor y moneda no se pueden cambiar si ya hay redenciones.',
    ],

    'relation' => [
        'redemptions_title' => 'Redenciones',
    ],
];
