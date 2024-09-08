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
use Turahe\Core\Menu\MenuManager;

/**
 * @method static static register(\Turahe\Core\Menu\MenuItem|array $items)
 * @method static static registerItem(\Turahe\Core\Menu\MenuItem $item)
 * @method static \Illuminate\Support\Collection get()
 * @method static static clear()
 *
 * @mixin \Turahe\Core\Menu\MenuManager
 */
class Menu extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return MenuManager::class;
    }
}
