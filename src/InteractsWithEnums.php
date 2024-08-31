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

trait InteractsWithEnums
{
    /**
     * Find enum by given name.
     */
    public static function find(string $name): ?self
    {
        return array_values(array_filter(static::cases(), function ($status) use ($name) {
            return $status->name == $name;
        }))[0] ?? null;
    }

    /**
     * Get a random enum instance.
     */
    public static function random(): self
    {
        return static::find(static::names()[array_rand(static::names())]);
    }

    /**
     * Get all the enum names.
     */
    public static function names(): array
    {
        return array_column(static::cases(), 'name');
    }
}
