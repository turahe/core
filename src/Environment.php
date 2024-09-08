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

namespace Turahe\Core;

use Illuminate\Support\Facades\DB;

class Environment
{
    /**
     * Capture the current environment in storage.
     */
    public static function capture(array $extra = []): void
    {
        settings(array_merge([
            '_app_url' => config('app.url'),
            '_prev_app_url' => settings('_app_url'),
            '_server_ip' => $_SERVER['SERVER_ADDR'] ?? '', // may not be always reliable
            '_server_hostname' => gethostname() ?: '',
            '_db_driver_version' => DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION),
            '_db_driver' => DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME),
            '_php_version' => PHP_VERSION,
            '_version' => \Turahe\Core\Application::VERSION,
        ], $extra));
    }

    /**
     * Determine whether critical environment keys are changed.
     */
    public static function hasChanged(): bool
    {
        return rtrim(config('app.url'), '/') != rtrim(settings('_app_url'), '/') ||
            (! empty(settings('_php_version')) && settings('_php_version') != PHP_VERSION) ||
            (! empty(settings('_server_hostname')) && settings('_server_hostname') != gethostname());
    }
}
