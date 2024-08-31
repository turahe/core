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
use Turahe\Core\Contracts\Resources\Cloneable;
use Turahe\Core\Resource\Http\ResourceRequest;
use Turahe\Core\Http\Controllers\ApiController;

class CloneController extends ApiController
{
    /**
     * Clone a resource record
     */
    public function handle(ResourceRequest $request): JsonResponse
    {
        /** @var \Turahe\Core\Resource\Resource&\Turahe\Core\Contracts\Resources\Cloneable */
        $resource = $request->resource();

        abort_unless($resource instanceof Cloneable, 404);

        $this->authorize('view', $request->record());

        $record = $resource->clone($request->record(), $request->user()->id);

        return $this->response($request->toResponse(
            $resource->displayQuery()->find($record->getKey())
        ));
    }
}
