<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'contact submission',
        'plural' => 'contact submissions',
    ],

    'navigation' => [
        'label' => 'Contact submissions',
    ],

    'pages' => [
        'view_title' => 'Contact submission',
        'list_title' => 'Contact submissions',
    ],

    'sections' => [
        'sender' => 'Sender details',
        'message' => 'Message content',
        'management' => 'Internal management',
    ],

    'fields' => [
        'name' => 'Name',
        'email' => 'Email',
        'subject' => 'Subject',
        'message' => 'Message',
        'status' => 'Status',
        'user' => 'Registered user',
        'ip_address' => 'IP address',
        'user_agent' => 'Browser / User agent',
        'read_at' => 'Read at',
        'replied_at' => 'Replied at',
        'admin_notes' => 'Internal notes',
        'created_at' => 'Received at',
        'updated_at' => 'Updated at',
    ],

    'actions' => [
        'mark_as_read' => 'Mark as read',
        'mark_as_replied' => 'Mark as replied',
        'edit_notes' => 'Edit notes',
        'delete' => 'Delete',
    ],

    'filters' => [
        'status' => 'Status',
        'all' => 'All',
    ],

    'empty' => [
        'heading' => 'No contact submissions',
        'description' => 'Messages sent by shoppers from the storefront will appear here.',
    ],

    'notifications' => [
        'marked_as_read' => 'Submission marked as read',
        'marked_as_replied' => 'Submission marked as replied',
        'notes_updated' => 'Internal notes updated',
        'deleted' => 'Submission deleted',
    ],

    'mail' => [
        'heading' => 'New contact message received',
        'view_in_panel' => 'View in Admin Panel',
        'footer_note' => 'This email was generated automatically by the Leen storefront.',
    ],
];
