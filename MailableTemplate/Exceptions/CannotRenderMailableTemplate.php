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

namespace Modules\Core\MailableTemplate\Exceptions;

use Exception;

class CannotRenderMailableTemplate extends Exception
{
    /**
     * Throw exception
     *
     * @return Exception
     *
     * @throws CannotRenderMailableTemplate
     */
    public static function layoutDoesNotContainABodyPlaceHolder()
    {
        return new static('The layout does not contain a `{{{ mailBody }}}` placeholder');
    }
}
