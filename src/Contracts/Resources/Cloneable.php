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

namespace Turahe\Core\Contracts\Resources;

use Turahe\Core\Models\Model;

interface Cloneable
{
    /**
     * Clone the resource record from the given id
     */
    public function clone(Model $model, int $userId): Model;
}
