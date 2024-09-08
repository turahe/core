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

namespace Turahe\Core\Highlights;

use JsonSerializable;

abstract class Highlight implements JsonSerializable
{
    /**
     * Get the highlight name.
     */
    abstract public function name(): string;

    /**
     * Get the highligh count.
     */
    abstract public function count(): int;

    /**
     * Get the background color class when the highligh count is bigger then zero.
     */
    abstract public function bgColorClass(): string;

    /**
     * Get the front-end route that the highly will redirect to.
     */
    abstract public function route(): array|string;

    /**
     * Prepare the class for JSON serialization.
     */
    public function jsonSerialize(): array
    {
        return [
            'count' => $this->count(),
            'name' => $this->name(),
            'route' => $this->route(),
            'bgColorClass' => $this->bgColorClass(),
        ];
    }
}
