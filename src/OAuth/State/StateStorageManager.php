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

namespace Turahe\Core\OAuth\State;

use Illuminate\Support\Manager;
use Turahe\Core\OAuth\State\StorageDrivers\Session;

class StateStorageManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->container['config']['core.oauth.state.storage'];
    }

    /**
     * Create the session driver
     *
     * @return Session
     */
    public function createSessionDriver()
    {
        return new Session;
    }
}
