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

namespace Turahe\Core\Calendar;

use InvalidArgumentException;
use Turahe\Core\Calendar\Google\GoogleCalendar;
use Turahe\Core\Calendar\Outlook\OutlookCalendar;
use Turahe\Core\Contracts\OAuth\Calendarable;
use Turahe\Core\OAuth\AccessTokenProvider;

class CalendarManager
{
    /**
     * Create calendar client.
     */
    public static function createClient(string $connectionType, AccessTokenProvider $token): Calendarable
    {
        $method = 'create'.ucfirst($connectionType).'Driver';

        if (! method_exists(new static, $method)) {
            throw new InvalidArgumentException(sprintf('Unable to resolve [%s] driver for [%s].', $method, static::class));
        }

        return self::$method($token);
    }

    /**
     * Create the Google calendar driver.
     */
    public static function createGoogleDriver(AccessTokenProvider $token): GoogleCalendar&Calendarable
    {
        return new GoogleCalendar($token);
    }

    /**
     * Create the Outlook calendar driver.
     */
    public static function createOutlookDriver(AccessTokenProvider $token): OutlookCalendar&Calendarable
    {
        return new OutlookCalendar($token);
    }
}
