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
use Turahe\Core\Resource\EmailSearch;
use Turahe\Core\Contracts\Resources\HasEmail;
use Turahe\Core\Resource\Http\ResourceRequest;
use Turahe\Core\Http\Controllers\ApiController;

class EmailSearchController extends ApiController
{
    /**
     * Perform email search.
     */
    public function handle(ResourceRequest $request): JsonResponse
    {
        if (empty($request->q)) {
            return $this->response([]);
        }

        $resources = Innoclapps::registeredResources()->whereInstanceOf(HasEmail::class);

        return $this->response(
            new EmailSearch($request, $resources)
        );
    }
}
