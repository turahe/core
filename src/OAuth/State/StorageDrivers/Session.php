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

namespace Turahe\Core\OAuth\State\StorageDrivers;

use Illuminate\Support\Facades\Session as Storage;
use Turahe\Core\Contracts\OAuth\StateStorage;

class Session implements StateStorage
{
    /**
     * The state session key
     *
     * @var string
     */
    protected $key = 'oauth2state';

    /**
     * Get state from storage
     */
    public function get(): ?string
    {
        return Storage::get($this->key);
    }

    /**
     * Put state in storage
     *
     * @param  string  $value
     */
    public function put($value): void
    {
        Storage::put($this->key, $value);
    }

    /**
     * Check whether there is stored state
     */
    public function has(): bool
    {
        return Storage::has($this->key);
    }

    /**
     * Forget the remembered state from storage
     */
    public function forget(): void
    {
        Storage::forget($this->key);
    }
}
