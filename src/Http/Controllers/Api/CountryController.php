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

use Turahe\Core\Models\Country;
use Illuminate\Http\JsonResponse;
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\Http\Resources\CountryResource;

class CountryController extends ApiController
{
    /**
     * Get a list of all the application countries in storage.
     */
    public function handle(): JsonResponse
    {
        return $this->response(
            CountryResource::collection(Country::get())
        );
    }
}
