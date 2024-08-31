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

namespace Turahe\Core\Settings\Stores;

class ArrayStore extends AbstractStore
{
    /**
     * Fire the post options to customize the store.
     */
    protected function postOptions(array $options)
    {
        // Do nothing...
    }

    /**
     * Read the data from the store.
     */
    protected function read(): array
    {
        return $this->data;
    }

    /**
     * Write the data into the store.
     */
    protected function write(array $data): void
    {
        // Nothing to do...
    }
}
