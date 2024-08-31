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

use Illuminate\Database\Eloquent\Model;

class DatabaseState
{
    protected static array $seeders = [];

    public static function register(string|array $class): void
    {
        static::$seeders = array_merge(static::$seeders, (array) $class);
    }

    public static function flush(): void
    {
        static::$seeders = [];
    }

    public static function seed(): void
    {
        collect(static::$seeders)->map(fn ($class) => new $class)->each(function ($instance) {
            Model::unguarded(function () use ($instance) {
                $instance->__invoke();
            });
        });
    }
}
