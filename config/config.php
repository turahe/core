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
    'name' => 'Core',

    /*
    |--------------------------------------------------------------------------
    | Application logo config
    |--------------------------------------------------------------------------
    |
    */
    'logo' => [
        'light' => env('LIGHT_LOGO_URL'),
        'dark'  => env('DARK_LOGO_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Unique Identification Key
    |--------------------------------------------------------------------------
    */
    'key' => env('IDENTIFICATION_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Application Date Format
    |--------------------------------------------------------------------------
    |
    | Application date format, the value is used when performing formats for to
    | local date via the available formatters.
    |
    */

    'date_format' => 'F j, Y',

    /*
    |--------------------------------------------------------------------------
    | Application Time Format
    |--------------------------------------------------------------------------
    |
    | Application time format, the value is used when performing formats for to
    | local datetime via the available formatters.
    |
    */

    'time_format' => 'H:i:s',

    /*
    |--------------------------------------------------------------------------
    | Application Currency
    |--------------------------------------------------------------------------
    |
    | The application currency, is used on a specific features e.q. form groups
    |
    */
    'currency' => 'USD',

    /*
    |--------------------------------------------------------------------------
    | reCaptcha configuration
    |--------------------------------------------------------------------------
    |
    | reCaptcha configuration to provide additional security.
    |
    */
    'recaptcha' => [
        'site_key'    => env('RECAPTCHA_SITE_KEY', null),
        'secret_key'  => env('RECAPTCHA_SECRET_KEY', null),
        'ignored_ips' => env('RECAPTCHA_IGNORED_IPS', []),
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft deletes config
    |--------------------------------------------------------------------------
    |
    */
    'soft_deletes' => [
        'prune_after' => env('PRUNE_TRASHED_RECORDS_AFTER', 30), // in days
    ],

    /*
    |--------------------------------------------------------------------------
    | Mailable templates configuration
    |--------------------------------------------------------------------------
    |
    | layout => The mailable templates default layout path
    |
    */

    'mailables' => [
        'layout' => env('MAILABLE_TEMPLATE_LAYOUT', storage_path('mail-layouts/mailable-template.html')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can specificy the default directory where the media files
    | will be uploaded, keep in mind that the application will create
    | folder tree in this directory according to custom logic e.q.
    | /media/contacts/:id/image.jpg
    |
    */
    'media' => [
        'directory' => env('MEDIA_DIRECTORY', 'media'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Application favourite colors
    |--------------------------------------------------------------------------
    |
    */
    'colors' => explode(',', env(
        'COMMON_COLORS',
        '#374151,#DC2626,#F59E0B,#10B981,#2563EB,#4F46E5,#7C3AED,#EC4899,#F3F4F6'
    )),

    /*
    |--------------------------------------------------------------------------
    | Application actions config
    |--------------------------------------------------------------------------
    |
    */
    'actions' => [
        'disable_notifications_when_records_are_more_than' => env('DISABLE_ACTIONS_NOTIFICATIONS_WHEN_RECORDS_ARE_MORE_THAN', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Application oAuth config
    |--------------------------------------------------------------------------
    |
    */
    'oauth' => [
        'state' => [
            /**
             * State storage driver
             */
            'storage' => 'session',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Microsoft Integration
    |--------------------------------------------------------------------------
    |
    | Microsoft integration related config for connecting via oAuth.
    |
    */
    'microsoft' => [
        /**
         * The Microsoft Azure Application (client) ID
         *
         * https://portal.azure.com
         */
        'client_id' => env('MICROSOFT_CLIENT_ID'),

        /**
         * Azure application secret key
         */
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),

        /**
         * Application tenant ID
         * Use 'common' to support personal and work/school accounts
         */
        'tenant_id' => env('MICROSOFT_TENANT_ID', 'common'),

        /*
        * Set the url to trigger the OAuth process this url should call return Microsoft::connect();
        */
        'redirect_uri' => env('MICROSOFT_REDIRECT_URI', '/microsoft/callback'),

        /**
         * Callback URL
         *
         * This configuration takes precedence of the "redirect_uri" configuration value.
         */
        'redirect_url' => env('MICROSOFT_REDIRECT_URL'),

        /**
         * Login base URL
         */
        'login_url_base' => env('MICROSOFT_LOGIN_URL_BASE', 'https://login.microsoftonline.com'),

        /**
         * OAuth2 path
         */
        'oauth2_path' => env('MICROSOFT_OAUTH2_PATH', '/oauth2/v2.0'),

        /**
         * Microsoft scopes to be used, Graph API will acept up to 20 scopes
         *
         * @see https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-permissions-and-consent
         */
        'scopes' => [
            'offline_access',
            'openid',
            'User.Read',
            'Mail.ReadWrite',
            'Mail.Send',
            'MailboxSettings.ReadWrite',
            'Calendars.ReadWrite',
        ],

        /**
         * The default timezone is always set to UTC.
         */
        'prefer_timezone' => env('MS_GRAPH_PREFER_TIMEZONE', 'UTC'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Google Integration
    |--------------------------------------------------------------------------
    |
    | Google integration related config for connecting via oAuth.
    |
    */
    'google' => [
        /**
         * Google Project Client ID
         */
        'client_id' => env('GOOGLE_CLIENT_ID'),

        /**
         * Google Project Client Secret
         */
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),

        /**
         * Callback URI
         */
        'redirect_uri' => env('GOOGLE_REDIRECT_URI', '/google/callback'),

        /**
         * Callback URL
         *
         * This configuration takes precedence of the "redirect_uri" configuration value.
         */
        'redirect_url' => env('GOOGLE_REDIRECT_URL'),

        /**
         * Access type
         */
        'access_type' => 'offline',

        /**
         * Scopes for OAuth
         */
        'scopes' => ['https://mail.google.com/', 'https://www.googleapis.com/auth/calendar'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Define default VoIP Client
    |--------------------------------------------------------------------------
    |
    | Currently only "Twilio" is supported.
    |
    */

    'voip' => [
        'client' => env('VOIP_CLIENT'),
        // Route names
        'endpoints' => [
            'call'   => 'voip.call',
            'events' => 'voip.events',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | The application available services
    |--------------------------------------------------------------------------
    |
    | Here may be defined available services for the core module.
    |
    */
    'services' => [
        'twilio' => [
            'applicationSid' => env('TWILIO_APP_SID'),
            'accountSid'     => env('TWILIO_ACCOUNT_SID'),
            'authToken'      => env('TWILIO_AUTH_TOKEN'),
            'number'         => env('TWILIO_NUMBER'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | The application import configuration
    |--------------------------------------------------------------------------
    |
    | Define configuration like max import rows support.
    |
    */
    'import' => [
        'max_rows' => env('MAX_IMPORT_ROWS', 4000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Resources configuration
    |--------------------------------------------------------------------------
    |
    | Define configuration like permissions common provider.
    |
    */
    'resources' => [
        /**
         * Register the resources common permissions provider
         */
        'permissions' => [
            'common' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Allowed Date Formats
    |--------------------------------------------------------------------------
    |
    | The application date format that the users are able to use.
    |
    */
    'date_formats' => [
        'd-m-Y',
        'd/m/Y',
        'm-d-Y',
        'm.d.Y',
        'm/d/Y',
        'Y-m-d',
        'd.m.Y',
        'F j, Y',
        'j F, Y',
        'D, F j, Y',
        'l, F j, Y',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Allowed Time Formats
    |--------------------------------------------------------------------------
    |
    | The application time format that the users are able to use.
    |
    */
    'time_formats' => [
        'H:i',
        'h:i A',
        'h:i a',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application favicon
    |--------------------------------------------------------------------------
    | Here you may enable favicon to be included, but first you must generate
    | the favicons via https://realfavicongenerator.net/ and upload the .zip file
    | contents in /public/favicons.
    |
    | More info: https://www.wach.id/docs/favicon
    |
    */
    'favicon_enabled' => env('ENABLE_FAVICON', false),
];
