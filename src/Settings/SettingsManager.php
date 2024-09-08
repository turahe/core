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

namespace Turahe\Core\Settings;

use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use Turahe\Core\Settings\Contracts\Manager as SettingsManagerContract;
use Turahe\Core\Settings\Contracts\Store as StoreContract;

class SettingsManager extends Manager implements SettingsManagerContract
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('settings.default', 'json');
    }

    /**
     * Register a new store.
     *
     *
     * @return static
     */
    public function registerStore(string $driver, array $params)
    {
        return $this->extend($driver, function () use ($params): StoreContract {
            return $this->container->make($params['driver'], [
                'options' => Arr::get($params, 'options', []),
            ]);
        });
    }
}
