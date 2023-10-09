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

use Modules\Core\Models\Country;
use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Resources\CountryResource;

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
