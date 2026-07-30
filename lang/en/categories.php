<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'category',
        'plural' => 'categories',
    ],

    'pages' => [
        'create_title' => 'New category',
    ],

    'section' => [
        'details' => 'Category details',
        'details_description' => 'Organize the catalog with an optional hierarchy. The slug is used in URLs and listings. Order is set by dragging rows in the list.',
    ],

    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'parent' => 'Parent',
        'parent_category' => 'Parent category',
        'image' => 'Image',
        'products_count' => 'Products',
        'sort_order' => 'Order',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    'placeholders' => [
        'name' => 'E.g. Handbags',
        'slug' => 'handbags',
        'root' => 'Root',
    ],

    'helpers' => [
        'parent_optional' => 'Optional. Leave empty for a root-level category.',
        'image' => 'Optional. Shown in the categories section on the Home page. Falls back to an initial when empty.',
    ],

    'actions' => [
        'create' => 'New category',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'delete_selected' => 'Delete selected',
        'confirm_delete' => 'Yes, delete',
    ],

    'modals' => [
        'delete_heading' => 'Delete category',
        'delete_description' => 'Associated products will be left without a category.',
        'delete_bulk_heading' => 'Delete categories',
        'delete_bulk_description' => 'Associated products will be left without a category. This action cannot be undone from here.',
    ],

    'empty' => [
        'heading' => 'No categories yet',
        'description' => 'Create the first category to organize the product catalog.',
    ],

    'notifications' => [
        'created' => 'Category created',
        'updated' => 'Category updated',
    ],

    'validation' => [
        'name_required' => 'The category name is required.',
        'slug_unique' => 'The slug is already used by another category.',
    ],
];
