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
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\Resource\Http\ResourceRequest;

class SearchController extends ApiController
{
    /**
     * Perform search for a resource.
     */
    public function handle(ResourceRequest $request): JsonResponse
    {
        $resource = tap($request->resource(), function ($resource) {
            abort_if(! $resource::searchable(), 404);
        });

        if (empty($request->q)) {
            return $this->response([]);
        }

        $query = $resource->searchQuery()
            ->criteria($resource->getRequestCriteria($request));

        if ($criteria = $resource->viewAuthorizedRecordsCriteria()) {
            $query->criteria($criteria);
        }

        return $this->response(
            $request->toResponse(
                $resource->order($query)->get()
            )
        );
    }
}
