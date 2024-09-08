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
use Turahe\Core\Card\CardsManager;

/**
 * @method static static register(string $resourceName, mixed $provider)
 * @method static \Illuminate\Support\Collection resolve(string $resourceName)
 * @method static \Illuminate\Support\Collection forResource(string $resourceName)
 * @method static \Illuminate\Support\Collection resolveForDashboard()
 * @method static \Illuminate\Support\Collection registered()
 *
 * @mixin \Turahe\Core\Card\CardsManager
 */
class Cards extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return CardsManager::class;
    }
}
