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

use Modules\Core\Facades\Fields;
use Illuminate\Http\JsonResponse;
use Modules\Core\Resource\Http\ResourceRequest;
use Modules\Core\Http\Controllers\ApiController;

class FieldController extends ApiController
{
    /**
     * Get the resource create fields.
     */
    public function create(ResourceRequest $request): JsonResponse
    {
        return $this->response(
            Fields::resolveCreateFieldsForDisplay($request->resourceName())
        );
    }

    /**
     * Get the resource update fields.
     */
    public function update(ResourceRequest $request): JsonResponse
    {
        $request->resource()->setModel($request->record());

        return $this->response(
            Fields::resolveUpdateFieldsForDisplay($request->resourceName())
        );
    }

    /**
     * Get the resource detail fields.
     */
    public function detail(ResourceRequest $request): JsonResponse
    {
        $request->resource()->setModel($request->record());

        return $this->response(
            Fields::resolveDetailFieldsForDisplay($request->resourceName())
        );
    }
}
