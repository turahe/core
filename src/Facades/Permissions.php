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
use Turahe\Core\Permissions\PermissionsManager;

/**
 * @method static void group(string|array $group, \Closure $callback)
 * @method static array groups()
 * @method static void view(string $view, array $data)
 * @method static void createMissing(string $guard = 'api')
 * @method static array all()
 * @method static array labeled()
 * @method static void register(\Closure $callback)
 *
 * @mixin \Turahe\Core\Permissions\PermissionsManager
 */
class Permissions extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return PermissionsManager::class;
    }
}
