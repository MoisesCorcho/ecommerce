<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'color',
        'plural' => 'colores',
    ],

    'pages' => [
        'create_title' => 'Nuevo color',
    ],

    'section' => [
        'details' => 'Datos del color',
        'details_description' => 'Configura el nombre y el tono hexadecimal para las muestras del catálogo y el detalle de producto.',
    ],

    'fields' => [
        'name' => 'Nombre',
        'slug' => 'Slug',
        'hex_code' => 'Código Hexadecimal',
        'sort_order' => 'Orden',
        'is_active' => 'Activo',
        'variants_count' => 'Variantes',
        'created_at' => 'Creado',
        'updated_at' => 'Actualizado',
    ],

    'placeholders' => [
        'name' => 'Ej. Cognac, Negro, Verde Oliva',
        'slug' => 'cognac',
        'hex_code' => '#8B5A2B',
    ],

    'helpers' => [
        'hex_code' => 'Código de color que se mostrará en los círculos de selección en la tienda.',
    ],

    'actions' => [
        'create' => 'Nuevo color',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'delete_selected' => 'Eliminar seleccionados',
        'confirm_delete' => 'Sí, eliminar',
    ],

    'empty' => [
        'heading' => 'No hay colores todavía',
        'description' => 'Crea el primer color para asociarlo a las variantes de productos.',
    ],

    'notifications' => [
        'created' => 'Color creado',
        'updated' => 'Color actualizado',
    ],

    'validation' => [
        'name_required' => 'El nombre del color es obligatorio.',
        'slug_unique' => 'El slug ya está en uso por otro color.',
        'hex_required' => 'El código hexadecimal es obligatorio.',
    ],
];
