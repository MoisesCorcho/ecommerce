<?php

declare(strict_types=1);

return [
    'navigation' => [
        'group' => 'Marketing',
        'label' => 'Pop-ups Promocionales',
    ],
    'model' => [
        'label' => 'Pop-up Promocional',
        'plural' => 'Pop-ups Promocionales',
    ],
    'sections' => [
        'content' => 'Contenido del Pop-up',
        'content_description' => 'Configura los textos traducibles, la imagen promocional y el llamado a la acción.',
        'coupon' => 'Vinculación de Cupón de Descuento',
        'coupon_description' => 'Asocia un cupón para incentivar la compra y facilitar su copiado en el modal.',
        'schedule' => 'Comportamiento y Programación',
        'schedule_description' => 'Define el retraso de apertura, prioridad y el rango de fechas en que estará visible.',
    ],
    'fields' => [
        'title' => 'Título del Pop-up',
        'subtitle' => 'Subtítulo o Descripción',
        'image' => 'Imagen Promocional',
        'image_helper' => 'Sube una imagen atractiva (JPEG, PNG, WebP) para el modal.',
        'coupon' => 'Cupón Asociado',
        'coupon_helper' => 'Opcional. Selecciona un cupón activo para mostrarlo en el pop-up.',
        'cta_text' => 'Texto del Botón (CTA)',
        'cta_url' => 'Enlace de Destino (URL)',
        'cta_url_helper' => 'Opcional. URL a la que redirigirá al hacer clic en el botón principal.',
        'delay_seconds' => 'Retraso de Aparición (Segundos)',
        'delay_seconds_helper' => 'Segundos a esperar tras la carga de la página antes de mostrar el modal (1 a 60s).',
        'is_active' => 'Activo',
        'sort_order' => 'Prioridad de Orden',
        'sort_order_helper' => 'Menor número indica mayor prioridad (ej. 0 antes que 10).',
        'starts_at' => 'Fecha de Inicio',
        'ends_at' => 'Fecha de Fin',
    ],
    'badges' => [
        'primary' => 'Principal',
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'has_coupon' => 'Con Cupón',
    ],
    'storefront' => [
        'eyebrow' => 'Una oferta más dulce que la miel',
        'copy_code' => 'Copiar código',
        'code_copied' => '¡Copiado!',
        'close' => 'Cerrar ventana emergente',
        'off' => 'de descuento',
    ],
];
