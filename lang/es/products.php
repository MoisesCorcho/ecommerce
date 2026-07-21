<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'producto',
        'plural' => 'productos',
    ],

    'pages' => [
        'create_title' => 'Nuevo producto',
    ],

    'tabs' => [
        'product' => 'Producto',
        'details' => 'Datos',
        'variants_prices' => 'Variantes y precios',
        'images' => 'Imágenes',
    ],

    'section' => [
        'identity' => 'Identidad del producto',
        'identity_description' => 'Nombre, categoría y visibilidad en el catálogo. Publicar exige al menos una variante activa con precio.',
        'attributes' => 'Atributos y estado',
        'attributes_description' => 'Detalles comerciales y flags de publicación.',
        'variants' => 'Variantes vendibles',
        'variants_description' => 'Cada variante es una opción de compra (SKU). Los precios son enteros: COP en pesos; EUR en centavos.',
        'gallery' => 'Galería del producto',
        'gallery_description' => 'Vista en cuadrícula. Arrastrá las tarjetas (o usá ↑↓) para reordenar. Solo una puede ser primaria.',
    ],

    'fields' => [
        'name' => 'Nombre',
        'slug' => 'Slug',
        'category' => 'Categoría',
        'description' => 'Descripción',
        'material' => 'Material',
        'dimensions' => 'Dimensiones',
        'is_preorder' => 'Preventa',
        'is_active' => 'Publicado',
        'status' => 'Estado',
        'variants_count' => 'Variantes',
        'created_at' => 'Creado',
        'updated_at' => 'Actualizado',
        'deleted_at' => 'Eliminado',
        'variants' => 'Variantes',
        'sku' => 'SKU',
        'color' => 'Color',
        'size' => 'Talla / tamaño',
        'stock' => 'Stock',
        'variant_active' => 'Variante activa',
        'prices' => 'Precios por moneda',
        'currency' => 'Moneda',
        'price' => 'Precio (entero)',
        'compare_at_price' => 'Precio de comparación',
        'images' => 'Imágenes',
        'file' => 'Archivo',
        'primary' => 'Primaria',
        'primary_image' => 'Imagen primaria',
    ],

    'placeholders' => [
        'name' => 'Ej. Bolso Honey',
        'slug' => 'bolso-honey',
        'no_category' => 'Sin categoría',
        'description' => 'Materiales, uso recomendado, detalles de la pieza…',
        'material' => 'Cuero, lona, etc.',
        'dimensions' => '30 × 20 × 10 cm',
        'sku' => 'LHB-HONEY-01',
        'color' => 'Negro',
        'size' => 'Única, M, 30cm…',
        'price' => '799000',
        'filter_all' => 'Todos',
    ],

    'helpers' => [
        'category_optional' => 'Opcional. Puedes asignarla o cambiarla más tarde.',
        'is_preorder' => 'Marca si el producto se vende antes de stock físico.',
        'is_active' => 'Requiere ≥1 variante activa con ≥1 precio (cualquier moneda). Si no se cumple, el guardado fallará con un mensaje claro.',
        'variant_active' => 'Solo las activas cuentan para publicar el producto.',
        'price_units' => 'COP: pesos enteros. EUR: centavos (12900 = €129,00).',
        'compare_at_price' => 'Opcional. Precio “antes” para mostrar descuento.',
        'image_file' => 'JPG, PNG o WebP · máx. 5 MB',
        'primary_image' => 'Al activarla se desmarcan las demás.',
        'primary_image_default' => 'Solo una por producto: al activarla se desmarcan las demás.',
    ],

    'status' => [
        'published' => 'Publicado',
        'draft' => 'Borrador',
    ],

    'filters' => [
        'published' => 'Publicado',
        'published_only' => 'Solo publicados',
        'drafts_only' => 'Solo borradores',
        'preorder' => 'Preventa',
        'preorder_only' => 'En preventa',
        'no_preorder' => 'Sin preventa',
        'trashed' => 'Eliminados',
    ],

    'actions' => [
        'create' => 'Nuevo producto',
        'edit' => 'Editar',
        'move_to_trash' => 'Mover a papelera',
        'restore' => 'Restaurar',
        'restore_selected' => 'Restaurar seleccionados',
        'force_delete' => 'Eliminar definitivamente',
        'confirm_delete' => 'Sí, eliminar',
        'confirm_force_delete' => 'Eliminar para siempre',
        'add_price' => 'Añadir precio',
        'add_variant' => 'Añadir variante',
        'add_image' => 'Añadir imagen',
    ],

    'modals' => [
        'delete_heading' => 'Eliminar producto',
        'delete_description' => 'El producto se moverá a la papelera. Podrás restaurarlo después.',
        'delete_bulk_heading' => 'Eliminar productos',
        'delete_bulk_description' => 'Se moverán a la papelera (soft delete). Podrás restaurarlos después.',
        'force_delete_heading' => 'Eliminar definitivamente',
        'force_delete_description' => 'Esta acción no se puede deshacer.',
    ],

    'empty' => [
        'heading' => 'No hay productos todavía',
        'description' => 'Crea el primer producto con al menos una variante y precio para poder publicarlo.',
    ],

    'notifications' => [
        'created' => 'Producto creado',
        'updated' => 'Producto actualizado',
    ],

    'item_labels' => [
        'price' => 'Precio',
        'new_variant' => 'Nueva variante',
        'variant_inactive' => ':sku (inactiva)',
        'primary' => 'Primaria',
        'new_image' => 'Nueva imagen',
    ],

    'validation' => [
        'sku_unique' => 'El SKU «:sku» ya pertenece a otra variante.',
        'slug_unique' => 'El slug ya está en uso por otro producto.',
        'variant_not_owned' => 'Una de las variantes no pertenece a este producto.',
        'price_not_owned' => 'Un precio no pertenece a la variante indicada.',
        'image_not_owned' => 'Una imagen no pertenece a este producto.',
    ],

    'exceptions' => [
        'cannot_publish_missing_variant_price' => 'No se puede publicar el producto: se requiere al menos una variante activa con al menos un precio en una moneda soportada.',
    ],
];
