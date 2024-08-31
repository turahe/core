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

namespace Turahe\Core\Contracts\Synchronization;

use Turahe\Core\Models\Synchronization;

interface Synchronizable
{
    /**
     * Synchronize the data for the given synchronization
     */
    public function synchronize(Synchronization $synchronization): void;
}
