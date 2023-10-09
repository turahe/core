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

namespace Modules\Core\Http\Controllers\Api\Resource;

use Illuminate\Http\JsonResponse;
use Modules\Core\Resource\Http\ResourceRequest;
use Modules\Core\Http\Controllers\ApiController;

class FilterRulesController extends ApiController
{
    /**
     * Get the resource available filters rules.
     */
    public function index(ResourceRequest $request): JsonResponse
    {
        return $this->response($request->resource()->filtersForResource($request));
    }
}
