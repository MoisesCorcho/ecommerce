<?php

declare(strict_types=1);

return [

    'meta' => [
        'page_title' => 'Registro',
    ],

    'title' => 'Crea tu cuenta',
    'subtitle' => 'Bienvenido. Completa tus datos para comenzar.',

    'fields' => [
        'name' => 'Nombre completo',
        'email' => 'Correo electrónico',
        'password' => 'Contraseña',
        'password_confirmation' => 'Confirmar contraseña',
        'phone_optional' => 'Número de teléfono (opcional)',
    ],

    'placeholders' => [
        'name' => 'Ana Pérez',
        'email' => 'tucorreo@ejemplo.com',
        'phone' => '+57 300 123 4567',
    ],

    'actions' => [
        'submit' => 'Crear cuenta',
        'submitting' => 'Creando cuenta...',
        'show_password' => 'Mostrar contraseña',
        'hide_password' => 'Ocultar contraseña',
    ],

    'strength' => [
        'weak' => 'Débil',
        'medium' => 'Media',
        'strong' => 'Fuerte',
    ],

    'terms' => [
        'prefix' => 'Acepto los',
        'terms_link' => 'Términos y Condiciones',
        'connector' => 'y la',
        'privacy_link' => 'Política de Privacidad',
    ],

    'links' => [
        'have_account' => '¿Ya tienes cuenta?',
        'login' => 'Iniciar sesión',
    ],

    'errors' => [
        'name_required' => 'El nombre completo es obligatorio.',
        'email_required' => 'El correo electrónico es obligatorio.',
        'email_invalid' => 'Ingresa un correo electrónico válido.',
        'password_required' => 'La contraseña es obligatoria.',
        'password_confirmation_required' => 'Confirma tu contraseña.',
        'password_mismatch' => 'Las contraseñas no coinciden.',
        'terms_required' => 'Debes aceptar los términos y condiciones.',
        'email_taken' => 'Este correo ya está registrado.',
    ],

];
