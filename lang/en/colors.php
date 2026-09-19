<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'color',
        'plural' => 'colors',
    ],

    'pages' => [
        'create_title' => 'New color',
    ],

    'section' => [
        'details' => 'Color details',
        'details_description' => 'Configure the name and hexadecimal swatch code for the storefront catalog and product detail.',
    ],

    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'hex_code' => 'Hex Code',
        'sort_order' => 'Sort Order',
        'is_active' => 'Active',
        'variants_count' => 'Variants',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    'placeholders' => [
        'name' => 'E.g. Cognac, Black, Olive Green',
        'slug' => 'cognac',
        'hex_code' => '#8B5A2B',
    ],

    'helpers' => [
        'hex_code' => 'Hex color code displayed in the storefront swatch pickers.',
    ],

    'actions' => [
        'create' => 'New color',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'delete_selected' => 'Delete selected',
        'confirm_delete' => 'Yes, delete',
    ],

    'empty' => [
        'heading' => 'No colors yet',
        'description' => 'Create the first color to associate with product variants.',
    ],

    'notifications' => [
        'created' => 'Color created',
        'updated' => 'Color updated',
    ],

    'validation' => [
        'name_required' => 'The color name is required.',
        'slug_unique' => 'The slug is already in use by another color.',
        'hex_required' => 'The hex code is required.',
    ],
];
