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

namespace Modules\Core\Mail\Headers;

use Illuminate\Support\Carbon;

class DateHeader extends Header
{
    /**
     * Get the header value
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getValue()
    {
        $tz = config('app.timezone');

        $dateString = $this->value;

        // https://github.com/briannesbitt/Carbon/issues/685
        if (is_string($dateString)) {
            $dateString = trim(preg_replace('/\(.*$/', '', $dateString));
        }

        return $dateString ? Carbon::parse($dateString)->tz($tz) : null;
    }
}
