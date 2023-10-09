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

namespace Modules\Core\Zapier;

use Exception;

class ActionNotSupportedException extends Exception
{
    /**
     * Initialize ActionNotSupportedException
     */
    public function __construct($action, $code = 0, ?Exception $previous = null)
    {
        parent::__construct("$action is not supported.", $code, $previous);
    }
}
