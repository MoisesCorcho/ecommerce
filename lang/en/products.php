<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'product',
        'plural' => 'products',
    ],

    'pages' => [
        'create_title' => 'New product',
    ],

    'tabs' => [
        'product' => 'Product',
        'details' => 'Details',
        'variants_prices' => 'Variants and prices',
        'images' => 'Images',
    ],

    'section' => [
        'identity' => 'Product identity',
        'identity_description' => 'Name, category, and catalog visibility. Publishing requires at least one active variant with a price.',
        'attributes' => 'Attributes and status',
        'attributes_description' => 'Commercial details and publication flags.',
        'variants' => 'Sellable variants',
        'variants_description' => 'Each variant is a purchase option (SKU). Prices are integers: COP in pesos; EUR in cents.',
        'gallery' => 'Product gallery',
        'gallery_description' => 'Grid view. Drag cards (or use ↑↓) to reorder. Only one can be primary.',
    ],

    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'category' => 'Category',
        'description' => 'Description',
        'material' => 'Material',
        'dimensions' => 'Dimensions',
        'is_preorder' => 'Preorder',
        'is_active' => 'Published',
        'status' => 'Status',
        'variants_count' => 'Variants',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
        'deleted_at' => 'Deleted',
        'variants' => 'Variants',
        'sku' => 'SKU',
        'color' => 'Color',
        'size' => 'Size',
        'stock' => 'Stock',
        'variant_active' => 'Active variant',
        'prices' => 'Prices by currency',
        'currency' => 'Currency',
        'price' => 'Price (integer)',
        'compare_at_price' => 'Compare-at price',
        'images' => 'Images',
        'file' => 'File',
        'primary' => 'Primary',
        'primary_image' => 'Primary image',
        'image_variant' => 'Variant',
    ],

    'placeholders' => [
        'name' => 'E.g. Honey bag',
        'slug' => 'honey-bag',
        'no_category' => 'No category',
        'description' => 'Materials, recommended use, piece details…',
        'material' => 'Leather, canvas, etc.',
        'dimensions' => '30 × 20 × 10 cm',
        'sku' => 'LHB-HONEY-01',
        'color' => 'Black',
        'size' => 'One size, M, 30cm…',
        'price' => '799000',
        'filter_all' => 'All',
    ],

    'helpers' => [
        'category_optional' => 'Optional. You can assign or change it later.',
        'is_preorder' => 'Mark if the product is sold before physical stock.',
        'is_active' => 'Requires ≥1 active variant with ≥1 price (any currency). Otherwise save fails with a clear message.',
        'variant_active' => 'Only active variants count toward publishing the product.',
        'price_units' => 'COP: whole pesos. EUR: cents (12900 = €129.00).',
        'compare_at_price' => 'Optional. “Before” price to show a discount.',
        'image_file' => 'JPG, PNG or WebP · max. 5 MB',
        'primary_image' => 'When enabled, other images are unmarked.',
        'primary_image_default' => 'Only one per product: enabling it unmarks the others.',
        'image_variant' => 'Leave empty to use this image for every color. Only saved variants appear here — save new variants first if you don\'t see one.',
    ],

    'status' => [
        'published' => 'Published',
        'draft' => 'Draft',
    ],

    'filters' => [
        'published' => 'Published',
        'published_only' => 'Published only',
        'drafts_only' => 'Drafts only',
        'preorder' => 'Preorder',
        'preorder_only' => 'On preorder',
        'no_preorder' => 'Not on preorder',
        'trashed' => 'Deleted',
    ],

    'actions' => [
        'create' => 'New product',
        'edit' => 'Edit',
        'move_to_trash' => 'Move to trash',
        'restore' => 'Restore',
        'restore_selected' => 'Restore selected',
        'force_delete' => 'Delete permanently',
        'confirm_delete' => 'Yes, delete',
        'confirm_force_delete' => 'Delete forever',
        'add_price' => 'Add price',
        'add_variant' => 'Add variant',
        'add_image' => 'Add image',
    ],

    'modals' => [
        'delete_heading' => 'Delete product',
        'delete_description' => 'The product will be moved to the trash. You can restore it later.',
        'delete_bulk_heading' => 'Delete products',
        'delete_bulk_description' => 'They will be moved to the trash (soft delete). You can restore them later.',
        'force_delete_heading' => 'Delete permanently',
        'force_delete_description' => 'This action cannot be undone.',
    ],

    'empty' => [
        'heading' => 'No products yet',
        'description' => 'Create the first product with at least one variant and price to publish it.',
    ],

    'notifications' => [
        'created' => 'Product created',
        'updated' => 'Product updated',
    ],

    'item_labels' => [
        'price' => 'Price',
        'new_variant' => 'New variant',
        'variant_inactive' => ':sku (inactive)',
        'primary' => 'Primary',
        'new_image' => 'New image',
    ],

    'validation' => [
        'sku_unique' => 'The SKU «:sku» already belongs to another variant.',
        'slug_unique' => 'The slug is already used by another product.',
        'variant_not_owned' => 'One of the variants does not belong to this product.',
        'price_not_owned' => 'A price does not belong to the indicated variant.',
        'image_not_owned' => 'An image does not belong to this product.',
        'price_non_negative' => 'The price must be a non-negative integer.',
        'compare_at_price_non_negative' => 'The compare-at price must be a non-negative integer.',
    ],

    'exceptions' => [
        'cannot_publish_missing_variant_price' => 'The product cannot be published: at least one active variant with at least one price in a supported currency is required.',
    ],
];
