<?php

declare(strict_types=1);

return [
    'fields' => [
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
    ],

    'errors' => [
        'current_password_incorrect' => 'Your current password is incorrect.',
    ],

    'profile' => [
        'title' => 'My account',
        'subtitle' => 'Manage your personal information and password.',
        'section_title' => 'Personal information',
        'submit' => 'Save changes',
        'updated' => 'Your profile has been updated. If you changed your email, check your inbox to verify it.',
    ],

    'password' => [
        'section_title' => 'Change password',
        'current' => 'Current password',
        'new' => 'New password',
        'confirmation' => 'Confirm new password',
        'submit' => 'Update password',
        'updated' => 'Your password has been updated.',
    ],

    'addresses' => [
        'title' => 'My addresses',
        'subtitle' => 'Manage the shipping addresses saved to your account.',
        'add_new' => 'Add address',
        'save' => 'Save address',
        'saved' => 'Address saved.',
        'cancel' => 'Cancel',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'deleted' => 'Address deleted.',
        'confirm_delete' => 'Delete this address?',
        'empty' => 'You have not added any addresses yet.',
        'default_badge' => 'Default',
        'make_default' => 'Set as default',
        'default_updated' => 'Default address updated.',
        'fields' => [
            'label' => 'Label',
            'full_name' => 'Full name',
            'phone' => 'Phone',
            'address_line_1' => 'Address line 1',
            'address_line_2' => 'Address line 2',
            'city' => 'City',
            'state' => 'State / Province',
            'country' => 'Country code',
            'postal_code' => 'Postal code',
            'is_default' => 'Set as default address',
        ],
    ],

    'orders' => [
        'title' => 'My orders',
        'subtitle' => 'Orders that have been paid or are further along.',
        'empty' => 'You do not have any orders yet.',
        'back_to_list' => '← Back to my orders',
        'shipping_address' => 'Shipping address',
        'detail_title' => 'Leen Handbags | Order detail',
    ],

    'reviews' => [
        'title' => 'My reviews',
        'subtitle' => 'Manage the reviews you have written.',
        'empty' => 'You have not written any reviews yet.',
        'save' => 'Save review',
        'cancel' => 'Cancel',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'confirm_delete' => 'Delete this review?',
        'updated' => 'Your review has been updated and is pending moderation again.',
        'deleted' => 'Review deleted.',
        'fields' => [
            'rating' => 'Rating',
            'comment' => 'Comment',
        ],
        'status' => [
            'approved' => 'Approved',
            'pending' => 'Pending moderation',
        ],
    ],
];
