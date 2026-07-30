<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'address',
        'plural' => 'addresses',
    ],

    'relation' => [
        'title' => 'Addresses',
    ],

    'section' => [
        'shipping' => 'Shipping address',
        'shipping_description' => 'Data used at checkout. Only one address can be the default per user.',
    ],

    'fields' => [
        'label' => 'Label',
        'is_default' => 'Default address',
        'is_default_short' => 'Default',
        'full_name' => 'Full name',
        'phone' => 'Phone',
        'address_line_1' => 'Line 1',
        'address_line_2' => 'Line 2',
        'city' => 'City',
        'state' => 'State / department',
        'state_short' => 'State',
        'country' => 'Country (ISO)',
        'country_short' => 'Country',
        'postal_code' => 'Postal code',
        'address' => 'Address',
    ],

    'placeholders' => [
        'label' => 'Home, Office…',
        'full_name' => 'Ana Pérez',
        'phone' => '+57 300 123 4567',
        'address_line_1' => 'Calle 10 #20-30',
        'address_line_2' => 'Apt 401, Tower B',
        'city' => 'Medellín',
        'state' => 'Antioquia',
        'country' => 'CO',
        'postal_code' => '050001',
        'empty' => '—',
    ],

    'helpers' => [
        'is_default' => 'If you mark this one, any other default address for the user is unmarked.',
        'country' => '2-letter code (ISO 3166-1 alpha-2).',
    ],

    'actions' => [
        'create' => 'New address',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'delete_selected' => 'Delete selected',
    ],

    'empty' => [
        'heading' => 'No addresses',
        'description' => 'Add a shipping address for this user.',
    ],

    'validation' => [
        'full_name_required' => 'Full name is required.',
        'phone_required' => 'Phone is required.',
        'address_line_1_required' => 'Address line 1 is required.',
        'city_required' => 'City is required.',
        'state_required' => 'State or department is required.',
        'country_invalid' => 'Country must be a 2-letter ISO code.',
    ],
];
