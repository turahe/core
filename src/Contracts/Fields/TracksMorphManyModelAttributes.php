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

namespace Turahe\Core\Contracts\Fields;

interface TracksMorphManyModelAttributes
{
    /**
     * Get the attributes the changes should be tracked on.
     */
    public function trackAttributes(): array|string;
}
