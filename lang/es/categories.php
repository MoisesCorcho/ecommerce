<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'categoría',
        'plural' => 'categorías',
    ],

    'pages' => [
        'create_title' => 'Nueva categoría',
    ],

    'section' => [
        'details' => 'Datos de la categoría',
        'details_description' => 'Organiza el catálogo en una jerarquía opcional. El slug se usa en URLs y listados. El orden se define arrastrando filas en el listado.',
    ],

    'fields' => [
        'name' => 'Nombre',
        'slug' => 'Slug',
        'parent' => 'Padre',
        'parent_category' => 'Categoría padre',
        'products_count' => 'Productos',
        'sort_order' => 'Orden',
        'created_at' => 'Creada',
        'updated_at' => 'Actualizada',
    ],

    'placeholders' => [
        'name' => 'Ej. Bolsos de mano',
        'slug' => 'bolsos-de-mano',
        'root' => 'Raíz',
    ],

    'helpers' => [
        'parent_optional' => 'Opcional. Deja vacío para una categoría de nivel raíz.',
    ],

    'actions' => [
        'create' => 'Nueva categoría',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'delete_selected' => 'Eliminar seleccionadas',
        'confirm_delete' => 'Sí, eliminar',
    ],

    'modals' => [
        'delete_heading' => 'Eliminar categoría',
        'delete_description' => 'Los productos asociados quedarán sin categoría.',
        'delete_bulk_heading' => 'Eliminar categorías',
        'delete_bulk_description' => 'Los productos asociados quedarán sin categoría. Esta acción no se puede deshacer desde aquí.',
    ],

    'empty' => [
        'heading' => 'No hay categorías todavía',
        'description' => 'Crea la primera categoría para organizar el catálogo de productos.',
    ],

    'notifications' => [
        'created' => 'Categoría creada',
        'updated' => 'Categoría actualizada',
    ],

    'validation' => [
        'name_required' => 'El nombre de la categoría es obligatorio.',
        'slug_unique' => 'El slug ya está en uso por otra categoría.',
    ],
];
