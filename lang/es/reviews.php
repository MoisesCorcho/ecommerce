<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'opinión',
        'plural' => 'opiniones',
    ],

    'navigation' => [
        'label' => 'Opiniones',
    ],

    'pages' => [
        'view_title' => 'Opinión',
        'list_title' => 'Opiniones',
    ],

    'sections' => [
        'details' => 'Detalle de la opinión',
        'moderation' => 'Moderación',
        'content' => 'Contenido',
    ],

    'fields' => [
        'product' => 'Producto',
        'user' => 'Cliente',
        'rating' => 'Calificación',
        'comment' => 'Comentario',
        'is_approved' => 'Aprobada',
        'is_verified_purchase' => 'Compra verificada',
        'created_at' => 'Creada',
        'updated_at' => 'Actualizada',
    ],

    'actions' => [
        'approve' => 'Aprobar',
        'unapprove' => 'Desaprobar',
        'delete' => 'Eliminar',
        'submit' => 'Enviar opinión',
        'update' => 'Actualizar opinión',
        'delete_own' => 'Eliminar mi opinión',
    ],

    'filters' => [
        'is_approved' => 'Aprobación',
        'approved_only' => 'Solo aprobadas',
        'pending_only' => 'Solo pendientes',
        'all' => 'Todas',
        'is_verified' => 'Compra verificada',
        'verified_only' => 'Solo verificadas',
        'unverified_only' => 'Solo no verificadas',
    ],

    'status' => [
        'pending_moderation' => 'Pendiente de moderación',
        'approved' => 'Aprobada',
        'verified_purchase' => 'Compra verificada',
    ],

    'empty' => [
        'heading' => 'Aún no hay opiniones',
        'description' => 'Las opiniones de clientes aparecerán aquí para moderar.',
        'no_reviews' => 'Todavía no hay opiniones para este producto.',
        'no_comment' => 'Sin comentario',
    ],

    'notifications' => [
        'approved' => 'Opinión aprobada',
        'unapproved' => 'Opinión desaprobada',
        'deleted' => 'Opinión eliminada',
        'saved' => 'Opinión guardada',
        'saved_pending' => 'Opinión enviada y pendiente de moderación',
        'updated_pending' => 'Opinión actualizada; vuelve a moderación',
    ],

    'ui' => [
        'section_title' => 'Opiniones',
        'average_label' => 'Calificación promedio',
        'count_label' => ':count opinión|:count opiniones',
        'your_review' => 'Tu opinión',
        'write_review' => 'Escribe una opinión',
        'edit_review' => 'Edita tu opinión',
        'login_required' => 'Inicia sesión para dejar una opinión.',
        'not_eligible' => 'Puedes dejar una opinión después de comprar este producto.',
        'pending_notice' => 'Tu opinión está pendiente de moderación y aún no es pública.',
        'rating_required' => 'Elige una calificación de 1 a 5.',
        'comment_placeholder' => 'Comparte tu experiencia (opcional)',
        'stars' => ':rating de 5 estrellas',
        'delete_confirm' => '¿Eliminar esta opinión de forma permanente?',
    ],

    'errors' => [
        'unauthenticated' => 'Debes iniciar sesión para gestionar opiniones.',
        'not_eligible' => 'Solo puedes opinar sobre productos que hayas comprado.',
        'already_exists' => 'Ya dejaste una opinión de este producto. Actualiza la existente.',
        'forbidden' => 'No tienes permiso para esta acción sobre la opinión.',
        'invalid_rating' => 'La calificación debe ser un entero entre 1 y 5.',
        'comment_too_long' => 'El comentario no puede superar 2000 caracteres.',
        'not_found' => 'Opinión no encontrada.',
        'rate_limited' => 'Demasiados intentos. Espera un momento.',
    ],

    'validation' => [
        'rating_required' => 'La calificación es obligatoria.',
        'rating_integer' => 'La calificación debe ser un entero.',
        'rating_between' => 'La calificación debe estar entre 1 y 5.',
        'comment_max' => 'El comentario no puede superar 2000 caracteres.',
    ],
];
