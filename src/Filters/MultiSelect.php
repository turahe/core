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

namespace Turahe\Core\Filters;

class MultiSelect extends Select
{
    /**
     * Defines a filter type
     */
    public function type(): string
    {
        return 'multi-select';
    }
}
