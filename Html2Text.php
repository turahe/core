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

namespace Modules\Core;

use Soundasleep\Html2TextException;

class Html2Text
{
    /**
     * Convert HTML to Text
     *
     * @param  string  $html
     * @return string
     *
     * @throws Html2TextException
     */
    public static function convert($html)
    {
        return \Soundasleep\Html2Text::convert($html, ['ignore_errors' => true]);
    }
}
