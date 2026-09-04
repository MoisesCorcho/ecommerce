<?php

return [

    'failed' => 'Estas credenciales no coinciden con nuestros registros.',
    'password' => 'La contraseña ingresada es incorrecta.',
    'throttle' => 'Demasiados intentos de inicio de sesión. Vuelve a intentarlo en :seconds segundos.',

    'verify_email_required' => 'Verifica tu email para continuar.',

    'back_to_store' => 'Volver a la tienda',

    'fields' => [
        'name' => 'Nombre',
        'last_name' => 'Apellido',
        'email' => 'Email',
        'password' => 'Contraseña',
        'password_confirmation' => 'Confirmar contraseña',
        'terms' => 'Acepto los términos y condiciones',
    ],

    'login' => [
        'title' => 'Iniciar sesión',
        'subtitle' => 'Hola. Ingresa tus datos para acceder a tu cuenta.',
        'submit' => 'Iniciar sesión',
        'submitting' => 'Iniciando sesión...',
        'show_password' => 'Mostrar contraseña',
        'hide_password' => 'Ocultar contraseña',
        'no_account' => '¿No tienes cuenta?',
        'register_link' => 'Crea una',
        'forgot_password' => '¿Olvidaste tu contraseña?',
        'remember' => 'Recordarme',
        'placeholders' => [
            'email' => 'tucorreo@ejemplo.com',
            'password' => 'Tu contraseña',
        ],
    ],

    'register' => [
        'title' => 'Crear cuenta',
        'subtitle' => 'Bienvenido. Completa tus datos para comenzar.',
        'submit' => 'Crear cuenta',
        'submitting' => 'Creando cuenta...',
        'show_password' => 'Mostrar contraseña',
        'hide_password' => 'Ocultar contraseña',
        'have_account' => '¿Ya tienes cuenta?',
        'login_link' => 'Iniciar sesión',
        'placeholders' => [
            'name' => 'Ana Pérez',
            'last_name' => 'Pérez',
            'email' => 'tucorreo@ejemplo.com',
            'password' => 'Tu contraseña',
            'password_confirmation' => 'Confirma tu contraseña',
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
    ],

    'forgot_password' => [
        'title' => 'Restablecer contraseña',
        'intro' => 'Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.',
        'submit' => 'Enviar enlace',
        'back_to_login' => 'Volver a iniciar sesión',
    ],

    'reset_password' => [
        'title' => 'Elige una nueva contraseña',
        'submit' => 'Restablecer contraseña',
    ],

    'verify_email' => [
        'title' => 'Verifica tu email',
        'intro' => 'Te enviamos un enlace de verificación a :email. Haz clic para activar tu cuenta.',
        'resend' => 'Reenviar email de verificación',
        'resent' => 'Enlace de verificación enviado.',
    ],

    'logout' => [
        'submit' => 'Cerrar sesión',
    ],

    'emails' => [
        'footer_note' => 'Todos los derechos reservados.',
        'verify_email' => [
            'subject' => 'Verifica tu dirección de correo electrónico',
            'greeting' => '¡Hola, :name!',
            'line_1' => 'Gracias por registrarte en Leen Handbags. Para activar tu cuenta y acceder a tu perfil y lista de deseos, por favor confirma tu dirección de correo electrónico haciendo clic en el siguiente botón:',
            'action' => 'Verificar dirección de correo',
            'line_2' => 'Si no creaste una cuenta en Leen Handbags, puedes ignorar este mensaje sin inconvenientes.',
        ],
        'reset_password' => [
            'subject' => 'Restablece tu contraseña',
            'greeting' => '¡Hola, :name!',
            'line_1' => 'Recibes este correo porque se solicitó un restablecimiento de contraseña para tu cuenta en Leen Handbags.',
            'action' => 'Restablecer contraseña',
            'line_2' => 'Este enlace para restablecer la contraseña expirará en :count minutos.',
            'line_3' => 'Si no solicitaste este cambio, no es necesario realizar ninguna acción.',
        ],
    ],

];
