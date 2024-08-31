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

namespace Turahe\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Turahe\Core\Facades\Fields;
use Illuminate\Http\JsonResponse;
use Turahe\Core\Http\Controllers\ApiController;

class FieldController extends ApiController
{
    /**
     * Get fields in a group for specific view for display.
     */
    public function index(string $group, string $view): JsonResponse
    {
        return $this->response(
            Fields::resolveForDisplay($group, $view)
        );
    }

    /**
     * Get the fields that are intended for the settings.
     */
    public function settings(string $group, string $view): JsonResponse
    {
        return $this->response(
            Fields::resolveForSettings($group, $view)
        );
    }

    /**
     * Get fields for settings in bulk in given groups.
     */
    public function bulkSettings(string $view, Request $request): JsonResponse
    {
        return $this->response(
            $request->collect('groups')->mapWithKeys(
                fn ($group) => [$group => Fields::resolveForSettings($group, $view)]
            )
        );
    }

    /**
     * Save the customized fields from settings.
     */
    public function update(string $group, string $view, Request $request): JsonResponse
    {
        Fields::customize($request->post(), $group, $view);

        return $this->response(
            Fields::resolveForDisplay($group, $view)
        );
    }

    /**
     * Reset the customized fields for a view.
     */
    public function destroy(string $group, string $view): JsonResponse
    {
        Fields::customize([], $group, $view);

        return $this->response([
            'settings' => Fields::resolveForSettings($group, $view),
            'fields'   => Fields::resolveForDisplay($group, $view),
        ]);
    }
}
