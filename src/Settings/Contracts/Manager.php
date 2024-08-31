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

namespace Turahe\Core\Settings\Contracts;

use Closure;

interface Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver();

    /**
     * Get all of the created "drivers".
     *
     * @return array
     */
    public function getDrivers();

    /**
     * Get a driver instance.
     *
     * @return \Turahe\Core\Settings\Contracts\Store
     */
    public function driver(?string $driver = null);

    /**
     * Register a custom driver creator Closure.
     *
     * @return static
     */
    public function extend(string $driver, Closure $callback);

    /**
     * Register a new store.
     *
     * @return static
     */
    public function registerStore(string $driver, array $params);
}
