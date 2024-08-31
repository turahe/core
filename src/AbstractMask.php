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

use JsonSerializable;
use Illuminate\Contracts\Support\Arrayable;

abstract class AbstractMask implements Arrayable, JsonSerializable
{
    /**
     * Initialize the mask
     */
    public function __construct(protected object|array $entity)
    {
    }

    /**
     * Get the entity
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
