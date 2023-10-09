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
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Resource\Http\TrashedResourcefulRequest;

class TrashedController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(TrashedResourcefulRequest $request): JsonResponse
    {
        $this->authorize('viewAny', $request->resource()::$model);

        $results = $request->resource()
            ->resourcefulHandler($request)
            ->index($request->newQuery());

        return $this->response($request->toResponse($results));
    }

    /**
     * Perform search on the trashed resource.
     */
    public function search(TrashedResourcefulRequest $request): JsonResponse
    {
        $resource = $request->resource();

        abort_if(! $resource::searchable(), 404);

        if (empty($request->q)) {
            return $this->response([]);
        }

        $query = $request->resource()
            ->searchTrashedQuery($request->newQuery())
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

    /**
     * Display resource record.
     */
    public function show(TrashedResourcefulRequest $request): JsonResponse
    {
        $this->authorize('view', $request->record());

        $result = $request->resource()
            ->resourcefulHandler($request)
            ->show($request->resourceId(), $request->newQuery());

        return $this->response($request->toResponse($result));
    }

    /**
     * Remove resource record from storage.
     */
    public function destroy(TrashedResourcefulRequest $request): JsonResponse
    {
        $this->authorize('delete', $request->record());

        $content = $request->resource()
            ->resourcefulHandler($request)
            ->forceDelete($request->record());

        return $this->response($content, empty($content) ? 204 : 200);
    }

    /**
     * Restore the soft deleted record.
     */
    public function restore(TrashedResourcefulRequest $request): JsonResponse
    {
        $this->authorize('view', $request->record());

        $request->resource()
            ->resourcefulHandler($request)
            ->restore($request->record());

        return $this->response($request->toResponse(
            $request->resource()->displayQuery()->find($request->resourceId())
        ));
    }
}
