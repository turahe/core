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

namespace Turahe\Core\Http\Controllers\Api\Workflow;

use Illuminate\Http\JsonResponse;
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\Http\Requests\WorkflowRequest;
use Turahe\Core\Http\Resources\WorkflowResource;
use Turahe\Core\Models\Workflow;

class WorkflowController extends ApiController
{
    /**
     * Get the stored workflows.
     */
    public function index(): JsonResponse
    {
        return $this->response(
            WorkflowResource::collection(Workflow::get())
        );
    }

    /**
     * Display the specified workflow..
     */
    public function show(Workflow $workflow): JsonResponse
    {
        return $this->response(new WorkflowResource($workflow));
    }

    /**
     * Store a newly created workflow in storage.
     */
    public function store(WorkflowRequest $request): JsonResponse
    {
        $workflow = new Workflow($request->createData());

        $workflow->save();

        return $this->response(new WorkflowResource($workflow), 201);
    }

    /**
     * Update the specified workflow in storage.
     */
    public function update(Workflow $workflow, WorkflowRequest $request): JsonResponse
    {
        $workflow->fill($request->createData());

        $workflow->save();

        return $this->response(new WorkflowResource($workflow));
    }

    /**
     * Remove the specified workflow from storage.
     */
    public function destroy(Workflow $workflow): JsonResponse
    {
        $workflow->delete();

        return $this->response('', 204);
    }
}
