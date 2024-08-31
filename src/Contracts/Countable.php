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

namespace Turahe\Core\Contracts;

interface Countable
{
    /**
     * Set that the class should count
     *
     * @return self
     */
    public function count(): static;

    /**
     * Check whether the class counts
     */
    public function counts(): bool;

    /**
     * Get the count key
     */
    public function countKey(): string;
}
