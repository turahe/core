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

namespace Modules\Core\Contracts\VoIP;

use Illuminate\Http\Request;

interface ReceivesEvents
{
    /**
     * Set the call events URL
     *
     * @param  string  $url The URL the client events webhook should be pointed to
     */
    public function setEventsUrl(string $url): static;

    /**
     * Handle the VoIP service events request
     *
     *
     * @return mixed
     */
    public function events(Request $request);
}
