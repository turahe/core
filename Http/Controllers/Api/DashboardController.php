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

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Core\Models\Dashboard;
use Modules\Core\Card\DashboardService;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Resources\DashboardResource;

class DashboardController extends ApiController
{
    /**
     * Display a listing of the current user dashboards.
     */
    public function index(Request $request): JsonResponse
    {
        $dashboards = Dashboard::byUser($request->user()->getKey())->orderBy('name')->get();

        return $this->response(
            DashboardResource::collection($dashboards)
        );
    }

    /**
     * Display the specified dashboard.
     */
    public function show(Dashboard $dashboard): JsonResponse
    {
        $this->authorize('view', $dashboard);

        return $this->response(new DashboardResource($dashboard));
    }

    /**
     * Store a newly created dashboard in storage.
     */
    public function store(Request $request, DashboardService $service): JsonResponse
    {
        $data = $request->validate([
            'name'            => 'required|string|max:191',
            'cards.*.key'     => 'sometimes|required',
            'cards.*.order'   => 'sometimes|numeric',
            'cards.*.enabled' => 'sometimes|boolean',
            'is_default'      => 'sometimes|boolean',
        ]);

        $dashboard = $service->create($data, $request->user()->getKey());

        return $this->response(new DashboardResource($dashboard), 201);
    }

    /**
     * Update the specified dashboard in storage.
     */
    public function update(Dashboard $dashboard, Request $request): JsonResponse
    {
        $this->authorize('update', $dashboard);

        $data = $request->validate([
            'name'            => 'sometimes|required|string|max:191',
            'cards.*.key'     => 'sometimes|required',
            'cards.*.order'   => 'sometimes|numeric',
            'cards.*.enabled' => 'sometimes|boolean',
            'is_default'      => 'sometimes|boolean',
        ]);

        $dashboard->fill($data);

        if ($dashboard->is_default === false && $dashboard->user->hasOnlyOneDashboard()) {
            $dashboard->is_default = true;
        }

        $dashboard->save();

        return $this->response(new DashboardResource($dashboard));
    }

    /**
     * Remove the specified dashboard from storage.
     */
    public function destroy(Dashboard $dashboard): JsonResponse
    {
        $this->authorize('delete', $dashboard);

        if ($dashboard->user->hasOnlyOneDashboard()) {
            abort(409, 'There must be at least one active dashboard.');
        }

        $dashboard->delete();

        return $this->response('', 204);
    }
}
