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

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Facades\Timezone;
use Modules\Core\Http\Controllers\ApiController;

class TimezoneController extends ApiController
{
    /**
     * Get a list of all timezones.
     */
    public function handle(): JsonResponse
    {
        return $this->response(Timezone::toArray());
    }
}
