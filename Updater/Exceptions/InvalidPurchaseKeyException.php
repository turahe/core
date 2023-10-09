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

namespace Modules\Core\Updater\Exceptions;

use Exception;

class InvalidPurchaseKeyException extends UpdaterException
{
    /**
     * Initialize new InvalidPurchaseKeyException instance
     *
     * @param  string  $message
     * @param  int  $code
     */
    public function __construct($message = '', $code = 0, ?Exception $previous = null)
    {
        parent::__construct('Invalid purchase key.', 400, $previous);
    }
}
