<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'dirección',
        'plural' => 'direcciones',
    ],

    'relation' => [
        'title' => 'Direcciones',
    ],

    'section' => [
        'shipping' => 'Dirección de envío',
        'shipping_description' => 'Datos que se usarán en el checkout. Solo una dirección puede ser predeterminada por usuario.',
    ],

    'fields' => [
        'label' => 'Etiqueta',
        'is_default' => 'Dirección predeterminada',
        'is_default_short' => 'Predet.',
        'full_name' => 'Nombre completo',
        'phone' => 'Teléfono',
        'address_line_1' => 'Línea 1',
        'address_line_2' => 'Línea 2',
        'city' => 'Ciudad',
        'state' => 'Estado / departamento',
        'state_short' => 'Estado',
        'country' => 'País (ISO)',
        'country_short' => 'País',
        'postal_code' => 'Código postal',
        'address' => 'Dirección',
    ],

    'placeholders' => [
        'label' => 'Casa, Oficina…',
        'full_name' => 'Ana Pérez',
        'phone' => '+57 300 123 4567',
        'address_line_1' => 'Calle 10 #20-30',
        'address_line_2' => 'Apto 401, Torre B',
        'city' => 'Medellín',
        'state' => 'Antioquia',
        'country' => 'CO',
        'postal_code' => '050001',
        'empty' => '—',
    ],

    'helpers' => [
        'is_default' => 'Si la marcás, cualquier otra predeterminada del usuario se desmarca.',
        'country' => 'Código de 2 letras (ISO 3166-1 alpha-2).',
    ],

    'actions' => [
        'create' => 'Nueva dirección',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'delete_selected' => 'Eliminar seleccionadas',
    ],

    'empty' => [
        'heading' => 'Sin direcciones',
        'description' => 'Agregá una dirección de envío para este usuario.',
    ],

    'validation' => [
        'full_name_required' => 'El nombre completo es obligatorio.',
        'phone_required' => 'El teléfono es obligatorio.',
        'address_line_1_required' => 'La línea 1 de dirección es obligatoria.',
        'city_required' => 'La ciudad es obligatoria.',
        'state_required' => 'El estado o departamento es obligatorio.',
        'country_invalid' => 'El país debe ser un código ISO de 2 letras.',
        'max_limit_reached' => 'Has alcanzado el límite máximo de 4 direcciones guardadas.',
    ],
];
