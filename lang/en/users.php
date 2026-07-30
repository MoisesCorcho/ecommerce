<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'user',
        'plural' => 'users',
    ],

    'pages' => [
        'create_title' => 'New user',
    ],

    'section' => [
        'account_details' => 'Account details',
        'account_details_description' => 'Access and contact information for the buyer or administrator.',
    ],

    'fields' => [
        'name' => 'Name',
        'last_name' => 'Last name',
        'email' => 'Email',
        'phone' => 'Phone',
        'password' => 'Password',
        'addresses_count' => 'Addresses',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    'placeholders' => [
        'phone' => '+57 300 123 4567',
        'empty' => '—',
    ],

    'helpers' => [
        'phone_optional' => 'Optional. Free format (E.164 recommended).',
        'password_keep' => 'Leave empty to keep the current password.',
    ],

    'actions' => [
        'create' => 'New user',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'delete_selected' => 'Delete selected',
        'confirm_delete' => 'Yes, delete',
    ],

    'modals' => [
        'delete_heading' => 'Delete user',
        'delete_description' => 'The user will be soft-deleted and will no longer appear in the default list.',
        'delete_bulk_heading' => 'Delete users',
        'delete_bulk_description' => 'Users will be soft-deleted and will no longer appear in the list.',
    ],

    'empty' => [
        'heading' => 'No users yet',
        'description' => 'Create the first buyer or administrator account.',
    ],

    'notifications' => [
        'created' => 'User created',
        'updated' => 'User updated',
    ],

    'validation' => [
        'name_required' => 'Name is required.',
        'email_required' => 'Email is required.',
        'email_invalid' => 'Email is not valid.',
        'email_unique' => 'The email is already used by another user.',
        'password_required_on_create' => 'Password is required when creating a user.',
    ],
];
