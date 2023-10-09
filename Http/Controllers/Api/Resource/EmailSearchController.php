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
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Resource\EmailSearch;
use Modules\Core\Contracts\Resources\HasEmail;
use Modules\Core\Resource\Http\ResourceRequest;
use Modules\Core\Http\Controllers\ApiController;

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
