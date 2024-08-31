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

namespace Turahe\Core\Actions;

class ActionFields
{
    /**
     * Create new instance of action request fields
     */
    public function __construct(protected array $fields)
    {
    }

    /**
     * Get field
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->fields[$name] ?? null;
    }
}
