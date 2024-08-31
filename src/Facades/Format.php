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
use Turahe\Core\Date\Format as BaseFormat;

/**
 * @method static string format($value, $format, ?\Turahe\Core\Contracts\Localizeable $user = null)
 * @method static string dateTime($value, ?\Turahe\Core\Contracts\Localizeable $user = null)
 * @method static string date($value, ?\Turahe\Core\Contracts\Localizeable $user = null)
 * @method static string time($value, ?\Turahe\Core\Contracts\Localizeable $user = null)
 * @method static string separateDateAndTime(string $date, string $time, ?\Turahe\Core\Contracts\Localizeable $user = null)
 * @method static string|null diffForHumans($value, ?\Turahe\Core\Contracts\Localizeable $user = null)
 *
 * @mixin \Turahe\Core\Date\Format
 */
class Format extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return BaseFormat::class;
    }
}
