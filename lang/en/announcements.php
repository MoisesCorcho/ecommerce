<?php

declare(strict_types=1);

return [
    'navigation' => [
        'group' => 'Marketing',
        'label' => 'Announcement Bar',
    ],
    'model' => [
        'label' => 'Announcement',
        'plural' => 'Announcement Bar',
    ],
    'sections' => [
        'content' => 'Announcement Content',
        'content_description' => 'Configure the translatable messages and optional destination link.',
        'schedule' => 'Visibility & Scheduling',
        'schedule_description' => 'Define priority and date range for storefront visibility.',
    ],
    'fields' => [
        'text' => 'Announcement Text',
        'text_es' => 'Text in Spanish',
        'text_en' => 'Text in English',
        'url' => 'Link (URL)',
        'url_helper' => 'Optional. You can enter an internal route (e.g. /shop) or an external link (https://...).',
        'is_active' => 'Active',
        'sort_order' => 'Sort Priority',
        'sort_order_helper' => 'Lower number indicates higher priority (e.g. 0 before 10).',
        'starts_at' => 'Starts At',
        'ends_at' => 'Ends At',
    ],
    'close' => 'Close announcement',
];
