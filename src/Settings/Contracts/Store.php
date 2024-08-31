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

interface Store
{
    /**
     * Get a specific key from the settings data.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Determine if a key exists in the settings data.
     */
    public function has(string $key): bool;

    /**
     * Set a specific key to a value in the settings data.
     */
    public function set(string|array $key, mixed $value = null): static;

    /**
     * Unset a key in the settings data.
     */
    public function forget(string $key): static;

    /**
     * Flushing all data.
     */
    public function flush(): static;

    /**
     * Get all settings data.
     */
    public function all(): array;

    /**
     * Save any changes done to the settings data.
     */
    public function save(): static;

    /**
     * Check if the data is saved.
     */
    public function isSaved(): bool;
}
