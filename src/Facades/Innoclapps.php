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

namespace Turahe\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Turahe\Core\Application;

/**
 * @method static void boot()
 * @method static void booting(callable $callback)
 * @method static void booted(callable $callback)
 * @method static string version()
 * @method static string systemName()
 * @method static \Turahe\Core\Resource\Resource resourceByName(string $name)
 * @method static \Turahe\Core\Resource\Resource resourceByModel(string|\Turahe\Core\Models\Model $model)
 *
 * @mixin \Turahe\Core\Application
 * */
class Innoclapps extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Application::class;
    }
}
