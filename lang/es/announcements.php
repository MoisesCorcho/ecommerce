<?php

declare(strict_types=1);

return [
    'navigation' => [
        'group' => 'Marketing',
        'label' => 'Barra de Anuncios',
    ],
    'model' => [
        'label' => 'Anuncio',
        'plural' => 'Barra de Anuncios',
    ],
    'sections' => [
        'content' => 'Contenido del Anuncio',
        'content_description' => 'Configura los textos traducibles y el enlace opcional para el anuncio.',
        'schedule' => 'Visibilidad y Programación',
        'schedule_description' => 'Define la prioridad y el rango de fechas en que estará visible en la tienda.',
    ],
    'fields' => [
        'text' => 'Texto del Anuncio',
        'text_es' => 'Texto en Español',
        'text_en' => 'Texto en Inglés',
        'url' => 'Enlace (URL)',
        'url_helper' => 'Opcional. Puedes usar una ruta interna (ej. /tienda) o un enlace externo (https://...).',
        'is_active' => 'Activo',
        'sort_order' => 'Prioridad de Orden',
        'sort_order_helper' => 'Menor número indica mayor prioridad (ej. 0 antes que 10).',
        'starts_at' => 'Fecha de Inicio',
        'ends_at' => 'Fecha de Fin',
    ],
    'close' => 'Cerrar anuncio',
];
