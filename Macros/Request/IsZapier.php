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

namespace Modules\Core\Macros\Request;

class IsZapier
{
    /**
     * Determine whether current request is from Zapier
     *
     * @return bool
     */
    public function __invoke()
    {
        return request()->header('user-agent') === 'Zapier';
    }
}
