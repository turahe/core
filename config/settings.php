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
    /* -----------------------------------------------------------------
     |  Default drivers
     | -----------------------------------------------------------------
     | Supported: 'array', 'json', 'database', 'redis'
     |
     | When using the "override" feature, the database driver should not be used
     | as it's interacting with the database in the service provider, it works good, but it's not recommended
     */

    'default' => ENV('SETTINGS_DRIVER', 'json'),

    /* -----------------------------------------------------------------
     |  Drivers
     | -----------------------------------------------------------------
     */

    'drivers' => [
        'array' => [
            'driver' => Modules\Core\Settings\Stores\ArrayStore::class,
        ],

        'json' => [
            'driver' => Modules\Core\Settings\Stores\JsonStore::class,

            'options' => [
                'path' => storage_path('settings.json'),
            ],
        ],

        'database' => [
            'driver' => \Modules\Core\Settings\Stores\DatabaseStore::class,

            'options' => [
                'table' => 'settings',
                'model' => \Modules\Core\Models\Setting::class,
            ],
        ],

        'redis' => [
            'driver' => Modules\Core\Settings\Stores\RedisStore::class,

            'options' => [
                'client' => 'predis',

                'default' => [
                    'host'     => env('REDIS_HOST', '127.0.0.1'),
                    'port'     => env('REDIS_PORT', 6379),
                    'database' => env('REDIS_DB', 0),
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Override application config values
    |--------------------------------------------------------------------------
    |
    | If defined, settings package will override these config values.
    |
    | Sample:
    |   "app.locale" => "settings.locale",
    |
    */
    'override' => [
        'app.name' => 'company_name',

        'core.logo.light' => 'logo_light',
        'core.logo.dark'  => 'logo_dark',

        'core.date_format' => 'date_format',
        'core.time_format' => 'time_format',
        'core.currency'    => 'currency',

        'core.microsoft.client_id'     => 'msgraph_client_id',
        'core.microsoft.client_secret' => 'msgraph_client_secret',

        'core.google.client_id'     => 'google_client_id',
        'core.google.client_secret' => 'google_client_secret',

        'core.recaptcha.site_key'    => 'recaptcha_site_key',
        'core.recaptcha.secret_key'  => 'recaptcha_secret_key',
        'core.recaptcha.ignored_ips' => 'recaptcha_ignored_ips',

        'core.services.twilio.applicationSid' => 'twilio_app_sid',
        'core.services.twilio.accountSid'     => 'twilio_account_sid',
        'core.services.twilio.authToken'      => 'twilio_auth_token',
        'core.services.twilio.number'         => 'twilio_number',

        'broadcasting.connections.pusher.key'             => 'pusher_app_key',
        'broadcasting.connections.pusher.secret'          => 'pusher_app_secret',
        'broadcasting.connections.pusher.app_id'          => 'pusher_app_id',
        'broadcasting.connections.pusher.options.cluster' => 'pusher_app_cluster',

        'updater.purchase_key' => 'purchase_key',
    ],

    /*
    |--------------------------------------------------------------------------
    | Encrypted settings keys
    |--------------------------------------------------------------------------
    |
    | Define settings keys which value should be encrypted in the store
    |
    */
    'encrypted' => [
        'msgraph_client_secret',
        'google_client_secret',
        'twilio_auth_token',
    ],
];
