<?php

declare(strict_types=1);

return [

    'meta' => [
        'page_title' => 'Register',
    ],

    'title' => 'Create your account',
    'subtitle' => 'Welcome. Fill in your details to get started.',

    'fields' => [
        'name' => 'Full name',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
        'phone_optional' => 'Phone number (optional)',
    ],

    'placeholders' => [
        'name' => 'Jane Doe',
        'email' => 'you@example.com',
        'phone' => '+1 555 123 4567',
    ],

    'actions' => [
        'submit' => 'Create account',
        'submitting' => 'Creating account...',
        'show_password' => 'Show password',
        'hide_password' => 'Hide password',
    ],

    'strength' => [
        'weak' => 'Weak',
        'medium' => 'Medium',
        'strong' => 'Strong',
    ],

    'terms' => [
        'prefix' => 'I accept the',
        'terms_link' => 'Terms and Conditions',
        'connector' => 'and the',
        'privacy_link' => 'Privacy Policy',
    ],

    'links' => [
        'have_account' => 'Already have an account?',
        'login' => 'Sign in',
    ],

    'errors' => [
        'name_required' => 'Full name is required.',
        'email_required' => 'Email is required.',
        'email_invalid' => 'Enter a valid email address.',
        'password_required' => 'Password is required.',
        'password_confirmation_required' => 'Confirm your password.',
        'password_mismatch' => 'Passwords do not match.',
        'terms_required' => 'You must accept the terms and conditions.',
        'email_taken' => 'This email is already registered.',
    ],

];
