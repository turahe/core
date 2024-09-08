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
use Turahe\Core\Http\Requests\ResourceTableRequest;
use Turahe\Core\Http\Resources\TableResource;
use Turahe\Core\QueryBuilder\Exceptions\QueryBuilderException;

class TableController extends ApiController
{
    /**
     * Display a table listing of the resource.
     */
    public function index(ResourceTableRequest $request): JsonResponse
    {
        try {
            return $this->response(
                TableResource::collection($request->boolean('trashed') ?
            $request->resolveTrashedTable()->make() :
            $request->resolveTable()->make())
            );
        } catch (QueryBuilderException $e) {
            abort(400, $e->getMessage());
        }
    }

    /**
     * Get the resource table settings.
     */
    public function settings(ResourceTableRequest $request): JsonResponse
    {
        return $this->response(
            $request->boolean('trashed') ?
            $request->resolveTrashedTable()->settings() :
            $request->resolveTable()->settings()
        );
    }

    /**
     * Customize the resource table.
     */
    public function customize(ResourceTableRequest $request): JsonResponse
    {
        $table = tap($request->resolveTable(), function ($table) {
            abort_unless($table->customizeable, 403, 'This table cannot be customized.');
        });

        return $this->response(
            $table->settings()->update($request->all())
        );
    }
}
