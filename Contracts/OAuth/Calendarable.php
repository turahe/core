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

namespace Modules\Core\Contracts\OAuth;

interface Calendarable
{
    /**
     * Get the OAuth account calendars
     *
     * @return \Modules\Core\Contracts\Calendar\Calendar[]
     */
    public function getCalendars();
}
