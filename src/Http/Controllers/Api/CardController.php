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

use Turahe\Core\Facades\Cards;
use Illuminate\Http\JsonResponse;
use Turahe\Core\Http\Controllers\ApiController;

class CardController extends ApiController
{
    /**
     * Get cards that are intended to be shown on dashboards.
     */
    public function forDashboards(): JsonResponse
    {
        return $this->response(Cards::resolveForDashboard());
    }

    /**
     * Get the available cards for a given resource.
     */
    public function index(string $resourceName): JsonResponse
    {
        return $this->response(Cards::resolve($resourceName));
    }

    /**
     * Get card by given uri key.
     */
    public function show(string $card): JsonResponse
    {
        return $this->response(Cards::registered()->first(function ($item) use ($card) {
            return $item->uriKey() === $card;
        })->authorizeOrFail());
    }
}
