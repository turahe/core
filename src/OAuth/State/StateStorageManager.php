<?php

declare(strict_types=1);
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

namespace Turahe\Core\OAuth\State;

use Illuminate\Support\Manager;
use InvalidArgumentException;
use Turahe\Core\OAuth\State\StorageDrivers\Session;

class StateStorageManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        $driver = config('core.oauth.state.storage');

        if (! is_string($driver) || $driver === '') {
            throw new InvalidArgumentException('OAuth state storage driver is not configured.');
        }

        return $driver;
    }

    /**
     * Create the session driver
     */
    public function createSessionDriver(): Session
    {
        return new Session;
    }
}
