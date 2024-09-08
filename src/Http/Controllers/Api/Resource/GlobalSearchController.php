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

namespace Turahe\Core\Http\Controllers\Api\Resource;

use Illuminate\Http\JsonResponse;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\Resource\GlobalSearch;
use Turahe\Core\Resource\Http\ResourceRequest;

class GlobalSearchController extends ApiController
{
    /**
     * Perform global search.
     */
    public function handle(ResourceRequest $request): JsonResponse
    {
        if (empty($request->q)) {
            return $this->response([]);
        }

        $resources = Innoclapps::globallySearchableResources();

        return $this->response(
            new GlobalSearch($request, $resources)
        );
    }
}
