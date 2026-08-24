<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'mensaje de contacto',
        'plural' => 'mensajes de contacto',
    ],

    'navigation' => [
        'label' => 'Mensajes de contacto',
    ],

    'pages' => [
        'view_title' => 'Mensaje de contacto',
        'list_title' => 'Mensajes de contacto',
    ],

    'sections' => [
        'sender' => 'Datos del remitente',
        'message' => 'Contenido del mensaje',
        'management' => 'Gestión interna',
    ],

    'fields' => [
        'name' => 'Nombre',
        'email' => 'Correo electrónico',
        'subject' => 'Asunto',
        'message' => 'Mensaje',
        'status' => 'Estado',
        'user' => 'Usuario registrado',
        'ip_address' => 'Dirección IP',
        'user_agent' => 'Navegador / Dispositivo',
        'read_at' => 'Leído el',
        'replied_at' => 'Respondido el',
        'admin_notes' => 'Notas internas',
        'created_at' => 'Recibido',
        'updated_at' => 'Actualizado',
    ],

    'actions' => [
        'mark_as_read' => 'Marcar como leído',
        'mark_as_replied' => 'Marcar como respondido',
        'edit_notes' => 'Editar notas',
        'delete' => 'Eliminar',
    ],

    'filters' => [
        'status' => 'Estado',
        'all' => 'Todos',
    ],

    'empty' => [
        'heading' => 'No hay mensajes de contacto',
        'description' => 'Los mensajes enviados por los clientes desde la tienda aparecerán aquí.',
    ],

    'notifications' => [
        'marked_as_read' => 'Mensaje marcado como leído',
        'marked_as_replied' => 'Mensaje marcado como respondido',
        'notes_updated' => 'Notas internas actualizadas',
        'deleted' => 'Mensaje eliminado',
    ],

    'mail' => [
        'heading' => 'Nuevo mensaje de contacto recibido',
        'view_in_panel' => 'Ver en Panel de Administración',
        'footer_note' => 'Este correo fue generado automáticamente por la tienda en línea Leen.',
    ],
];
