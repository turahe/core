<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
 *
 *
 */

return [
    'settings'                 => 'Ajustes',
    'updated'                  => 'Configuración actualizada',
    'general_settings'         => 'Configuración general',
    'company_information'      => 'Información de la empresa',
    'update_user_account_info' => 'La actualización de esta configuración no afectará la configuración de su cuenta de usuario, ya que esta configuración es general. Actualice la misma configuración en su cuenta de usuario si desea actualizar estas opciones.',
    'general'                  => 'General',
    'system'                   => 'Sistema',
    'system_email'             => 'Cuenta de correo electrónico del sistema',
    'system_email_configured'  => 'Cuenta configurada por otro usuario',
    'system_email_info'        => 'Seleccione la cuenta de correo electrónico conectada a la bandeja de entrada que se utilizará para enviar correos relacionados con el sistema, como el usuario asignado al contacto, recordatorio de actividad, invitaciones de usuario, etc.',
    'choose_logo'              => 'Elegir logo',
    'date_format'              => 'Formato de fecha',
    'time_format'              => 'Formato de la hora',
    'privacy_policy_info'      => 'Si no tienes política de privacidad, puedes configurarla aquí, consulta la política de privacidad en la siguiente URL: :url',
    'phones'                   => [
        'require_calling_prefix'      => 'Exigir prefijo en los números de teléfono',
        'require_calling_prefix_info' => 'La mayoría de las integraciones de llamadas requieren que los números de teléfono estén en formato E.164. Al habilitar esta opción, se asegurará de que no se ingresen números de teléfono sin un prefijo de llamada específico del país.',
    ],
    'recaptcha' => [
        'recaptcha'        => 'reCaptcha',
        'site_key'         => 'Clave de acceso',
        'secret_key'       => 'Clave secreta',
        'ignored_ips'      => 'Ignorar direcciones IP',
        'ignored_ips_info' => 'Ingrese las direcciones IP separadas por comas que desea que el reCaptcha omita la validación.',
    ],
    'security' => [
        'security'                     => 'Seguridad',
        'disable_password_forgot'      => 'Desactivar la función de olvido de contraseña',
        'disable_password_forgot_info' => 'Cuando está habilitada, la función de contraseña olvidada estará deshabilitada.',
        'block_bad_visitors'           => 'Bloquea a los visitantes no deseados',
        'block_bad_visitors_info'      => 'Si está habilitado, se verificará una lista de agentes de usuario, direcciones IP y referentes incorrectos para cada visitante invitado.',
    ],
    'first_day_of_week' => 'La semana comienza el',
    'tools'             => [
        'tools'          => 'Herramientas',
        'run'            => 'Ejecutar herramienta',
        'executed'       => 'Acción ejecutada con éxito',
        'json-language'  => 'Generar archivo de idioma JSON',
        'clear-cache'    => 'Borrar la memoria caché de la aplicación',
        'storage-link'   => 'Crear un enlace simbólico de "public/storage" a "storage/app/public".',
        'migrate'        => 'Ejecutar las migraciones de bases de datos',
        'optimize'       => 'Guarde en caché los archivos de arranque de la aplicación, como la configuración y las rutas.',
        'seed-mailables' => 'Sembrar las plantillas de correo de la aplicación',
    ],
    'integrations' => [
        'twilio' => [
            'create_app' => 'Crear aplicación',
            'disconnect' => 'Desconectar Integración',
            'number'     => 'Seleccione el número Twilio de su cuenta que se utilizará para hacer y recibir llamadas.',
            'app'        => 'Crear una aplicación que se encargará de iniciar nuevas llamadas y llamadas entrantes.',
        ],
    ],
];
