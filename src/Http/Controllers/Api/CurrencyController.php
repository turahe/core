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

namespace Turahe\Core\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Turahe\Core\Http\Controllers\ApiController;

class CurrencyController extends ApiController
{
    /**
     * Get the application available currencies.
     */
    public function __invoke(): JsonResponse
    {
        return $this->response(config('money'));
    }
}
