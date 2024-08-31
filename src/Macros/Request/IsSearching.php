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

namespace Turahe\Core\Macros\Request;

class IsSearching
{
    /**
     * Determine whether user is performing search via the RequestCriteria
     *
     * @return bool
     */
    public function __invoke()
    {
        return ! is_null(request()->get('q', null));
    }
}
