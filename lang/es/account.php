<?php

declare(strict_types=1);

return [
    'fields' => [
        'name' => 'Nombre',
        'email' => 'Correo electrónico',
        'phone' => 'Teléfono',
    ],

    'errors' => [
        'current_password_incorrect' => 'Tu contraseña actual es incorrecta.',
    ],

    'breadcrumb' => [
        'home' => 'Inicio',
        'account' => 'Mi cuenta',
    ],

    'nav' => [
        'label' => 'Secciones de la cuenta',
        'profile' => 'Perfil',
        'addresses' => 'Direcciones',
        'orders' => 'Mis pedidos',
        'reviews' => 'Mis reseñas',
        'logout' => 'Cerrar sesión',
        'secondary_label' => 'Cuenta',
    ],

    'profile' => [
        'title' => 'Mi cuenta',
        'subtitle' => 'Administra tu información personal y tu contraseña.',
        'section_title' => 'Información personal',
        'submit' => 'Guardar cambios',
        'updated' => 'Tu perfil se actualizó. Si cambiaste tu correo, revisa tu bandeja de entrada para verificarlo.',
        'greeting' => 'Bienvenido/a de nuevo, :name',
    ],

    'password' => [
        'section_title' => 'Cambiar contraseña',
        'current' => 'Contraseña actual',
        'new' => 'Nueva contraseña',
        'confirmation' => 'Confirma la nueva contraseña',
        'submit' => 'Actualizar contraseña',
        'updated' => 'Tu contraseña se actualizó.',
    ],

    'addresses' => [
        'title' => 'Mis direcciones',
        'subtitle' => 'Administra las direcciones de envío guardadas en tu cuenta.',
        'add_new' => 'Agregar dirección',
        'save' => 'Guardar dirección',
        'saved' => 'Dirección guardada.',
        'cancel' => 'Cancelar',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'deleted' => 'Dirección eliminada.',
        'confirm_delete' => '¿Eliminar esta dirección?',
        'empty' => 'Todavía no agregaste ninguna dirección.',
        'default_badge' => 'Predeterminada',
        'make_default' => 'Marcar como predeterminada',
        'default_updated' => 'Dirección predeterminada actualizada.',
        'fields' => [
            'label' => 'Etiqueta',
            'full_name' => 'Nombre completo',
            'phone' => 'Teléfono',
            'address_line_1' => 'Dirección línea 1',
            'address_line_2' => 'Dirección línea 2',
            'city' => 'Ciudad',
            'state' => 'Departamento / Provincia',
            'country' => 'Código de país',
            'postal_code' => 'Código postal',
            'is_default' => 'Marcar como dirección predeterminada',
        ],
    ],

    'orders' => [
        'title' => 'Mis pedidos',
        'subtitle' => 'Pedidos que ya fueron pagados o que están en un estado posterior.',
        'empty' => 'Todavía no tienes pedidos.',
        'empty_title' => 'Aún no tienes pedidos',
        'empty_cta' => 'Explorar productos',
        'back_to_list' => '← Volver a mis pedidos',
        'shipping_address' => 'Dirección de envío',
        'detail_title' => 'Leen Handbags | Detalle del pedido',
    ],

    'reviews' => [
        'title' => 'Mis reseñas',
        'subtitle' => 'Administra las reseñas que has escrito.',
        'empty' => 'Todavía no has escrito ninguna reseña.',
        'empty_title' => 'Aún no tienes reseñas',
        'empty_cta' => 'Explorar productos',
        'rating_aria' => ':rating de 5 estrellas',
        'save' => 'Guardar reseña',
        'cancel' => 'Cancelar',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'confirm_delete' => '¿Eliminar esta reseña?',
        'updated' => 'Tu reseña se actualizó y vuelve a estar pendiente de moderación.',
        'deleted' => 'Reseña eliminada.',
        'fields' => [
            'rating' => 'Calificación',
            'comment' => 'Comentario',
        ],
        'status' => [
            'approved' => 'Aprobada',
            'pending' => 'Pendiente de moderación',
        ],
    ],
];
