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

use DateTime;
use Exception;
use DateTimeZone;
use JsonSerializable;
use Turahe\Core\Contracts\Localizeable;
use Illuminate\Contracts\Support\Arrayable;

class Timezone implements Arrayable, JsonSerializable
{
    /**
     * @throws Exception
     */
    public function convertFromUTC(int $timestamp, ?string $timezone = null, string $format = 'Y-m-d H:i:s'): string
    {
        $timezone ??= $this->current();

        $date = new DateTime($timestamp, new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone($timezone));

        return $date->format($format);
    }

    /**
     * @throws Exception
     */
    public function convertToUTC(int $timestamp, ?string $timezone = null, string $format = 'Y-m-d H:i:s'): string
    {
        $timezone ??= $this->current();

        $date = new DateTime($timestamp, new DateTimeZone($timezone));
        $date->setTimezone(new DateTimeZone('UTC'));

        return $date->format($format);
    }

    /**
     * Alias to convertToUTC
     *
     * @param  mixed  $params
     *
     * @throws Exception
     */
    public function toUTC(...$params): string
    {
        return $this->convertToUTC(...$params);
    }

    /**
     * Alias to convertFromUTC
     *
     * @param mixed
     *
     * @return string
     * @throws Exception
     */
    public function fromUTC(...$params): string
    {
        return $this->convertFromUTC(...$params);
    }

    /**
     * Get the current timezone for the appliaction
     */
    public function current(?Localizeable $user = null): string
    {
        $user ??= auth()->user();

        // In case using the logged in user, check the interface
        throw_if(
            $user && ! $user instanceof Localizeable,
            new Exception('The user must be instance of '.Localizeable::class)
        );

        return $user ? $user->getUserTimezone() : config('app.timezone');
    }

    /**
     * Get all timezones
     */
    public function all(): array
    {
        return DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Timezones to array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }
}
