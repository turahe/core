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
use Modules\Core\Models\CustomField;
use Modules\Core\Fields\CustomFieldService;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\CustomFieldRequest;
use Modules\Core\Http\Resources\CustomFieldResource;

class CustomFieldController extends ApiController
{
    /**
     * Get the fields types that can be used as custom fields.
     */
    public function index(Request $request): JsonResponse
    {
        $fields = CustomField::with('options')
            ->latest()
            ->paginate($request->integer('per_page', null));

        return $this->response(
            CustomFieldResource::collection($fields)
        );
    }

    /**
     * Create new custom field.
     */
    public function store(CustomFieldRequest $request, CustomFieldService $service): JsonResponse
    {
        $field = $service->create($request->all());

        return $this->response(new CustomFieldResource($field), 201);
    }

    /**
     * Update custom field.
     */
    public function update(string $id, CustomFieldRequest $request, CustomFieldService $service): JsonResponse
    {
        $field = $service->update($request->except(['field_type', 'field_id', 'is_unique']), (int) $id);

        return $this->response(new CustomFieldResource($field));
    }

    /**
     * Delete custom field.
     */
    public function destroy(string $id, CustomFieldService $service): JsonResponse
    {
        $service->delete((int) $id);

        return $this->response('', 204);
    }
}
