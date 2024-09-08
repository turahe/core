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
use Illuminate\Support\Str;
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\Http\Requests\SettingRequest;

class SettingsController extends ApiController
{
    /**
     * Get the application settings.
     */
    public function index(): JsonResponse
    {
        return $this->response(
            collect(settings()->all())->reject(fn ($value, $name) => Str::startsWith($name, '_'))
        );
    }

    /**
     * Persist the settings in storage.
     */
    public function save(SettingRequest $request): JsonResponse
    {
        $request->saveSettings();

        return $this->response(settings()->all());
    }
}
