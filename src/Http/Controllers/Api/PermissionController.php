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

use Illuminate\Http\JsonResponse;
use Turahe\Core\Facades\Permissions;
use Turahe\Core\Http\Controllers\ApiController;

class PermissionController extends ApiController
{
    /**
     * Get all registered application permissions.
     */
    public function index(): JsonResponse
    {
        Permissions::createMissing();

        return $this->response([
            'grouped' => Permissions::groups(),
            'all' => Permissions::all(),
        ]);
    }
}
