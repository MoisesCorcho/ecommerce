<?php

declare(strict_types=1);

return [

    'meta' => [
        'page_title' => 'Iniciar sesión',
    ],

    'title' => 'Iniciar sesión',
    'subtitle' => 'Hola. Ingresa tus datos para acceder a tu cuenta.',

    'fields' => [
        'email' => 'Correo electrónico',
        'password' => 'Contraseña',
    ],

    'placeholders' => [
        'email' => 'tucorreo@ejemplo.com',
    ],

    'actions' => [
        'submit' => 'Iniciar sesión',
        'submitting' => 'Iniciando sesión...',
        'show_password' => 'Mostrar contraseña',
        'hide_password' => 'Ocultar contraseña',
    ],

    'links' => [
        'forgot_password' => '¿Olvidaste tu contraseña?',
        'no_account' => '¿No tienes cuenta?',
        'register' => 'Crear una',
        'back_to_store' => 'Volver a la tienda',
    ],

    'errors' => [
        'credentials' => 'Las credenciales no son correctas. Verifica tu email y contraseña.',
        'email_required' => 'El correo electrónico es obligatorio.',
        'email_invalid' => 'Ingresa un correo electrónico válido.',
        'password_required' => 'La contraseña es obligatoria.',
    ],

];
