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

trait MetableElement
{
    /**
     * Additional field meta
     */
    public array $meta = [];

    /**
     * Get the element meta
     */
    public function meta(): array
    {
        return $this->meta;
    }

    /**
     * Add element meta
     */
    public function withMeta(array $attributes): static
    {
        $this->meta = array_merge_recursive($this->meta, $attributes);

        return $this;
    }
}
