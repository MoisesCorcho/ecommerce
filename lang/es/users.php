<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'usuario',
        'plural' => 'usuarios',
    ],

    'pages' => [
        'create_title' => 'Nuevo usuario',
    ],

    'section' => [
        'account_details' => 'Datos de la cuenta',
        'account_details_description' => 'Información de acceso y contacto del comprador o administrador.',
    ],

    'fields' => [
        'name' => 'Nombre',
        'email' => 'Email',
        'phone' => 'Teléfono',
        'password' => 'Contraseña',
        'addresses_count' => 'Direcciones',
        'created_at' => 'Creado',
        'updated_at' => 'Actualizado',
    ],

    'placeholders' => [
        'phone' => '+57 300 123 4567',
        'empty' => '—',
    ],

    'helpers' => [
        'phone_optional' => 'Opcional. Formato libre (recomendado E.164).',
        'password_keep' => 'Dejá vacío para mantener la contraseña actual.',
    ],

    'actions' => [
        'create' => 'Nuevo usuario',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'delete_selected' => 'Eliminar seleccionados',
        'confirm_delete' => 'Sí, eliminar',
    ],

    'modals' => [
        'delete_heading' => 'Eliminar usuario',
        'delete_description' => 'El usuario se eliminará de forma lógica (soft delete) y dejará de verse en el listado.',
        'delete_bulk_heading' => 'Eliminar usuarios',
        'delete_bulk_description' => 'Los usuarios se eliminarán de forma lógica (soft delete) y dejarán de verse en el listado.',
    ],

    'empty' => [
        'heading' => 'No hay usuarios todavía',
        'description' => 'Creá la primera cuenta de comprador o administrador.',
    ],

    'notifications' => [
        'created' => 'Usuario creado',
        'updated' => 'Usuario actualizado',
    ],

    'validation' => [
        'name_required' => 'El nombre es obligatorio.',
        'email_required' => 'El email es obligatorio.',
        'email_invalid' => 'El email no es válido.',
        'email_unique' => 'El email ya está en uso por otro usuario.',
        'password_required_on_create' => 'La contraseña es obligatoria al crear un usuario.',
    ],
];
