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

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Resource\PlaceholdersGroup;
use Modules\Core\Resource\ResourcePlaceholders;
use Modules\Core\Http\Controllers\ApiController;

class PlaceholdersController extends ApiController
{
    /**
     * Retrieve placeholders via fields.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->response(ResourcePlaceholders::createGroupsFromResources(
            $request->input('resources', [])
        ));
    }

    /**
     * Parse placeholders via input fields.
     */
    public function parseViaInputFields(Request $request): JsonResponse
    {
        $resources = $request->input('resources', []);

        return $this->response(
            $this->placeholders($resources, $request)->parseWhenViaInputFields($request->input('content'))
        );
    }

    /**
     * Parse placeholders via interpolation.
     */
    public function parseViaInterpolation(Request $request): JsonResponse
    {
        $resources = $request->input('resources', []);

        return $this->response(
            $this->placeholders($resources, $request)->render($request->input('content'))
        );
    }

    /**
     * Create new Placeholders instance from the given resources.
     */
    protected function placeholders(array $resources, Request $request): ResourcePlaceholders
    {
        $groups = [];

        foreach ($resources as $resource) {
            $instance = Innoclapps::resourceByName($resource['name']);

            if ($instance) {
                $record = $instance->displayQuery()->find($resource['id']);

                if ($request->user()->can('view', $record)) {
                    $groups[$resource['name']] = new PlaceholdersGroup($instance, $record);
                }
            }
        }

        return new ResourcePlaceholders(array_values($groups));
    }
}
